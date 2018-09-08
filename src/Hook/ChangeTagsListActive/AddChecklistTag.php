<?php

namespace BlueSpice\Checklist\Hook\ChangeTagsListActive;

use BlueSpice\Hook\ChangeTagsListActive;

class AddChecklistTag extends ChangeTagsListActive {

	protected function doProcess() {
		$this->tags[] = 'bs-checklist-change';
	}

}