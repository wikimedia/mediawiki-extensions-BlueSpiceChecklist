bs.util.registerNamespace( 'bs.checklist.ui' );

bs.checklist.ui.ChecklistOptionsInputWidget = function ( config ) {
	bs.checklist.ui.ChecklistOptionsInputWidget.super.call( this, config );
	this.loading = false;
	this.setDisabled( true );
};

OO.inheritClass( bs.checklist.ui.ChecklistOptionsInputWidget, OO.ui.DropdownInputWidget );

bs.checklist.ui.ChecklistOptionsInputWidget.prototype.setInspector = function ( inspector ) {
	this.inspector = inspector;
};

bs.checklist.ui.ChecklistOptionsInputWidget.prototype.updateChecklistOptions = function ( list ) {
	this.setDisabled( true );
	this.setLoading( true );
	this.inspector.pushPending();
	this.getChecklistOptions( list ).done( ( options ) => {
		this.setOptions( options );
		this.setDisabled( false );
		let valueToSet = '';
		const attrValue = this.inspector.selectedNode.getAttribute( 'mw' ).attrs.value;
		if ( typeof attrValue !== 'undefined' ) {
			valueToSet = attrValue.split( '|' ).shift();
		}
		this.setLoading( false );
		this.inspector.popPending();
		this.setValue( valueToSet );
	} );
};

bs.checklist.ui.ChecklistOptionsInputWidget.prototype.getChecklistOptions = function ( value ) {
	const dfd = $.Deferred();
	bs.api.store.getData( 'checklist-template' ).done( ( response ) => {
		const results = response.results;
		const options = [];
		for ( let i = 0; i < results.length; i++ ) {
			const result = results[ i ];
			if ( result.id === value ) {
				for ( let j = 0; j < result.listOptions.length; j++ ) {
					const labelNoColor = result.listOptions[ j ].split( '|' ).shift();
					options.push( {
						data: labelNoColor,
						label: labelNoColor
					} );
				}
			}
		}
		dfd.resolve( options );
	} );
	return dfd.promise();
};

bs.checklist.ui.ChecklistOptionsInputWidget.prototype.setValue = function ( value ) {
	if ( this.isLoading() ) {
		return;
	}
	bs.checklist.ui.ChecklistOptionsInputWidget.super.prototype.setValue.call( this, value );
};

bs.checklist.ui.ChecklistOptionsInputWidget.prototype.setLoading = function ( value ) {
	this.loading = value;
};

bs.checklist.ui.ChecklistOptionsInputWidget.prototype.isLoading = function () {
	return this.loading;
};
