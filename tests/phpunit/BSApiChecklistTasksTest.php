<?php

/*
 * Test BlueSpiceChecklist API Endpoints
 */

/**
 * @group BlueSpiceChecklist
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */
class BSApiChecklistTasksTest extends ApiTestCase {

	/**
	 * Anything that needs to happen before your tests should go here.
	 */
	protected function setUp(): void {
		// Be sure to do call the parent setup and teardown functions.
		// This makes sure that all the various cleanup and restorations
		// happen as they should (including the restoration for setMwGlobals).
		global $wgGroupPermissions;
		$wgGroupPermissions['*']['read'] = true;
		$wgGroupPermissions['*']['api'] = true;
		$wgGroupPermissions['*']['writeapi'] = true;
		parent::setUp();
		$this->insertPage( "Test", "<bs:checklist />" );
	}

	/**
	 * @covers \BSApiChecklistTasks::task_doChangeCheckItem
	 * @return array
	 */
	public function testTask_doChangeCheckItem() {
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-checklist-tasks',
			'task' => 'doChangeCheckItem',
			'taskData' => json_encode( [
				'pos' => '1',
				'value' => 'true'
			] ),
			'context' => json_encode( [ 'wgTitle' => 'Test' ] )
		  ], null, null );

		$this->assertTrue( $data[ 0 ][ 'success' ] );

		return $data;
	}

	/**
	 * @covers \BSApiChecklistTasks::task_saveOptionsList
	 * @return array
	 */
	public function testTask_saveOptionsList() {
		$oTitle = Title::makeTitle( NS_TEMPLATE, 'Test' );
		$this->assertFalse( $oTitle->exists() );

		$arrRecords = [ 'a', 'b', 'c' ];

		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-checklist-tasks',
			'task' => 'saveOptionsList',
			'taskData' => json_encode( [
				'title' => $oTitle->getText(),
				'records' => $arrRecords
			] ),
		  ], null, null, 'csrf' );

		$this->assertTrue( $data[ 0 ][ 'success' ] );

		$oTitleAfter = Title::makeTitle( NS_TEMPLATE, 'Test' );
		$this->assertTrue( $oTitleAfter->exists() );

		$contentObj = $this->getServiceContainer()->getWikiPageFactory()
			->newFromID( $oTitleAfter->getArticleID() )->getContent();
		$content = ( $contentObj instanceof TextContent ) ? $contentObj->getText() : '';

		foreach ( $arrRecords as $record ) {
			$this->assertStringContainsString( "* " . $record, $content );
		}

		return $data;
	}

}
