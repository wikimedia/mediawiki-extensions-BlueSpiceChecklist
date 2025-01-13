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
class BSApiChecklistTemplateStoreTest extends ApiTestCase {

	/**
	 * @covers \BSApiChecklistTemplateStore::execute
	 * @return array
	 */
	public function testMakeData() {
		$data = $this->doApiRequest( [
			'action' => 'bs-checklist-template-store'
		] );

		$this->assertArrayHasKey( 'total', $data[0] );
		$this->assertArrayHasKey( 'results', $data[0] );

		return $data;
	}

}
