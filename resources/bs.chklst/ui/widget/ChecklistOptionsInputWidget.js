bs.util.registerNamespace( 'bs.chklst.ui' );

bs.chklst.ui.ChecklistOptionsInputWidget = function BsVecUiChecklistOptionsInputWidget( config ) {
	bs.chklst.ui.ChecklistOptionsInputWidget.super.call( this, config );
	this.inspector = config.inspector;
	this.attribute = config.attribute;
	this.loading = false;
	this.setDisabled( true );
	this.inspector.listInput.on( 'change', this.updateChecklistOptions, [], this );
};

OO.inheritClass( bs.chklst.ui.ChecklistOptionsInputWidget, OO.ui.DropdownInputWidget );

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.updateChecklistOptions = function ( list ) {
	this.setDisabled( true );
	this.setLoading( true );
	this.inspector.pushPending();
	this.getChecklistOptions( list ).done( ( options ) => {
		this.setOptions( options );
		this.setDisabled( false );
		let valueToSet = '';
		const attrValue = this.inspector.selectedNode.getAttribute( 'mw' ).attrs[ this.attribute.name ];
		if ( typeof this.attribute.default !== 'undefined' ) {
			valueToSet = this.attribute.default.split( '|' ).shift();
		}
		if ( typeof attrValue !== 'undefined' ) {
			valueToSet = attrValue.split( '|' ).shift();
		}
		this.setLoading( false );
		this.inspector.popPending();
		this.setValue( valueToSet );
	} );
};

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.getChecklistOptions = function ( value ) {
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

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.setValue = function ( value ) {
	if ( this.isLoading() ) {
		return;
	}
	bs.chklst.ui.ChecklistOptionsInputWidget.super.prototype.setValue.call( this, value );
};

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.setLoading = function ( value ) {
	this.loading = value;
};

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.isLoading = function () {
	return this.loading;
};
