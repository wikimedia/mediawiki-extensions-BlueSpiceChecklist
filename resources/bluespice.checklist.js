/**
 * Js for Checklist extension
 *
 * @author     Patric Wirth
 * @package    Bluespice_Extensions
 * @subpackage Checklist
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * Base class for all Checklist related methods and properties
 */
BsChecklist = {

	click: function(elem) {
		var id = elem.id;
		id = id.split( "-" );
		id = id.pop();

		bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
			pos: id,
			value: elem.checked,
			type: 'check'
		});
	},

	change: function(elem) {
		var id = elem.id;
		id = id.split( "-" );
		id = id.pop();
		elem.style.color = elem.options[elem.selectedIndex].style.color;

		bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
			pos: id,
			value: $( '#'+elem.id ).find( ":selected" ).text(),
			type: 'list'
		});
	}
};

