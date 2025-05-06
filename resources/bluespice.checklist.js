function getId( target ) {
	let id = $( target ).attr( 'id' );
	id = id.split( '-' );
	id = id.pop();
	return id;
}

$( document ).on( 'click', '.bs-checklist-item', ( e ) => {
	const target = e.target;
	const id = getId( target );
	const isChecked = $( target ).attr( 'checked' );
	let toCheck = false;
	if ( isChecked !== 'checked' ) {
		toCheck = true;
	}

	bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
		pos: id,
		value: toCheck,
		type: 'check'
	} );
} );

$( document ).on( 'change', '.bs-checklist-list', ( e ) => {
	const target = e.target;
	const id = getId( target );
	const index = $( target )[ 0 ].selectedIndex;
	target.style.color = target.options[ index ].style.color;
	bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
		pos: id,
		value: target.options[ index ].text,
		type: 'list'
	} );
} );
