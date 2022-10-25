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

use MediaWiki\MediaWikiServices;
use Parser;
use PPFrame;
use TextContent;
use Title;
use WikiPage;

class Extension extends \BlueSpice\Extension {

	public static $iCheckboxCounter = 0;
	public static $bCheckboxFound = false;
	public static $iChecklistMaxItemLength = 60;

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
	public static function preg_replace_nth( $pattern, $replacement, $subject, $nth = 1 ) {
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

	/**
	 * @param Parser &$parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:checklist', '\BlueSpice\Checklist\Extension::onMagicWordBsChecklist' );
		return true;
	}

	/**
	 * handle tag "bs:checkbox"
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public static function onMagicWordBsChecklist( $input, array $args, \Parser $parser,
		\PPFrame $frame ) {
		$parserOutput = $parser->getOutput();
		$parserOutput->addModules( [ 'ext.bluespice.checklist.view' ] );
		$parserOutput->updateCacheExpiry( 0 );
		$parserOutput->setPageProperty( 'bs-tag-checklist', 1 );
		self::$bCheckboxFound = true;
		$sOut = [];

		if ( isset( $args['list'] ) ) {
			$aOptions = self::getListOptions( $args['list'] );
		}
		if ( !isset( $args['value'] ) || $args['value'] === 'false' ) {
			$args['value'] = '';
		}
		if ( !isset( $args['checked'] ) ) {
			$args['checked'] = '';
		}

		$sSelectColor = '';
		if ( isset( $args['type'] ) && $args['type'] == 'list' ) {
			$sOut[] = "<select {color} ";
			$sOut[] = "id='bs-cb-" . self::getNewCheckboxId() . "' ";
			$sOut[] = "onchange='BsChecklist.change(this);' ";
			$sOut[] = ">";

			$bDefault = empty( $args['value'] ) ? true : false;

			foreach ( $aOptions as $sOption ) {
				$aOptionSet = explode( "|", $sOption );

				if ( !$sSelectColor && isset( $aOptionSet[1] ) ) {
					$sSelectColor = "style='color:" . $aOptionSet[1] . ";' ";
				}

				$sOption = trim( $aOptionSet[0] );
				$sOut[] = "<option ";
				if ( isset( $aOptionSet[1] ) ) {
					$sOut[] = "style='color:" . $aOptionSet[1] . ";' ";
				}
				if ( $bDefault || $args['value'] == $sOption ) {
					$bDefault = false;
					$sOut[] = "selected='selected'";
					if ( isset( $aOptionSet[1] ) ) {
						$sSelectColor = "style='color:" . $aOptionSet[1] . ";' ";
					}
				}
				$sOut[] = ">";
				$sOut[] = $sOption;
				$sOut[] = "</option>";
			}
			$sOut[] = "</select>";
		} else {
			$sOut[] = "<input type='checkbox' ";
			$sOut[] = "id='bs-cb-" . self::getNewCheckboxId() . "' ";
			$sOut[] = "onclick='BsChecklist.click(this);' ";
			if ( $args['value'] == 'checked' || $args['checked'] == 'true' ) {
				$sOut[] = "checked='checked' ";
			}
			$sOut[] = "/>";
		}
		$sOut = implode( '', $sOut );
		$sOut = str_replace( '{color}', $sSelectColor, $sOut );
		return $sOut;
	}

	/**
	 * @return int
	 */
	protected static function getNewCheckboxId() {
		self::$iCheckboxCounter++;
		return self::$iCheckboxCounter;
	}
}
