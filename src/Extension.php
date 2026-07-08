<?php

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
