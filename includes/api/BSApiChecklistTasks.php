<?php

class BSApiChecklistTasks extends BSApiTasksBase {

	protected $aTasks = array(
		'doChangeCheckItem' => [
			'examples' => [
				[
					'pos' => '2',
					'value' => 'true'
				]
			],
			'params' => [
				'pos' => [
					'desc' => 'Integer value of target checkbox position',
					'type' => 'string',
					'required' => true
				],
				'value' => [
					'desc' => 'Value of checkbox in form of "true"/"false"',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'saveOptionsList' => [
			'examples' => [
				[
					'title' => 'ChecklistTest',
					'records' => [ 'a', 'b', 'c' ]
				]
			],
			'params' => [
				'title' => [
					'desc' => 'Valid title in NS_TEMPLATE namespace',
					'type' => 'string',
					'required' => true
				],
				'records' => [
					'desc' => 'Array of items for checklist',
					'type' => 'array',
					'required' => true
				]
			]
		]
	);

	protected function getRequiredTaskPermissions() {
		return array(
			'doChangeCheckItem' => array( 'checklistmodify' ),
			'saveOptionsList' => array( 'edit' )
		);
	}

	protected $sTaskLogType = 'bs-checklist';

	public function task_doChangeCheckItem( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();
		$iPos = (int)$oTaskData->pos;
		if ( $iPos == 0 ) {
			return $oResponse;
		}

		$sValue = $oTaskData->value;
		if ( $sValue === '' ) {
			return $oResponse;
		}

		$sArticleId = $this->getTitle()->getArticleID();
		if ( $sArticleId == 0 ) {
			return $oResponse;
		}

		$oWikiPage = WikiPage::newFromID( $sArticleId );
		$oContent = $oWikiPage->getContent();
		$sContent = $oContent->getNativeData();

		$bChecked = null;
		// Maybe a sanity-check is just enough here
		$sNewValue = 'value="';
		if ( $sValue === true ) {
			$sNewValue .= "checked";
			$summary = wfMessage( "bs-checklist-summary-checked", $iPos )->plain();
			$bChecked = true;
		}
		else if ( $sValue === false ) {
			$bChecked = false;
			$sNewValue .= "";
			$summary = wfMessage( "bs-checklist-summary-unchecked", $iPos )->plain();
		}
		else {
			$sNewValue .= $sValue;
			$summary = wfMessage( "bs-checklist-summary-changed", $iPos, $sValue )->plain();
		}

		$sNewValue .= '" ';

		$sContent = \BlueSpice\Checklist\Extension::preg_replace_nth( "/(<bs:checklist )([^>]*?>)/", "$1" . $sNewValue . "$2", $sContent, $iPos );

		$oContentHandler = $oContent->getContentHandler();
		$oNewContent = $oContentHandler->makeContent( $sContent, $oWikiPage->getTitle() );
		if ( $this->getConfig()->get( 'ChecklistMarkAsMinorEdit' ) ) {
			$flags = EDIT_MINOR;
		} else {
			$flags = 0;
		}
		$oResult = $oWikiPage->doEditContent( $oNewContent, $summary, $flags, false, null, null, [ 'bs-checklist-change' ] );

		// Create a log entry for the changes on the checklist values
		if ( !is_null( $bChecked ) ) {
			if ( $bChecked ) {
				$this->logTaskAction( 'checked', array(
					'4::position' => $iPos
				));
			}
			else {
				$this->logTaskAction( 'unchecked', array(
					'4::position' => $iPos
				));
			}
		}
		else {
			$this->logTaskAction( 'selected', array(
					'4::position' => $iPos,
					'5::selected' => $sValue
			));
		}

		$oResponse->success = true;
		$this->runUpdates();

		return $oResponse;
	}

	public function task_saveOptionsList( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$oTitle = Title::newFromText( $oTaskData->title, NS_TEMPLATE );

		if ( $oTitle instanceof Title === false ) {
			$oResponse->message = wfMessage( "bs-checklist-savelist-error-invalid-title" )->plain();
			return $oResponse;
		}

		if ( !$oTitle->userCan( 'edit' ) ) {
			$oResponse->message = wfMessage( "bs-checklist-savelist-error-edit-not-permitted" )->plain();
			return $oResponse;
		}

		$sContent = '';
		foreach( $oTaskData->records as $record ) {
			$sContent .= '* ' . $record . "\n";
		}

		$sSummary = wfMessage( "bs-checklist-update-list" )->plain();

		$oWikiPage = WikiPage::factory( $oTitle );
		$oContentHandler = $oWikiPage->getContentHandler();
		$oNewContent = $oContentHandler->makeContent( $sContent, $oWikiPage->getTitle() );
		$oResult = $oWikiPage->doEditContent( $oNewContent, $sSummary );

		if ( $oResult->isGood() ) {
			$oResponse->success = true;
		}
		else {
			$oResponse->message = $oResult->getMessage()->plain();
		}

		return $oResponse;
	}

}
