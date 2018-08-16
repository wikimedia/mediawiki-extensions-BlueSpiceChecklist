<?php

class BSApiChecklistTemplateStore extends BSApiExtJSStoreBase {
	protected function makeData($sQuery = '') {
		$aTemplateData = array();
		$dbr = wfGetDB(DB_REPLICA);
		$res = $dbr->select(
			array( 'page' ),
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => NS_TEMPLATE
			)
		);

		$aTitles = array();
		foreach( $res as $row ) {
			$oTitle = Title::makeTitle(
				$row->page_namespace,
				$row->page_title
			);
			// only add those titles that do have actual lists
			$aListOptions = \BlueSpice\Checklist\Extension::getListOptions( $oTitle->getFullText() );
			if ( sizeof( $aListOptions ) > 0 ) {
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