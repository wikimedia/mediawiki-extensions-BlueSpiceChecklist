bs.util.registerNamespace( 'bs.chklst.ui' );

bs.chklst.ui.ChecklistOptionsInputWidget = function BsVecUiChecklistOptionsInputWidget ( config ) {
	bs.chklst.ui.ChecklistOptionsInputWidget.super.call( this, config );
	this.inspector = config.inspector;
	this.attribute = config.attribute;
	this.loading = false;
	this.setDisabled( true );
	var me = this;
	me.inspector['listInput'].on( 'change', this.updateChecklistOptions, [], me );
};

OO.inheritClass( bs.chklst.ui.ChecklistOptionsInputWidget, OO.ui.DropdownInputWidget );

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.updateChecklistOptions = function( list ) {
	var me = this;
	this.setDisabled( true );
	this.setLoading( true );
	this.inspector.pushPending();
	this.getChecklistOptions( list ).done( function( options ) {
		me.setOptions( options );
		me.setDisabled( false );
		var valueToSet = '';
		var attrValue = me.inspector.selectedNode.getAttribute( 'mw' ).attrs[me.attribute.name];
		if ( typeof me.attribute.default !== 'undefined' ) {
			valueToSet = me.attribute.default.split( "|" ).shift();
		};
		if ( typeof attrValue !== 'undefined' ) {
			valueToSet = attrValue.split( "|" ).shift();
		};
		me.setLoading( false );
		me.inspector.popPending();
		me.setValue( valueToSet );
	});
}

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.getChecklistOptions = function( value ) {
	var dfd = $.Deferred();
	bs.api.store.getData( 'checklist-template' ).done( function( response ) {
		var results = response.results;
		var options = [];
		for ( var i = 0; i < results.length; i++ ) {
			var result = results[i];
			if ( result.id === value ) {
				for (var j = 0; j < result.listOptions.length; j++ ) {
					var labelNoColor = result.listOptions[j].split( "|" ).shift();
					options.push({
						data: labelNoColor,
						label: labelNoColor
					});
				}
			}
		};
		dfd.resolve( options );
	});
	return dfd.promise();
}

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.setValue = function( value ) {
	if ( this.isLoading() ) {
		return;
	};
	bs.chklst.ui.ChecklistOptionsInputWidget.super.prototype.setValue.call( this, value );
}

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.setLoading = function( value ) {
	this.loading = value;
}

bs.chklst.ui.ChecklistOptionsInputWidget.prototype.isLoading = function() {
	return this.loading;
}