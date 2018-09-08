<?php

namespace BlueSpice\Checklist\Hook\ListDefinedTags;

use BlueSpice\Hook\ListDefinedTags;

class AddChecklistTag extends ListDefinedTags {

	protected function doProcess() {
		$this->tags[] = 'bs-checklist-change';
	}

}