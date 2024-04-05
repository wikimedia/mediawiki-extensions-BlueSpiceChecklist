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

function getId( target ) {
	var id = $( target ).attr( 'id' );
	id = id.split( "-" );
	id = id.pop();
	return id;
}

$( document ).on( 'click', '.bs-checklist-item', function ( e ) {
	var target = e.target;
	var id = getId( target );
	var isChecked = $( target ).attr( 'checked' );
	var toCheck = false;
	if ( isChecked !== 'checked' ) {
		toCheck = true;
	}

	bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
		pos: id,
		value: toCheck,
		type: 'check'
	});
} );

$( document ).on( 'change', '.bs-checklist-list', function ( e ) {
	var target = e.target;
	var id = getId( target );
	var index = $(target)[0].selectedIndex;
	target.style.color = target.options[ index ].style.color;
	bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
		pos: id,
		value: target.options[ index ].text,
		type: 'list'
	});
} );
