<?php
/*
 * Test BlueSpiceChecklist API Endpoints
 */

use MediaWiki\Tests\Api\ApiTestCase;

/**
 * @group BlueSpiceChecklist
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */
class BSApiChecklistAvailableOptionsStoreTest extends ApiTestCase {

	/**
	 * @covers \BSApiChecklistAvailableOptionsStore::execute
	 * @return array
	 */
	public function testMakeData() {
		$data = $this->doApiRequest( [
			'action' => 'bs-checklist-available-options-store'
		] );

		$this->assertArrayHasKey( 'total', $data[0] );
		$this->assertArrayHasKey( 'results', $data[0] );

		return $data;
	}

}
