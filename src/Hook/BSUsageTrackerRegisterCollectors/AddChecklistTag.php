<?php

namespace BlueSpice\Checklist\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddChecklistTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:checklist'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-checklist'
			]
		];
	}

}
