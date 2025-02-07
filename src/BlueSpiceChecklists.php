<?php

namespace BlueSpice\Checklist;

use MediaWiki\Content\TextContent;
use MediaWiki\Html\Html;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use WikiPage;

class BlueSpiceChecklists {

	/** @var int */
	private $counter = 0;

	/** @var int */
	private $iChecklistMaxItemLength = 60;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/**
	 *
	 * @param TitleFactory $titleFactory
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct( TitleFactory $titleFactory, WikiPageFactory $wikiPageFactory ) {
		$this->titleFactory = $titleFactory;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/**
	 * handle tag "bs:checklist"
	 * with splitting tags type value is not set automatically
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public function onBsChecklist( $input, array $args, Parser $parser,
		PPFrame $frame ) {
		if ( !isset( $args['type'] ) ) {
			$args['type'] = 'list';
		}
		return $this->onMagicWordBsChecklist( $input, $args, $parser, $frame );
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
	public function onMagicWordBsChecklist( $input, array $args, Parser $parser,
		PPFrame $frame ) {
		$parserOutput = $parser->getOutput();
		$parserOutput->addModules( [ 'ext.bluespice.checklist.view' ] );
		$parserOutput->updateCacheExpiry( 0 );
		$parserOutput->setPageProperty( 'bs-tag-checklist', 1 );

		$this->counter++;
		$id = $this->counter;
		$out = [];
		$options = [];

		if ( isset( $args['list'] ) ) {
			$options = $this->getListOptions( $args['list'] );
		}
		if ( !isset( $args['value'] ) || $args['value'] === 'false' ) {
			$args['value'] = '';
		}
		if ( !isset( $args['checked'] ) ) {
			$args['checked'] = '';
		}

		$selectColor = '';
		if ( isset( $args['type'] ) && $args['type'] == 'list' ) {
			$default = empty( $args['value'] ) ? true : false;
			$setColor = '';
			foreach ( $options as $option ) {
				$optionset = explode( "|", $option );

				if ( !$selectColor && isset( $optionset[1] ) ) {
					$selectColor = "style='color:" . $optionset[1] . ";' ";
				}

				$optSet = trim( $optionset[0] );
				$out[] = "<option ";
				if ( isset( $optionset[1] ) ) {
					$out[] = "style='color:" . $optionset[1] . ";' ";
				}
				if ( $default || $args['value'] == $optSet ) {
					$default = false;
					$out[] = "selected='selected'";
					if ( isset( $optionset[1] ) ) {
						$selectColor = "style='color:" . $optionset[1] . ";' ";
						$setColor = $optionset[1];
					}
				}
				$out[] = ">";
				$out[] = $optSet;
				$out[] = "</option>";
			}
			$output = Html::rawElement( 'select', [
				'style' => "color: $setColor",
				'id' => "bs-cb-$id",
				'class' => "bs-checklist-list"
			], implode( '', $out ) );
		} else {
			$checked = false;
			if ( $args['value'] == 'checked' || $args['checked'] == 'true' ) {
				$checked = true;
			}
			$output = Html::element( 'input',
			[
				'type' => 'checkbox',
				'id' => "bs-cb-$id",
				'class' => 'bs-checklist-item',
				'checked' => $checked
			] );
		}

		return $output;
	}

	/**
	 * @param string $listTitle
	 * @return array
	 */
	public function getListOptions( $listTitle ) {
		$options = [];

		$title = $this->titleFactory->newFromText( $listTitle, NS_TEMPLATE );
		if ( $title instanceof Title && $title->exists() ) {
			$wikipage = $this->wikiPageFactory->newFromID( $title->getArticleID() );
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
					if ( strlen( $line ) > $this->iChecklistMaxItemLength ) {
						return [];
					}
					$newLine = trim( substr( $line, 1 ) );
					$options[] = $newLine;
				}
			}
		}
		return $options;
	}
}
