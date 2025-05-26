<?php

/**
 * BlueSpice MediaWiki
 * Extension: Checklist
 * Description: Provides checklist functions.
 * Authors: Markus Glaser, Patric Wirth, Leonid Verhovskij
 *
 * Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @author     Markus Glaser
 * @author     Leonid Verhovskij
 * @package    BlueSpice_Extensions
 * @subpackage Checklist
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Checklist;

use MediaWiki\Content\TextContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use WikiPage;

class Extension extends \BlueSpice\Extension {

	/** @var int */
	public static int $iChecklistMaxItemLength = 60;

	/**
	 * @param string $listTitle
	 * @return array
	 */
	public static function getListOptions( $listTitle ) {
		$options = [];

		$title = Title::newFromText( $listTitle, NS_TEMPLATE );
		if ( $title instanceof Title && $title->exists() ) {
			$wikipage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromID( $title->getArticleID() );
			if ( $wikipage instanceof WikiPage ) {
				$contentObj = $wikipage->getContent();
				$content = ( $contentObj instanceof TextContent ) ? $contentObj->getText() : '';
				// Noinclude handling
				// See https://github.com/wikimedia/mediawiki-extensions-ExternalData/blob/master/ED_GetData.php
				$content = \StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $content );
				$content = strtr( $content, [ '<includeonly>' => '', '</includeonly>' => '' ] );

				$lines = explode( "\n", trim( $content ) );
				foreach ( $lines as $line ) {
					if ( strpos( $line, '*' ) !== 0 ) {
						return [];
					}
					if ( strlen( $line ) > self::$iChecklistMaxItemLength ) {
						return [];
					}
					$newLine = trim( substr( $line, 1 ) );
					$options[] = $newLine;
				}
			}
		}
		return $options;
	}

	/**
	 * http://www.php.net/manual/en/function.preg-replace.php#112400
	 *
	 * @param mixed $pattern
	 * @param mixed $replacement
	 * @param mixed $subject
	 * @param int $nth
	 * @return mixed
	 */
	public static function preg_replace_nth( $pattern, $replacement, $subject, $nth = 1 ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		return preg_replace_callback( $pattern,
			static function ( $found ) use ( &$pattern, &$replacement, &$nth ) {
					$nth--;
				if ( $nth == 0 ) {
					$sResult = preg_replace( '/value=".*?"(\s*|)/', '', reset( $found ) );
					$sResult = preg_replace( '/checked=".*?"(\s*|)/', '', $sResult );
					$sResult = preg_replace( $pattern, $replacement, $sResult );
					return $sResult;
				}
					return reset( $found );
			}, $subject, $nth );
	}
}
