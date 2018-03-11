/*!
 * VisualEditor ContentEditable BSChecklistNode class.
 *
 * @copyright 2018 Hallo Welt! GmbH
 * @license GPL-3.0-only
 */

/**
 * ContentEditable MediaWiki checklist node.
 *
 * @class
 * @extends ve.ce.MWInlineExtensionNode
 *
 * @constructor
 * @param {ve.dm.BSChecklistNode} model Model to observe
 * @param {Object} [config] Configuration options
 */
ve.ce.BSChecklistNode = function VeCeBSChecklistNode() {
	// Parent constructor
	ve.ce.BSChecklistNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.BSChecklistNode, ve.ce.MWInlineExtensionNode );

/* Static properties */

ve.ce.BSChecklistNode.static.name = 'checklist';

ve.ce.BSChecklistNode.static.primaryCommandName = 'checklist';

// This tag does not have any content
ve.ce.BSChecklistNode.static.rendersEmpty = true;

/* Methods */

/**
 * @inheritdoc
 */
ve.ce.BSChecklistNode.prototype.onSetup = function () {
	// Parent method
	ve.ce.BSChecklistNode.super.prototype.onSetup.call( this );

	// DOM changes
	this.$element.addClass( 've-ce-bsChecklistNode' );
};

/**
 * @inheritdoc ve.ce.GeneratedContentNode
 */
ve.ce.BSChecklistNode.prototype.validateGeneratedContents = function ( $element ) {
	if ( $element.is( 'div' ) && $element.hasClass( 'errorbox' ) ) {
		return false;
	}
	return true;
};

/* Registration */

ve.ce.nodeFactory.register( ve.ce.BSChecklistNode );