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

class Extension extends \BlueSpice\Extension {

	public static $iCheckboxCounter = 0;
	public static $bCheckboxFound = false;
	public static $iChecklistMaxItemLength = 60;

	/**
	 *
	 * @param string $listTitle
	 * @return array
	 */
	public static function getListOptions( $listTitle ) {
		$aOptions = [];
		$oTitle = \Title::newFromText( $listTitle, NS_TEMPLATE );
		// echo $args['list']." ".$oTitle->getArticleID();
		if ( is_object( $oTitle ) ) {
			$oWikiPage = \WikiPage::newFromID( $oTitle->getArticleID() );
			if ( is_object( $oWikiPage ) ) {
				$sContent = $oWikiPage->getContent()->getNativeData();
				// Noinclude handling
				// See https://github.com/wikimedia/mediawiki-extensions-ExternalData/blob/master/ED_GetData.php
				$sContent = \StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $sContent );
				$sContent = strtr( $sContent, [ '<includeonly>' => '', '</includeonly>' => '' ] );
				$aLines = explode( "\n", trim( $sContent ) );
				foreach ( $aLines as $sLine ) {
					if ( strpos( $sLine, '*' ) !== 0 ) {
						return [];
					}
					if ( strlen( $sLine ) > self::$iChecklistMaxItemLength ) {
						return [];
					}
					$sNewLine = trim( substr( $sLine, 1 ) );
					$aOptions[] = $sNewLine;
				}
			}
		}
		return $aOptions;
	}

	/**
	 * http://www.php.net/manual/en/function.preg-replace.php#112400
	 * @param mixed $pattern
	 * @param mixed $replacement
	 * @param mixed $subject
	 * @param int $nth
	 * @return mixed
	 */
	public static function preg_replace_nth( $pattern, $replacement, $subject, $nth = 1 ) {
		return preg_replace_callback( $pattern,
			function ( $found ) use ( &$pattern, &$replacement, &$nth ) {
					$nth--;
					if ( $nth == 0 ) {
						$sResult = preg_replace( '/value=".*?" /', '', reset( $found ) );
						$sResult = preg_replace( $pattern, $replacement, $sResult );
						return $sResult;
					}
					return reset( $found );
			}, $subject, $nth );
	}

	/**
	 *
	 * @param Parser &$parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:checklist', '\BlueSpice\Checklist\Extension::onMagicWordBsChecklist' );
		return true;
	}

	/**
	 * handle tag "bs:checkbox"
	 * @param type $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return type
	 */
	public static function onMagicWordBsChecklist( $input, array $args, \Parser $parser,
		\PPFrame $frame ) {
		$parser->getOutput()->updateCacheExpiry( 0 );
		$parser->getOutput()->setProperty( 'bs-tag-checklist', 1 );
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

				$sOption = $aOptionSet[0];
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
		$sOut = implode( $sOut, '' );
		$sOut = str_replace( '{color}', $sSelectColor, $sOut );
		return $sOut;
	}

	/**
	 *
	 * @return int
	 */
	protected static function getNewCheckboxId() {
		self::$iCheckboxCounter++;
		return self::$iCheckboxCounter;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array &$aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public static function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:checklist'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-checklist'
			]
		];
	}

}
