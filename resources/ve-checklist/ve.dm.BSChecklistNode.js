/*!
 * VisualEditor DataModel BSChecklistNode class.
 *
 * @copyright 2018 Hallo Welt! GmbH
 * @license GPL-3.0-only
 */

/**
 * DataModel MediaWiki checklist node.
 *
 * @class
 * @extends ve.dm.MWInlineExtensionNode
 *
 * @constructor
 * @param {Object} [element]
 */
ve.dm.BSChecklistNode = function VeDmBSChecklistNode() {
	// Parent constructor
	ve.dm.BSChecklistNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.BSChecklistNode, ve.dm.MWInlineExtensionNode );

/* Static members */

ve.dm.BSChecklistNode.static.name = 'checklist';

ve.dm.BSChecklistNode.static.tagName = 'bs:checklist';

// Name of the parser tag
ve.dm.BSChecklistNode.static.extensionName = 'bs:checklist';

// This tag renders without content
ve.dm.BSChecklistNode.static.childNodeTypes = [];
ve.dm.BSChecklistNode.static.isContent = true;

ve.dm.BSChecklistNode.static.defaultAttributes = {
	value : "checked",
	type: "check"
}

/* Registration */

ve.dm.modelRegistry.register( ve.dm.BSChecklistNode );