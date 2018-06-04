/**
 * Js for Checklist extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Checklist
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for all Checklist related methods and properties
 */
BsChecklist = {

	checkboxImage: 'extensions/BlueSpiceChecklist/resources/images/checkbox.png',
	checkboxImageChecked: 'extensions/BlueSpiceChecklist/resources/images/checked.png',
	checkboxStyle: 'color:transparent;width:12px;height:12px;border:0px;background-color:transparent;background-repeat:no-repeat;',
	optionsLists: [],
	menuButton: false,
	lastCommand: "mceBsCheckbox",
	lastCommandKey: false,

	init: function () {
		/*alert('check');*/
	},

	click: function(elem) {
		var id = elem.id;
		id = id.split( "-" );
		id = id.pop();

		bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
			pos: id,
			value: elem.checked
		});
	},

	change: function(elem) {
		var id = elem.id;
		id = id.split( "-" );
		id = id.pop();
		elem.style.color = elem.options[elem.selectedIndex].style.color;

		bs.api.tasks.exec( 'checklist', 'doChangeCheckItem', {
			pos: id,
			value: $( '#'+elem.id ).find( ":selected" ).text()
		});
	},

	getOptionsList: function(listId, forceReload) {
		if ( !forceReload && listId in BsChecklist.optionsLists ) {
			return BsChecklist.optionsLists[listId];
		}

		$.ajax({
			type: "GET",
			url: bs.api.makeUrl( 'bs-checklist-template-store' )
				+ "&filter=" + JSON.stringify( [{
					type: "templatetitle",
					comparison: "eq",
					value: listId,
					field: "text"
			}] ),
			async: false, //TODO: Reimplement with aysnc call
			success: function(response){
				if (response.results.length > 0 ) {
					BsChecklist.optionsLists[listId] = response.results[0].listOptions;
				} else {
					BsChecklist.optionsLists[listId] = ["-"];
				}
			}
		});

		return BsChecklist.optionsLists[listId];
	 },

	changeSelect: function(elem) {
		var value = $(elem).find( ":selected" ).text();
		tinymce.activeEditor.dom.setAttrib( elem.parentNode, 'data-bs-value', value );
	},

	makeCheckbox: function( checked ) {
		var innerText;
		innerText = '<button contentEditable="false" class="bsClickableElement" ';
		innerText += 'style="'+BsChecklist.checkboxStyle+'background-image:url(\'';
		if ( checked ) {
			innerText += BsChecklist.checkboxImageChecked;
		} else {
			innerText += BsChecklist.checkboxImage;
		}
		// Do not use short notation for closing tag (/>), as it breaks IE
		innerText += '\');" ></button>';
		return innerText;
	},

	makeSelectbox: function( options, valueText ) {
		var innerText = '<select contenteditable="false" class="bsClickableElement" onchange="this.style.color=this.options[this.selectedIndex].style.color;parent.BsChecklist.changeSelect(this);" {color}>';
		var selectedColor = '';
		for ( var i = 0; i < options.length; i++ ) {
			var optionSet = options[i].split( "|" );
			var optionValue = optionSet[0];
			var optionColor = optionSet[1];

			if ( !selectedColor && optionColor ) selectedColor = 'style="color:'+optionColor+';" ';

			innerText += '<option ';
			if ( optionColor ) innerText += 'style="color:'+optionColor+';" ';
			if ( optionValue == valueText ) {
				if ( optionColor ) selectedColor = 'style="color:'+optionColor+';" ';
				innerText += 'selected="selected"';
			}
			innerText += '>'+optionValue+'</option>';
		}
		innerText += '</select>';
		innerText = innerText.replace( "{color}", selectedColor );
		return innerText;
	},

	makeAndRegisterCheckboxSpecialTag: function( ed, checked ) {
		var id = ed.plugins.bswikicode.getSpecialTagList().length;
		ed.plugins.bswikicode.pushSpecialTagList( '<bs:checklist value="" />' );
		var node = ed.dom.create(
				'span',
				{
					'id'              : "bs_specialtag:@@@ST"+id+"@@@",
					'class'           : "mceNonEditable tag",
					'data-bs-name'    : "bs:checklist",
					'data-bs-type'    : "tag",
					'data-bs-id'      : id,
					'data-bs-value'   : "false",
					'data-bs-cbtype'  : "checkbox"
				},
				BsChecklist.makeCheckbox( checked ) );
		return node;
	},

	makeAndRegisterSelectboxSpecialTag: function( ed, record, value ) {
		// record can be an object ( if it's called from dialog ) or string ( it's called from menue item )
		var listname = record.get ? record.get('text') : record;
		var options =  record.get ? record.get( 'listOptions' ) : BsChecklist.getOptionsList( listname );
		var id = ed.plugins.bswikicode.getSpecialTagList().length;
		ed.plugins.bswikicode.pushSpecialTagList( '<bs:checklist type="list" value="" list="'+listname+'"/>' );
		var node = ed.dom.create(
				'span',
				{
					'id'              : "bs_specialtag:@@@ST"+id+"@@@",
					'class'           : "mceNonEditable tag",
					'data-bs-name'    : "bs:checklist",
					'data-bs-type'    : "tag",
					'data-bs-id'      : id,
					'data-bs-value'   : "false",
					'data-bs-cbtype'  : "list"
				},
				BsChecklist.makeSelectbox( options, value ) );
		return node;
	}
};

mw.loader.using( 'ext.bluespice', function() {
	BsChecklist.init();
});

$(document).on( "BSVisualEditorRenderSpecialTag", function( event, sender, type, st ){
	if ( type != 'bs:checklist' ) return false;
	var ed = tinymce.activeEditor;
	var specialtag = ed.dom.createFragment( st[0] ).childNodes[0];

	var cbt = ed.dom.getAttrib( specialtag, 'type', 'checkbox' );
	var valueText = ed.dom.getAttrib( specialtag, 'value', 'false' );
	var listText = ed.dom.getAttrib( specialtag, 'list', '' );

	var innerText;
	if ( cbt == 'checkbox' ) {
		if ( valueText == 'checked' ) {
			innerText = BsChecklist.makeCheckbox( true );
		} else {
			innerText = BsChecklist.makeCheckbox( false );
		}
	} else if ( cbt == 'list' ) {
		innerText = BsChecklist.makeSelectbox(
			BsChecklist.getOptionsList( listText ) , valueText
		);
	}

	var moreAttribs = 'data-bs-value="'+valueText+'"';
	moreAttribs += ' data-bs-cbtype="'+cbt+'"';

	return {
		innerText: innerText,
		moreAttribs: moreAttribs
	};
});

$(document).on( "BSVisualEditorRecoverSpecialTag", function( event, sender, specialTagMatch, innerText ){
	if( specialTagMatch == null ) return false;
	var valueregex = '<.*?data-bs-value="(.*?)"[^>]*?>';
	var valueMatcher = new RegExp( valueregex, '' );
	var value = valueMatcher.exec( specialTagMatch[1] );
	var valueText;
	if ( value ) {
		valueText = value[1];
	} else {
		valueText = '';
	}
	var newInnerText = innerText.replace( /value="(.*?)"/, 'value="'+valueText+'"' );
	return {
		innerText: newInnerText
	}
});

$(document).on( "BSVisualEditorClickSpecialTag", function( event, sender, ed, e, dataname ){
	if ( dataname == 'bs:checklist' ) {
		var cbtype = ed.dom.getAttrib( e.target.parentNode, 'data-bs-cbtype' );

		if ( !cbtype ) {
			cbtype = 'checkbox';
		}

		var value = ed.dom.getAttrib( e.target.parentNode, 'data-bs-value' );

		if ( cbtype == 'checkbox' ) {
			if ( value == 'checked' ) {
				value = 'false';
				ed.dom.setStyle(e.target, 'background-image', "url('"+BsChecklist.checkboxImage+"')" );
			} else {
				value = 'checked';
				ed.dom.setStyle( e.target, 'background-image', "url('"+BsChecklist.checkboxImageChecked+"')" );
			}
		}

		ed.dom.setAttrib( e.target.parentNode, 'data-bs-value', value );
	}
});

$(document).on( 'BsVisualEditorActionsInit', function( event, plugin, buttons, commands, menus ) {
	var t = plugin;
	var ed = t.getEditor();

	menus.push({
		menuId: 'bsChecklist',
		menuConfig: {
			text: mw.message( 'bs-checklist-menu-insert-checkbox' ).plain(),
			cmd : 'mceBsChecklistLastCommand'
		}
	});

	var menuItems = [];

	menuItems.push({
		text: mw.message( 'bs-checklist-menu-insert-no-list-loaded' ).plain(),
		disabled: true,
		onPostRender: function( e ) {
			if ( Object.keys( BsChecklist.optionsLists ).length > 0 ) {
				this.hide( true );
			}
		}
	});

	menuItems.push( {text: '-'} );

	menuItems.push({
		text: mw.message( 'bs-checklist-button-checkbox-title' ).plain(),
		value: 'Checkbox',
		onclick:function(){
			BsChecklist.lastCommand = 'mceBsCheckbox';
			BsChecklist.lastCommandKey = false;
			ed.execCommand( 'mceBsCheckbox', false );
		}
	});

	menuItems.push({
		text: mw.message( 'bs-checklist-menu-insert-list-title' ).plain(),
		onclick: function() {
			// Open window
			var me = this;
			mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
				Ext.require( 'BS.Checklist.Window', function(){
					BS.Checklist.Window.clearListeners();
					BS.Checklist.Window.on( 'ok', function( sender, data ){
						BsChecklist.lastCommand = 'mceBsSelectbox';
						BsChecklist.lastCommandKey = data;
						ed.execCommand( 'mceBsSelectbox', false, data );

					});
					BS.Checklist.Window.show( me );
				});
			});
		}
	});

	ed.addButton( 'bscheckbox', {
		title: mw.message( 'bs-checklist-button-checkbox-title' ).plain(),
		cmd: 'mceBsChecklistLastCommand',
		type: 'splitbutton',
		//icon: 'image',
		menu: menuItems,
		onPostRender: function() {
			var self = this;
			BsChecklist.menuButton = this;
			ed.on( 'NodeChange', function( evt ) {
				self.disabled( false );
				if ( !evt.target.selection.isCollapsed() ) {
					self.disabled( true );
				}
				$(evt.parents).each( function(){
					if ( this.tagName.toLowerCase() == 'pre' ) {
						self.disabled( true );
					}
				});
			});
		},

		onShow: function( e ) {
			var listKeys = [];
			e.control.items().each( function( index,value ) {
				listKeys.push( index.text() );
			});
			for ( var thekey in BsChecklist.optionsLists ) {
				if ( typeof BsChecklist.optionsLists[thekey] === 'function' ) continue;
				if ( $.inArray( thekey, listKeys ) == -1 ) {
					var menuItem = new tinymce.ui.MenuItem({
							text: thekey,
							value : thekey,
							onclick:function( e ){
								BsChecklist.lastCommand = 'mceBsSelectbox';
								BsChecklist.lastCommandKey = this.value();
								ed.execCommand( 'mceBsSelectbox', false, this.value() );
							}
						});
					e.control.prepend( menuItem );
				}
			}
		}

	});

	ed.addCommand( 'mceBsChecklistLastCommand', function( ui, value ) {
		ed.execCommand( BsChecklist.lastCommand, false, BsChecklist.lastCommandKey );
	});

	ed.addCommand( 'mceBsCheckbox', function( ui, value ) {
		//needed in FF, apparently to init selection
		ed.selection.getBookmark();
		//only insert if selection is collapsed
		if ( ed.selection.isCollapsed() ) {
			var node = BsChecklist.makeAndRegisterCheckboxSpecialTag( ed, false );
			ed.dom.insertAfter( node, ed.selection.getNode() );
			//Place cursor to end
			ed.selection.select( node, false );
			ed.selection.collapse( false );
		}
		return;
	});

	commands.push({
		commandId: 'checkbox',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceBsCheckbox', ui, v );
		}
	});

	ed.addCommand( 'mceBsSelectbox', function( ui, value ) {
		//needed in FF, apparently to init selection
		ed.selection.getBookmark();
		//only insert if selection is collapsed
		if ( ed.selection.isCollapsed() ) {
			var node = BsChecklist.makeAndRegisterSelectboxSpecialTag( ed, value, '' );
			ed.dom.insertAfter( node, ed.selection.getNode() );
			//Place cursor to end
			ed.selection.select( node, false );
			ed.selection.collapse( false );
		}
		return;
	});

	commands.push({
		commandId: 'selectbox',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceBsSelectbox', ui, v );
		}
	});
});
