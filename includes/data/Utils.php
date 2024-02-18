<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : Utils.php
 * Description :
 * Date        : 7-1-2024
 * Time        : 10:38
 */

namespace MediaWiki\Extension\WikiApiary\data;

use ExtensionRegistry;
use Title;

class Utils {

	/**
	 * @param string $option
	 * @param bool $extract
	 * @return string|array
	 */
	public static function checkForMultiple( string $option, bool $extract = false ) {
		$ret = [];
		if ( str_contains( $option, ','	) ) {
			$exploded = array_map( 'trim', explode(
				',',
				$option
			) );
			if ( $extract ) {
				foreach ( $exploded as $item ) {
					if ( str_contains( $item, '=' ) ) {
						$itemExploded = explode( '=', $item );
						$k = trim( $itemExploded[0] );
						$v = trim( $itemExploded[1] );
						$ret[$k] = $v;
					}
				}
			} else {
				$ret = $exploded;
			}
		} elseif ( $extract ) {
			if ( str_contains( $option, '=' ) ) {
				$itemExploded = explode( '=', $option );
				$k = $itemExploded[0];
				$v = $itemExploded[1];
				$ret[$k] = $v;
			} else {
				$ret = $option;
			}
		} else {
			$ret = $option;
		}
		return $ret;
	}

	/**
	 * @param int $id
	 *
	 * @return string|null
	 */
	public static function getPageTitleFromID( int $id ): ?string {
		$title = Title::newFromID( $id );
		return $title ? $title->getFullText() : null;
	}

	/**
	 * @param string $k
	 * @param bool $checkEmpty
	 * @param array|null $arguments
	 * @return string|true|null
	 */
	public static function getOptionSetting( string $k, bool $checkEmpty = true, array $arguments = [] ) {
		if ( $checkEmpty ) {
			if ( isset( $arguments[ $k ] ) && $arguments[ $k ] != '' ) {
				return trim( $arguments[ $k ] );
			} else {
				return null;
			}
		} else {
			if ( isset( $arguments[ $k ] ) ) {
				return true;
			} else {
				return null;
			}
		}
	}

	/**
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function exportArrayFunction( array $data ): array {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'ArrayFunctions' ) ) {
			return [ \ArrayFunctions\Utils::export( $data ) ];
		} else {
			return [];
		}
	}

	/**
	 * @param array $result
	 *
	 * @return string
	 */
	public static function formatCSV( array $result ): string {
		if ( empty( $result ) ) {
			return '';
		}
		$ret = '';
		foreach ( $result as $row ) {
			$ret .= implode( ',', $row ) . ';';
		}
		return $ret;
	}

	/**
	 * @param array $data
	 * @param string $title
	 * @param array $columnNames
	 * @param bool $horizontal
	 *
	 * @return string
	 */
	public static function renderTable( array $data, string $title = '',
		array $columnNames = [ 'Field', 'Values' ], bool $horizontal = false ): string {
		$result = '';
		if ( !empty( $title ) ) {
			$result .= '<h3>' . $title . '</h3>' . PHP_EOL;
		}
		if ( $horizontal ) {
			$ret = '{| class="wikitable"' . PHP_EOL;
			$ret .= '|-' . PHP_EOL;
			$ret .= '! ';
			foreach ( $columnNames as $cName ) {
				$ret .= $cName . ' !! ';
			}
			$ret = substr( $ret, 0, -3 );
			$ret .= PHP_EOL;
			$ret .= '|-' . PHP_EOL;
			foreach ( $data as $tableData ) {
				$ret .= '| ';
				foreach ( $tableData as $singleLine ) {
					$ret .= $singleLine . ' || ';
				}
				$ret = substr( $ret, 0, -3 );
				$ret .= PHP_EOL;
				$ret .= '|-' . PHP_EOL;
			}
			$ret .= '|}' . PHP_EOL;
			$result .= $ret . PHP_EOL;
		} else {
			foreach ( $data as $tableName => $tableData ) {
				$ret = '{| class="wikitable"' . PHP_EOL;
				$ret .= '|+ ' . $tableName . PHP_EOL;
				$ret .= '|-' . PHP_EOL;
				$ret .= '! ' . $columnNames[0] . ' !! ' . $columnNames[1] . PHP_EOL;
				$ret .= '|-' . PHP_EOL;
				foreach ( $tableData as $k => $v ) {
					if ( is_array( $v ) ) {
						foreach ( $v as $extraKey => $extraValue ) {
							$ret .= '| ' . Structure::w8yMessage( $extraKey ) . ' || ' . $extraValue . PHP_EOL;
							$ret .= '|-' . PHP_EOL;
						}
					} else {
						$ret .= '| ' . Structure::w8yMessage( $k ) . ' || ' . $v . PHP_EOL;
						$ret .= '|-' . PHP_EOL;
					}
				}
				$ret .= '|}' . PHP_EOL;
				$result .= $ret . PHP_EOL;
			}
		}
		return $result;
	}
}
