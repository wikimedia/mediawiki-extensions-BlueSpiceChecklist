<?php

class BSApiChecklistAvailableOptionsStore extends BSApiExtJSStoreBase {
	protected function makeData( $sQuery = '' ) {

		$aData = array();
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			array( 'page' ),
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => NS_TEMPLATE
			)
		);

		$aAvailableOptions = array();
		foreach( $res as $row ) {
			$oTitle = Title::makeTitle(
				$row->page_namespace,
				$row->page_title
			);
			// only add those titles that do have actual lists
			$aListOptions = \BlueSpice\Checklist\Extension::getListOptions( $oTitle->getFullText() );
			if (sizeof( $aListOptions ) > 0 ) {
				$aAvailableOptions = array_merge( $aAvailableOptions, $aListOptions );
			}
		}
		foreach ( $aAvailableOptions as $sOption ) {
			$oTemplate = new stdClass();
			$oTemplate->text = $sOption;
			$oTemplate->leaf = true;
			$oTemplate->id = $sOption;
			$aData[] = $oTemplate;
		}

		return $aData;
	}

}
