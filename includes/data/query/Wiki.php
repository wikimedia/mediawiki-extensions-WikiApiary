<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : Wiki.php
 * Description :
 * Date        : 11-1-2024
 * Time        : 22:05
 */

namespace MediaWiki\Extension\WikiApiary\data\query;

use MediaWiki\Extension\WikiApiary\data\Structure;
use MediaWiki\Extension\WikiApiary\data\Utils;
use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\DBConnRef;

class Wiki {

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param int $versionId
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getExtensions( int $versionId, DBConnRef $dbr ): array {
		$select = [ '*' ];
		$from = Structure::DBTABLE_EXTENSIONS_LINK;
		$where = [ Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_VID => $versionId ];
		$res = $dbr->newSelectQueryBuilder()->
		select( $select )->
		from( $from )->
		join( Structure::DBTABLE_EXTENSIONS, null, Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_ID . ' = ' . Structure::DBTABLE_EXTENSIONS . '.' . Structure::EXTENSION_ID )->
		where( $where )->
		caller( __METHOD__ )->
		fetchResultSet();

		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				foreach ( $this->structure->returnTableColumns( Structure::DBTABLE_EXTENSIONS ) as $tName ) {
					$ret[$t][$tName] = $row[$tName];
				}
				$t++;
			}
		}
		return $ret;
	}

	/**
	 * @param int $versionId
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getSkins( int $versionId, DBConnRef $dbr ): array {
		$select = [ '*' ];
		$from = Structure::DBTABLE_SKINS_LINK;
		$where = [ Structure::DBTABLE_SKINS_LINK . '.' . Structure::SKIN_LINK_VID => $versionId ];
		$res = $dbr->newSelectQueryBuilder()->
		select( $select )->
		from( $from )->
		join( Structure::DBTABLE_SKINS, null, Structure::DBTABLE_SKINS_LINK . '.' . Structure::SKIN_LINK_ID . ' = ' . Structure::DBTABLE_SKINS . '.' . Structure::SKIN_ID )->
		where( $where )->
		caller( __METHOD__ )->
		fetchResultSet();

		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				foreach ( $this->structure->returnTableColumns( Structure::DBTABLE_SKINS ) as $tName ) {
					$ret[$t][$tName] = $row[$tName];
				}
				$t++;
			}
		}
		return $ret;
	}

	/**
	 * @param int $pageId
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getWikiAndScrapeRecord( int $pageId, DBConnRef $dbr ): array {
		$select = [ Structure::DBTABLE_WIKIS . '.*', Structure::DBTABLE_SCRAPE . '.*' ];
		$from = Structure::DBTABLE_WIKIS;
		$where = [ Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_PAGEID => $pageId,
			Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_DEFUNCT => 0 ];
		$res = $dbr->newSelectQueryBuilder()->
		select( $select )->
		from( $from )->
		join( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . ' = ' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
		where( $where )->
		caller( __METHOD__ )->
		fetchResultSet();
		$ret = [];
		$result = [];
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$ret[] = (array)$row;
			}
			$ret = $ret[0];
			foreach ( $ret as $k => $v ) {
				switch ( substr( $k,
					0,
					6 ) ) {
					case "w8y_wi":
						$result['wiki'][$k] = $v;
						break;
					case "w8y_sr":
						$result['scrape'][$k] = $v;
						break;
				}
			}
		}
		return $result;
	}

	/**
	 * @param int $pageID
	 * @param string $export
	 * @return array|string
	 */
	public function doQuery( int $pageID, string $export = "table" ) {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );

		// Let's get the wiki and scrape information first

		$result = $this->getWikiAndScrapeRecord( $pageID, $dbr );
		if ( empty( $result ) ) {
			return $result;
		}
		$result['wiki']['w8y_pageTitle'] = Utils::getPageTitleFromID( $pageID );
		if ( $result['scrape'][Structure::SCRAPE_IS_ALIVE] == 0 ) {
			$result['extensions'] = [];
			$result['skins'] = [];
		} else {
			$result['extensions'] = $this->getExtensions( $result['scrape'][Structure::SCRAPE_VR_ID], $dbr );
			$result['skins'] = $this->getSkins( $result['scrape'][Structure::SCRAPE_VR_ID], $dbr );
		}
		switch ( $export ) {
			case "table":
				return Utils::renderTable( $result,
					'Results for ' . $result['wiki']['w8y_pageTitle'] .
					' ( pageID: ' . $pageID . ' )' );
			case "arrayfunctions":
				return [ Utils::exportArrayFunction( $result ), 'nowiki' => true ];
			case "lua":
				return $result;
			default:
				return "";
		}
	}
}
