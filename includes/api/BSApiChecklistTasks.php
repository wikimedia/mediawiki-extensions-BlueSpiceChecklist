<?php

use BlueSpice\Api\Response\Standard;
use BlueSpice\Checklist\Extension as Checklist;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\TextContent;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

class BSApiChecklistTasks extends BSApiTasksBase {

	/**
	 * @var array
	 */
	protected $aTasks = [
		'doChangeCheckItem' => [
			'examples' => [
				[
					'pos' => '2',
					'value' => 'true',
					'type' => 'check'
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
				],
				'type' => [
					'desc' => 'Type of the checklist: list or check',
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
	];

	/**
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'doChangeCheckItem' => [ 'checklistmodify' ],
			'saveOptionsList' => [ 'edit' ]
		];
	}

	/**
	 * @var string
	 */
	protected $sTaskLogType = 'bs-checklist';

	/**
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_doChangeCheckItem( $oTaskData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$oResponse = $this->makeStandardReturn();
		$iPos = (int)$oTaskData->pos;
		if ( $iPos == 0 ) {
			return $oResponse;
		}
		$title = $this->getTitle();
		if ( !$title ) {
			return $oResponse;
		}

		$type = property_exists( $oTaskData, 'type' ) ? $oTaskData->type : 'check';
		$value = property_exists( $oTaskData, 'value' ) ? $oTaskData->value : '';

		if ( $type === 'check' ) {
			$value = (bool)$value;
		}

		$sArticleId = $title->getArticleID();
		if ( $sArticleId == 0 ) {
			return $oResponse;
		}

		$oWikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromID( $sArticleId );
		$contentObj = $oWikiPage->getContent();
		$content = ( $contentObj instanceof TextContent ) ? $contentObj->getText() : '';

		$newValue = $this->getNewValue( $value, $type );
		$summary = $this->getSummary( $value, $type, $iPos );

		$pattern = "/(<bs:checklist )([^>]*?>)/";
		if ( $type === 'check' ) {
			$pattern = "/(<bs:checkbox )([^>]*?>)/";
		}

		$newContent = Checklist::preg_replace_nth(
			$pattern,
			"$1" . $newValue . "$2",
			$content,
			$iPos
		);
		// If checkbox is set with bs:checklist type=check pattern has to be changed
		if ( $type === 'check' && $newContent === $content ) {
			$pattern = "/(<bs:checklist )([^>]*?>)/";
			$newContent = Checklist::preg_replace_nth(
				$pattern,
				"$1" . $newValue . "$2",
				$newContent,
				$iPos
			);
		}

		$oContentHandler = $contentObj->getContentHandler();
		$oNewContent = $oContentHandler->makeContent( $newContent, $oWikiPage->getTitle() );
		if ( $this->getConfig()->get( 'ChecklistMarkAsMinorEdit' ) ) {
			$flags = EDIT_MINOR;
		} else {
			$flags = 0;
		}
		$user = $this->getUser();
		$updater = $oWikiPage->newPageUpdater( $user );
		$updater->setContent( SlotRecord::MAIN, $oNewContent );
		$comment = CommentStoreComment::newUnsavedComment( $summary );
		$updater->addTag( 'bs-checklist-change' );
		try {
			$updater->saveRevision( $comment, $flags );
		} catch ( Exception $e ) {
			$logger = LoggerFactory::getInstance( 'BlueSpiceChecklist' );
			$logger->error( $e->getMessage() );
		}

		// Create a log entry for the changes on the checklist values

		if ( $type === 'check' ) {
			if ( $value ) {
				$this->logTaskAction( 'checked', [
					'4::position' => $iPos
				] );
			} else {
				$this->logTaskAction( 'unchecked', [
					'4::position' => $iPos
				] );
			}
		} else {
			$this->logTaskAction( 'selected', [
					'4::position' => $iPos,
					'5::selected' => $value
			] );
		}

		$oResponse->success = true;
		$this->runUpdates();

		return $oResponse;
	}

	/**
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_saveOptionsList( $oTaskData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$oResponse = $this->makeStandardReturn();

		$oTitle = Title::newFromText( $oTaskData->title, NS_TEMPLATE );

		if ( $oTitle instanceof Title === false ) {
			$oResponse->message = wfMessage( "bs-checklist-savelist-error-invalid-title" )->text();
			return $oResponse;
		}

		if ( !$this->services->getPermissionManager()
			->userCan( 'edit', $this->getUser(), $oTitle )
		) {
			$oResponse->message = wfMessage( "bs-checklist-savelist-error-edit-not-permitted" )->text();
			return $oResponse;
		}

		$sContent = '';
		foreach ( $oTaskData->records as $record ) {
			$sContent .= '* ' . $record . "\n";
		}

		$sSummary = wfMessage( "bs-checklist-update-list" )->text();

		$oWikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $oTitle );
		$oContentHandler = $oWikiPage->getContentHandler();
		$oNewContent = $oContentHandler->makeContent( $sContent, $oWikiPage->getTitle() );
		$user = $this->getUser();
		$updater = $oWikiPage->newPageUpdater( $user );
		$updater->setContent( SlotRecord::MAIN, $oNewContent );
		$comment = CommentStoreComment::newUnsavedComment( $sSummary );
		try {
			$updater->saveRevision( $comment );
		} catch ( Exception $e ) {
			$logger = LoggerFactory::getInstance( 'BlueSpiceChecklist' );
			$logger->error( $e->getMessage() );
		}
		$oResult = $updater->getStatus();
		if ( $oResult->isGood() ) {
			$oResponse->success = true;
		} else {
			$oResponse->message = $oResult->getMessage()->text();
		}

		return $oResponse;
	}

	/**
	 * @param string|bool $value
	 * @param string $type
	 * @return string
	 */
	protected function getNewValue( $value, $type ) {
		if ( $type === 'check' ) {
			return "checked=\"" . ( $value ? 'true' : 'false' ) . "\" ";
		}
		if ( $type === 'list' ) {
			return "value=\"$value\" ";
		}

		return '';
	}

	/**
	 * @param string|bool $value
	 * @param string $type
	 * @param int $pos
	 * @return string
	 */
	protected function getSummary( $value, $type, $pos ) {
		if ( $type === 'check' ) {
			if ( $value ) {
				return wfMessage( "bs-checklist-summary-checked", $pos )->text();
			} else {
				return wfMessage( "bs-checklist-summary-unchecked", $pos )->text();
			}
		}
		if ( $type === 'list' ) {
			return wfMessage( "bs-checklist-summary-changed", $pos, $value )->text();
		}

		return '';
	}
}
