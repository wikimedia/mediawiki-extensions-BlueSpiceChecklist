bs.util.registerNamespace( 'bs.chklst.ui' );

bs.chklst.ui.ChecklistInputWidget = function BsChklstUiChecklistInputWidget ( config ) {
	bs.chklst.ui.ChecklistInputWidget.super.call( this, config );
	this.inspector = config.inspector;
	this.attribute = config.attribute;
	this.loading = false;
	this.setDisabled( true );
	var me = this;
	this.getChecklists().done(
		function( options ) {
			me.loadChecklistsDone( options );
		}
	);
};

OO.inheritClass( bs.chklst.ui.ChecklistInputWidget, OO.ui.DropdownInputWidget );

bs.chklst.ui.ChecklistInputWidget.prototype.getChecklists = function() {
	var dfd = $.Deferred();
	this.setLoading( true );
	this.inspector.pushPending();
	bs.api.store.getData( 'checklist-template' ).done( function( response ) {
		var results = response.results;
		var options = [];
		for ( var i = 0; i < results.length; i++ ) {
			options.push({
				data: results[i].id,
				label: results[i].text
			});
		};
		dfd.resolve( options );
	});
	return dfd.promise();
}

bs.chklst.ui.ChecklistInputWidget.prototype.loadChecklistsDone = function( options ) {
	this.setOptions( options );
	this.setDisabled( false );
	this.setLoading( false );
	this.inspector.popPending();
	var attrValue = this.inspector.selectedNode.getAttribute( 'mw' ).attrs[this.attribute.name];
	this.setValue( attrValue || this.attribute.default );
}

bs.chklst.ui.ChecklistInputWidget.prototype.setValue = function( value ) {
	if ( this.isLoading() ) {
		return;
	};
	bs.chklst.ui.ChecklistInputWidget.super.prototype.setValue.call( this, value );
}

bs.chklst.ui.ChecklistInputWidget.prototype.setLoading = function( value ) {
	this.loading = value;
}

bs.chklst.ui.ChecklistInputWidget.prototype.isLoading = function() {
	return this.loading;
}