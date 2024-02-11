<?php
/*
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace MediaWiki\Extension\WikiApiary;

use MediaWiki\Extension\WikiApiary\Scribunto\ScribuntoLuaLibrary;

class ScribuntoHooks {

	/**
	 * Add w8y library to Scribunto.
	 *
	 * @link https://www.mediawiki.org/wiki/Extension:Scribunto/Hooks/ScribuntoExternalLibraries
	 *
	 * @param string $engine
	 * @param array &$extraLibraries
	 */
	public static function onScribuntoExternalLibraries( string $engine, array &$extraLibraries ): void {
		if ( $engine === 'lua' ) {
			$extraLibraries['w8y'] = ScribuntoLuaLibrary::class;
		}
	}

	/**
	 * External Lua library paths for Scribunto
	 *
	 * @param string $engine
	 * @param string[] &$extraLibraryPaths
	 */
	public static function onScribuntoExternalLibraryPaths( string $engine, array &$extraLibraryPaths ): void {
		if ( $engine === 'lua' ) {
			$extraLibraryPaths[] = __DIR__ . '/Scribunto';
		}
	}
}
