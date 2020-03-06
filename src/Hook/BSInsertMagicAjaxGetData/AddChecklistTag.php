<?php

namespace BlueSpice\Checklist\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddChecklistTag extends BSInsertMagicAjaxGetData {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:checklist',
			'type' => 'tag',
			'name' => 'checklist',
			'desc' => $this->msg( 'bs-checklist-tag-checklist-desc' )->text(),
			'code' => '<bs:checklist />',
			'mwvecommand' => 'checklistCommand',
			'previewable' => false,
			'examples' => [ [
				'label' => $this->msg( 'bs-checklist-tag-checklist-example-check' )->text(),
				'code' => '<bs:checklist type="check" value="checked" />'
			], [
				'label' => $this->msg( 'bs-checklist-tag-checklist-example-list' )->text(),
				'code' => '<bs:checklist type="list" value="false" list="Status" />'
			] ],
			'helplink' => $this->getHelpLink()
		];

		return true;
	}

	/**
	 *
	 * @return string
	 */
	private function getHelpLink() {
		return $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceChecklist' )->getUrl();
	}

}
