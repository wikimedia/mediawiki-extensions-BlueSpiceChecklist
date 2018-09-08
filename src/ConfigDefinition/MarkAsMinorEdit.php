<?php

namespace BlueSpice\Checklist\ConfigDefinition;

class MarkAsMinorEdit extends \BlueSpice\ConfigDefinition\BooleanSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_QUALITY_ASSURANCE . '/BlueSpiceChecklist',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceChecklist/' . static::FEATURE_QUALITY_ASSURANCE ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceChecklist',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-checklist-config-markasminoredit';
	}
}
