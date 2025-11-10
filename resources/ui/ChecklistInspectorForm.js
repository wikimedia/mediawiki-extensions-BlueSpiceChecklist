bs.util.registerNamespace( 'bs.checklist.ui' );

bs.checklist.ui.ChecklistInspectorForm = function ( config ) {
	bs.checklist.ui.ChecklistInspectorForm.super.call( this, {
		definition: {
			buttons: []
		}
	} );
	this.inspector = config.inspector;
};

OO.inheritClass( bs.checklist.ui.ChecklistInspectorForm, mw.ext.forms.standalone.Form );

bs.checklist.ui.ChecklistInspectorForm.prototype.makeItems = function () {
	return [
		{
			type: 'bs_checklist_input',
			name: 'list',
			label: mw.msg( 'bs-checklist-tag-checklist-attr-list-label' ),
			help: mw.msg( 'bs-checklist-tag-checklist-attr-list-help' ),
			widget_$overlay: true, // eslint-disable-line camelcase
			widget_listeners: { // eslint-disable-line camelcase
				change: function ( value ) {
					this.getItem( 'value' ).updateChecklistOptions( value );
				}
			},
			labelAlign: 'top'
		},
		{
			type: 'bs_checklist_options',
			name: 'value',
			label: mw.msg( 'bs-checklist-tag-checklist-attr-value-label' ),
			help: mw.msg( 'bs-checklist-tag-checklist-attr-value-help' ),
			widget_$overlay: true, // eslint-disable-line camelcase
			labelAlign: 'top'
		}
	];
};

bs.checklist.ui.ChecklistInspectorForm.prototype.onParseComplete = function ( form, items ) {
	bs.checklist.ui.ChecklistInspectorForm.super.prototype.onParseComplete.call( this, form, items );
	for ( const key in items ) {
		items[ key ].setInspector( this.inspector );
	}
};
