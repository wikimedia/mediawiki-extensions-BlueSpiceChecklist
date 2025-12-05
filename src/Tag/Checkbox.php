<?php

namespace BlueSpice\Checklist\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;

class Checkbox extends GenericTag {

	/** @var int */
	private int $count = 0;

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:checkbox' ];
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
			'checkbox'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$checked = new BooleanValue();

		return [
			'checked' => $checked,
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
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'checkbox',
				'name' => 'checked',
				'label' => Message::newFromKey( 'bs-checklist-ve-checklistinspector-cb-checked' )->text(),
				'help' => Message::newFromKey( 'bs-checklist-tag-checklist-attr-checked-help' )->text(),
			]
		] );

		return new ClientTagSpecification(
			'Checkbox',
			Message::newFromKey( 'bs-checkbox-tag-checkbox-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-checkbox-ve-checkboxinspector-title' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'span';
	}
}
