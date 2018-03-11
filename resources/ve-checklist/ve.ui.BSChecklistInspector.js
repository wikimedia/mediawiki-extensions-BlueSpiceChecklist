/*!
 * VisualEditor UserInterface MWChecklistInspector class.
 *
 * @copyright 2018 Hallo Welt! GmbH
 * @license GPL-3.0-only
 */

/**
 * MediaWiki checklist inspector.
 *
 * @class
 * @extends ve.ui.MWLiveExtensionInspector
 *
 * @constructor
 * @param {Object} [config] Configuration options
 */
ve.ui.BSChecklistInspector = function VeUiBSChecklistInspector( config ) {
	// Parent constructor
	ve.ui.BSChecklistInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
};

/* Inheritance */

OO.inheritClass( ve.ui.BSChecklistInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ve.ui.BSChecklistInspector.static.name = 'bsChecklistInspector';

ve.ui.BSChecklistInspector.static.title = OO.ui.deferMsg( 'bs-checklist-ve-checklistinspector-title' );

ve.ui.BSChecklistInspector.static.modelClasses = [ ve.dm.BSChecklistNode ];

ve.ui.BSChecklistInspector.static.dir = 'ltr';

//This tag does not have any content
ve.ui.BSChecklistInspector.static.allowedEmpty = true;
ve.ui.BSChecklistInspector.static.selfCloseEmptyBody = true;

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.BSChecklistInspector.prototype.initialize = function () {
	var checkedField;

	// Parent method
	ve.ui.BSChecklistInspector.super.prototype.initialize.call( this );

	// Index layout
	this.indexLayout = new OO.ui.PanelLayout( {
		scrollable: false,
		expanded: false
	} );


	this.checkedCheckbox = new OO.ui.CheckboxInputWidget();

	checkedField = new OO.ui.FieldLayout( this.checkedCheckbox, {
		align: 'inline',
		label: ve.msg( 'bs-checklist-ve-checklistinspector-cb-checked' )
	} );

	// Initialization
	this.$content.addClass( 've-ui-bsChecklistInspector-content' );

	// Tag has no content, so no input needed
	this.input.toggle();

	this.indexLayout.$element.append(
		checkedField.$element,
		this.generatedContentsError.$element
	);
	this.form.$element.append(
		this.indexLayout.$element
	);
};

/**
 * @inheritdoc
 */
ve.ui.BSChecklistInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.BSChecklistInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			var attributes = this.selectedNode.getAttribute( 'mw' ).attrs,
				checked = attributes.value == "checked" ? "checked" : "false";

			// Populate form
			this.checkedCheckbox.setSelected( attributes.value == "checked" );

			// Add event handlers
			this.checkedCheckbox.on( 'change', this.onChangeHandler );

			// Always allow to "save". This is needed when inserting a tag
			// because due to the tag not being modified, the "done" button
			// is disabled then. But note, this is a hack, can probably be
			// done better.
			this.actions.setAbilities( { done: true } );

		}, this );
};

/**
 * @inheritdoc
 */
ve.ui.BSChecklistInspector.prototype.getTeardownProcess = function ( data ) {
	return ve.ui.BSChecklistInspector.super.prototype.getTeardownProcess.call( this, data )
		.first( function () {
			this.checkedCheckbox.off( 'change', this.onChangeHandler );
		}, this );
};

ve.ui.BSChecklistInspector.prototype.updateMwData = function ( mwData ) {
	var checked;

	// Parent method
	ve.ui.BSChecklistInspector.super.prototype.updateMwData.call( this, mwData );

	// Get data from inspector
	checked = this.checkedCheckbox.isSelected();

	// Update attributes
	mwData.attrs.value = checked ? "checked" : "false";

};

/**
 * @inheritdoc
 */
ve.ui.BSChecklistInspector.prototype.formatGeneratedContentsError = function ( $element ) {
	return $element.text().trim();
};

/**
 * Append the error to the current tab panel.
 */
ve.ui.BSChecklistInspector.prototype.onTabPanelSet = function () {
	this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
};

/* Registration */

ve.ui.windowFactory.register( ve.ui.BSChecklistInspector );