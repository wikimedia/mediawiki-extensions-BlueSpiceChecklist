bs.util.registerNamespace( 'bs.checklist.formelement' );

bs.checklist.formelement.ChecklistInputElement = function () {
	bs.checklist.formelement.ChecklistInputElement.parent.call( this );
};

OO.inheritClass( bs.checklist.formelement.ChecklistInputElement, mw.ext.forms.formElement.InputFormElement );

bs.checklist.formelement.ChecklistInputElement.prototype.getType = function () {
	return 'bs_checklist_input';
};

bs.checklist.formelement.ChecklistInputElement.prototype.getWidgets = function () {
	return {
		view: OO.ui.LabelWidget,
		edit: bs.checklist.ui.ChecklistInputWidget
	};
};

bs.checklist.formelement.ChecklistInputElement.prototype.isSystemElement = function () {
	return true;
};

bs.checklist.formelement.ChecklistInputElement.prototype.isHidden = function () {
	return true;
};

bs.checklist.formelement.ChecklistInputElement.prototype.getDisplayName = function () {
	return mw.message( 'bs-bookshelf-formelement-book-input' ).text();
};

mw.ext.forms.registry.Type.register( 'bs_checklist_input', new bs.checklist.formelement.ChecklistInputElement() );
