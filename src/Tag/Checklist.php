<?php

namespace BlueSpice\Checklist\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\FormLoaderSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringValue;

class Checklist extends GenericTag {

	/** @var int */
	private int $count = 0;

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:checklist' ];
	}

	/**
	 * @return bool
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		$id = $this->count++;
		return new ChecklistHandler(
			$services->getWikiPageFactory(),
			$services->getTitleFactory(),
			$id,
			'list'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$list = new StringValue();
		$value = new StringValue();

		return [
			'list' => $list,
			'value' => $value,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getResourceLoaderModules(): ?array {
		return [ 'ext.bluespice.checklist.view' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return new ClientTagSpecification(
			'Checklist',
			Message::newFromKey( 'bs-checklist-tag-checklist-desc' ),
			new FormLoaderSpecification(
				'bs.checklist.ui.ChecklistInspectorForm',
				[ 'ext.bluespice.checklist.tag' ]
			),
			Message::newFromKey( 'bs-checklist-ve-checklistinspector-title' )
		);
	}
}
