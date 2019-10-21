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
class BSApiChecklistAvailableOptionsStoreTest extends ApiTestCase {

	/**
	 * Anything that needs to happen before your tests should go here.
	 */
	protected function setUp() : void {
		// Be sure to do call the parent setup and teardown functions.
		// This makes sure that all the various cleanup and restorations
		// happen as they should (including the restoration for setMwGlobals).
		parent::setUp();
		$this->doLogin();
	}

	/**
	 * Anything cleanup you need to do should go here.
	 */
	protected function tearDown() : void {
		parent::tearDown();
	}

	public function testMakeData() {
		$data = $this->doApiRequest( [
			'action' => 'bs-checklist-available-options-store'
		] );

		$this->assertArrayHasKey( 'total', $data[0] );
		$this->assertArrayHasKey( 'results', $data[0] );

		return $data;
	}

}
