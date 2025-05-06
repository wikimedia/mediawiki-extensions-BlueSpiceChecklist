bs.util.registerNamespace( 'bs.chklst.util.tag' );
bs.chklst.util.tag.CheckboxDefinition = function BsVecUtilTagCheckboxDefinition() {
	bs.chklst.util.tag.CheckboxDefinition.super.call( this );
};

OO.inheritClass( bs.chklst.util.tag.CheckboxDefinition, bs.chklst.util.tag.ChecklistDefinition );

bs.chklst.util.tag.CheckboxDefinition.prototype.getCfg = function () {
	const cfg = bs.chklst.util.tag.CheckboxDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'Checkbox',
		name: 'checkbox',
		tagname: 'bs:checkbox',
		descriptionMsg: 'bs-checkbox-tag-checkbox-desc',
		menuItemMsg: 'bs-checkbox-ve-checkboxinspector-title',
		toolGroup: 'object',
		tabbed: false,
		tabs: [ {
			name: 'check',
			labelMsg: 'bs-checklist-tag-checklist-tab-checkbox',
			value: 'check'
		} ],
		attributes: [ {
			name: 'checked',
			labelMsg: 'bs-checklist-ve-checklistinspector-cb-checked',
			helpMsg: 'bs-checklist-tag-checklist-attr-checked-help',
			type: 'toggle',
			default: 'false',
			tab: 'check'
		} ]
	} );
};

bs.vec.registerTagDefinition(
	new bs.chklst.util.tag.CheckboxDefinition()
);
