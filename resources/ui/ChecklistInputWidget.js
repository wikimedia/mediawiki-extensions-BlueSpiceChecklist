bs.util.registerNamespace( 'bs.checklist.ui' );

bs.checklist.ui.ChecklistInputWidget = function ( config ) {
	bs.checklist.ui.ChecklistInputWidget.super.call( this, config );
	this.loading = false;
	this.setDisabled( true );
};

OO.inheritClass( bs.checklist.ui.ChecklistInputWidget, OO.ui.DropdownInputWidget );

bs.checklist.ui.ChecklistInputWidget.prototype.setInspector = function ( inspector ) {
	this.inspector = inspector;
	this.getChecklists().done(
		( options ) => {
			this.loadChecklistsDone( options );
		}
	);
};

bs.checklist.ui.ChecklistInputWidget.prototype.getChecklists = function () {
	const dfd = $.Deferred();
	this.setLoading( true );
	this.inspector.pushPending();
	bs.api.store.getData( 'checklist-template' ).done( ( response ) => {
		const results = response.results;
		const options = [];
		for ( let i = 0; i < results.length; i++ ) {
			options.push( {
				data: results[ i ].id,
				label: results[ i ].text
			} );
		}
		dfd.resolve( options );
	} );
	return dfd.promise();
};

bs.checklist.ui.ChecklistInputWidget.prototype.loadChecklistsDone = function ( options ) {
	this.setOptions( options );
	this.setDisabled( false );
	this.setLoading( false );
	this.inspector.popPending();
	const attrValue = this.inspector.selectedNode.getAttribute( 'mw' ).attrs.list;
	this.setValue( attrValue || '' );
};

bs.checklist.ui.ChecklistInputWidget.prototype.setValue = function ( value ) {
	if ( this.isLoading() ) {
		return;
	}
	bs.checklist.ui.ChecklistInputWidget.super.prototype.setValue.call( this, value );
};

bs.checklist.ui.ChecklistInputWidget.prototype.setLoading = function ( value ) {
	this.loading = value;
};

bs.checklist.ui.ChecklistInputWidget.prototype.isLoading = function () {
	return this.loading;
};
