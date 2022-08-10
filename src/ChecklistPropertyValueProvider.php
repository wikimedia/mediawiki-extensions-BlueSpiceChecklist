<?php

namespace BlueSpice\Checklist;

use BlueSpice\SMWConnector\PropertyValueProvider;
use SMWDataItem;
use TextContent;

class ChecklistPropertyValueProvider extends PropertyValueProvider {

	/**
	 * @return string
	 */
	public function getAliasMessageKey() {
		return "prefs-checklist-sesp-alias";
	}

	/**
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "prefs-checklist-sesp-desc";
	}

	/**
	 * @return int
	 */
	public function getType() {
		return SMWDataItem::TYPE_BOOLEAN;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return '_CHECKLIST';
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return "Checklist";
	}

	/**
	 * @param \SESP\AppFactory $appFactory
	 * @param \SMW\DIProperty $property
	 * @param \SMW\SemanticData $semanticData
	 * @return null
	 */
	public function addAnnotation( $appFactory, $property, $semanticData ) {
		$wikiPage = $appFactory->newWikiPage( $semanticData->getSubject()->getTitle() );
		// parse wikipage for bs:checklist tag
		if ( $wikiPage !== null && $wikiPage->getContent() !== null ) {
			$contentObj = $wikiPage->getContent();
			$content = ( $contentObj instanceof TextContent ) ? $contentObj->getText() : '';
			$isChecklistFound = $this->isChecklistFound( $content );
			$semanticData->addPropertyObjectValue( $property, new \SMWDIBoolean( $isChecklistFound ) );
		}

		return null;
	}

	private function isChecklistFound( $nativeData ) {
		return ( strpos( $nativeData, "<bs:checklist" ) === false ) ? false : true;
	}
}
