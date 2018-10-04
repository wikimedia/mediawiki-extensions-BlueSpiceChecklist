bs.util.registerNamespace( 'bs.chklst.util.tag' );
bs.chklst.util.tag.ChecklistDefinition = function BsVecUtilTagChecklistDefinition() {
	bs.chklst.util.tag.ChecklistDefinition.super.call( this );
};

OO.inheritClass( bs.chklst.util.tag.ChecklistDefinition, bs.vec.util.tag.Definition );

bs.chklst.util.tag.ChecklistDefinition.prototype.getCfg = function() {
	var cfg = bs.chklst.util.tag.ChecklistDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'Checklist',
		name: 'checklist',
		tagname: 'bs:checklist',
		descriptionMsg: 'bs-checklist-tag-checklist-desc',
		menuItemMsg: 'bs-checklist-ve-checklistinspector-title',
		toolGroup: 'object',
		tabbed: true,
		tabs: [{
			name: 'check',
			labelMsg: 'bs-checklist-tag-checklist-tab-checkbox',
			value: 'check'
		},{
			name: 'list',
			labelMsg: 'bs-checklist-tag-checklist-tab-list',
			value: 'list'
		}],
		attributes: [{
			name: 'type',
			type: 'tab',
			default: 'check'
		},{
			name: 'checked',
			labelMsg: 'bs-checklist-ve-checklistinspector-cb-checked',
			helpMsg: 'bs-checklist-tag-checklist-attr-checked-help',
			type: 'toggle',
			default: 'false',
			tab: 'check'
		},{
			name: 'list',
			labelMsg: 'bs-checklist-tag-checklist-attr-list-label',
			helpMsg: 'bs-checklist-tag-checklist-attr-list-help',
			type: 'custom',
			widgetClass: bs.chklst.ui.ChecklistInputWidget,
			default: '',
			tab: 'list'
		},{
			name: 'value',
			labelMsg: 'bs-checklist-tag-checklist-attr-value-label',
			helpMsg: 'bs-checklist-tag-checklist-attr-value-help',
			type: 'custom',
			widgetClass: bs.chklst.ui.ChecklistOptionsInputWidget,
			default: '',
			tab: 'list'
		}]
	});
};

bs.vec.registerTagDefinition(
	new bs.chklst.util.tag.ChecklistDefinition()
);
