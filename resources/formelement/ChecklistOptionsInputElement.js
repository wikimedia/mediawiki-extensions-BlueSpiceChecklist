bs.util.registerNamespace( 'bs.checklist.formelement' );

bs.checklist.formelement.ChecklistOptionsInputElement = function () {
	bs.checklist.formelement.ChecklistOptionsInputElement.parent.call( this );
};

OO.inheritClass( bs.checklist.formelement.ChecklistOptionsInputElement, mw.ext.forms.formElement.InputFormElement );

bs.checklist.formelement.ChecklistOptionsInputElement.prototype.getType = function () {
	return 'bs_checklist_options';
};

bs.checklist.formelement.ChecklistOptionsInputElement.prototype.getWidgets = function () {
	return {
		view: OO.ui.LabelWidget,
		edit: bs.checklist.ui.ChecklistOptionsInputWidget
	};
};

bs.checklist.formelement.ChecklistOptionsInputElement.prototype.isSystemElement = function () {
	return true;
};

bs.checklist.formelement.ChecklistOptionsInputElement.prototype.isHidden = function () {
	return true;
};

bs.checklist.formelement.ChecklistOptionsInputElement.prototype.getDisplayName = function () {
	return mw.message( 'bs-bookshelf-formelement-book-input' ).text();
};

mw.ext.forms.registry.Type.register( 'bs_checklist_options', new bs.checklist.formelement.ChecklistOptionsInputElement() );
