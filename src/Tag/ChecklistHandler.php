<?php

namespace BlueSpice\Checklist\Tag;

use MediaWiki\Content\TextContent;
use MediaWiki\Html\Html;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use WikiPage;

class ChecklistHandler implements ITagHandler {

	/** @var int */
	private int $maxLength = 60;

	/**
	 * @param WikiPageFactory $wikiPageFactory
	 * @param TitleFactory $titleFactory
	 * @param int $counter
	 */
	public function __construct(
		private readonly WikiPageFactory $wikiPageFactory,
		private readonly TitleFactory $titleFactory,
		private int $counter,
		private readonly string $type
	) {
	}

	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$parserOutput = $parser->getOutput();
		$parserOutput->updateCacheExpiry( 0 );
		$parserOutput->setPageProperty( 'bs-tag-checklist', '1' );

		$this->counter++;
		$id = $this->counter;
		$out = [];
		$options = [];

		if ( isset( $params['list'] ) ) {
			$options = $this->getListOptions( $params['list'] );
		}
		if ( !isset( $params['value'] ) || $params['value'] === 'false' ) {
			$params['value'] = '';
		}
		if ( !isset( $params['checked'] ) ) {
			$params['checked'] = '';
		}

		$selectColor = '';
		if ( $this->type === 'list' ) {
			$default = empty( $params['value'] ) ? true : false;
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
				if ( $default || $params['value'] == $optSet ) {
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
			if ( $params['value'] == 'checked' || $params['checked'] == 'true' ) {
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
					if ( strlen( $line ) > $this->maxLength ) {
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
