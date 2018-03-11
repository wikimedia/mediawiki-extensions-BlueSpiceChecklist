/*!
 * VisualEditor UserInterface BSChecklistInspectorTool class.
 *
 * @copyright 2018 Hallo Welt! GmbH
 * @license GPL-3.0-only
 */

/**
 * MediaWiki UserInterface checklist tool.
 *
 * @class
 * @extends ve.ui.FragmentInspectorTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */

ve.ui.BSChecklistInspectorTool = function VeUiBSChecklistInspectorTool( toolGroup, config ) {
	ve.ui.BSChecklistInspectorTool.super.call( this, toolGroup, config );
};
OO.inheritClass( ve.ui.BSChecklistInspectorTool, ve.ui.FragmentInspectorTool );
ve.ui.BSChecklistInspectorTool.static.name = 'bsChecklistTool';
ve.ui.BSChecklistInspectorTool.static.group = 'object';
ve.ui.BSChecklistInspectorTool.static.icon = 'checklist';
ve.ui.BSChecklistInspectorTool.static.title = OO.ui.deferMsg(
	'bs-checklist-ve-checklistinspector-title'
);
ve.ui.BSChecklistInspectorTool.static.modelClasses = [ ve.dm.BSChecklistNode ];
ve.ui.BSChecklistInspectorTool.static.commandName = 'bsChecklistCommand';
ve.ui.toolFactory.register( ve.ui.BSChecklistInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'bsChecklistCommand', 'window', 'open',
		{ args: [ 'bsChecklistInspector' ], supportedSelections: [ 'linear' ] }
	)
);