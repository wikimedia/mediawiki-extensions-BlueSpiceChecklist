<?php

namespace BlueSpice\Checklist\Hook;

use BlueSpice\Checklist\Tag\Checkbox;
use BlueSpice\Checklist\Tag\Checklist;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new Checklist();
		$tags[] = new Checkbox();
	}
}
