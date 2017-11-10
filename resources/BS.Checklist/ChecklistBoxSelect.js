Ext.define( 'BS.Checklist.ChecklistBoxSelect', {
	extend:'Ext.ux.form.field.BoxSelect',
	requires: [ 'BS.store.BSApi' ],
	displayField: 'text',
	valueField: 'text',
	anchor: '95%',
	growMin: 150,
	pinList: true,
	triggerOnClick: true,
	triggerAction: 'all',
	filterPickList: true,
	stacked: true,
	forceSelection: false,
	createNewOnEnter: true,
	queryMode: 'local',
	delimiter: ',',
	deferredSetValueConf: false,
	initComponent: function() {
		this.store = new BS.store.BSApi({
			apiAction: 'bs-checklist-available-options-store',
			fields: [ 'text' ]
		});
		this.store.load();

		this.callParent(arguments);
	}
});
