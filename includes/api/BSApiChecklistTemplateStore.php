<?php

use MediaWiki\Title\Title;

class BSApiChecklistTemplateStore extends BSApiExtJSStoreBase {
	/**
	 *
	 * @param string $sQuery
	 * @return \stdClass[]
	 */
	protected function makeData( $sQuery = '' ) {
		$aTemplateData = [];
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			[ 'page' ],
			[ 'page_namespace', 'page_title' ],
			[
				'page_namespace' => NS_TEMPLATE
			],
			__METHOD__
		);

		$aTitles = [];
		foreach ( $res as $row ) {
			$oTitle = Title::makeTitle(
				$row->page_namespace,
				$row->page_title
			);
			// only add those titles that do have actual lists
			$aListOptions = \BlueSpice\Checklist\Extension::getListOptions( $oTitle->getFullText() );
			if ( count( $aListOptions ) > 0 ) {
				$oTemplate = new stdClass();
				$oTemplate->text = $oTitle->getText();
				$oTemplate->leaf = true;
				$oTemplate->id = $oTitle->getPrefixedText();
				$oTemplate->listOptions = $aListOptions;
				$aTemplateData[] = $oTemplate;
			}
		}

		return $aTemplateData;
	}
}
