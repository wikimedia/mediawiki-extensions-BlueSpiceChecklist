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
	protected function setUp() {
		// Be sure to do call the parent setup and teardown functions.
		// This makes sure that all the various cleanup and restorations
		// happen as they should (including the restoration for setMwGlobals).
		global $wgGroupPermissions;
		$wgGroupPermissions['*']['read'] = true;
		$wgGroupPermissions['*']['api'] = true;
		$wgGroupPermissions['*']['writeapi'] = true;
		parent::setUp();
		$this->doLogin();
		$this->insertPage( "Test", "<bs:checklist />" );
	}

	function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	/**
	 * Anything cleanup you need to do should go here.
	 */
	protected function tearDown() {
		parent::tearDown();
	}

	public function testTask_doChangeCheckItem() {
		$tokens = $this->getTokens();

		$data = $this->doApiRequest( [
			'action' => 'bs-checklist-tasks',
			'token' => $tokens[ 'edittoken' ],
			'task' => 'doChangeCheckItem',
			'taskData' => json_encode( [
				'pos' => '1',
				'value' => 'true'
			] ),
			'context' => json_encode( ['wgTitle' => 'Test' ] )
		  ] );

		$this->assertEquals( true, $data[ 0 ][ 'success' ] );

		return $data;
	}

	public function testTask_saveOptionsList() {
		$tokens = $this->getTokens();

		$oTitle = Title::makeTitle( NS_TEMPLATE, 'Test' );
		$this->assertEquals( false, $oTitle->exists() );

		$arrRecords = ['a', 'b', 'c' ];

		$data = $this->doApiRequest( [
			'action' => 'bs-checklist-tasks',
			'token' => $tokens[ 'edittoken' ],
			'task' => 'saveOptionsList',
			'taskData' => json_encode( [
				'title' => $oTitle->getText(),
				'records' => $arrRecords
			] ),
		  ] );

		$this->assertEquals( true, $data[ 0 ][ 'success' ] );

		$oTitleAfter = Title::makeTitle( NS_TEMPLATE, 'Test' );
		$this->assertEquals( true, $oTitleAfter->exists() );

		$sContent = WikiPage::newFromID( $oTitleAfter->getArticleID() )->getContent()->getNativeData();

		foreach ( $arrRecords as $record ) {
			$this->assertContains( "* " . $record, $sContent );
		}

		return $data;
	}

}
