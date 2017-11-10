Ext.define( 'BS.Checklist.Window', {
	extend: 'BS.Window',
	id: 'bs-insertchecklist',
	singleton: true,
	layout: 'border',
	height: 400,
	width:500,
	modal: true,
	title: mw.message('bs-checklist-dlg-insert-list-title').plain(),
	listSelected: false,
	isDirty: false,
	preventDeselect: false,
	afterInitComponent: function() {
		this.btnNew = Ext.create( 'Ext.Button', {
			text: '+', //mw.message('bs-checklist-dlg-new-list').plain(),
			id: this.getId()+'-btn-new'
		});
		this.btnNew.on( 'click', this.onBtnNewClick, this );

		//this.buttons.unshift( this.btnNew );

		this.btnSave = Ext.create( 'Ext.Button', {
			text: mw.message( 'bs-checklist-dlg-save-list' ).plain(),
			id: this.getId()+'-btn-save'
		});
		this.btnSave.on( 'click', this.onBtnSaveClick, this );

		this.strBoxSelect = Ext.create( 'Ext.data.JsonStore', {
			fields: ['text']
		});

		this.bsListItems = Ext.create( 'BS.Checklist.ChecklistBoxSelect', {
			disabled: true,
			//TODO: i18n
			emptyText: mw.message( 'bs-checklist-dlg-items-emptytext' ).plain(),
			id: 'bs-insertchecklist-itembox'
		});
		this.bsListItems.on( 'change', this.onItemChange, this );

		this.bsListItemsLabel = Ext.create( 'Ext.form.Label', {
			html: '<div class="bs-insertcheckboxitem-hint">'+mw.message('bs-checklist-dlg-items-hint').plain()+'</div>'
		});

		this.pnlMain = Ext.create( 'Ext.form.FormPanel', {
			header: true,
			title: mw.message( 'bs-checklist-dlg-items-label' ).plain(),
			region: 'center',
			bodyPadding: 5,
			items: [
				this.bsListItems,
				this.bsListItemsLabel,
				this.btnSave
			]
		});

		this.templateStore = Ext.create( 'Ext.data.TreeStore', {
			proxy: {
				type: 'ajax',
				url: bs.api.makeUrl( 'bs-checklist-template-store' )
			},
			root: {
				text: 'Lists',
				id: 'src',
				expanded: true
			},
			defaultRootProperty: 'results',
			fields: [ 'id', 'listOptions', 'text' ]
		});

		this.templateTree = Ext.create( 'Ext.tree.Panel', {
			width: 250,
			title: mw.message( 'bs-checklist-dlg-panel-title' ).plain(),
			useArrows: true,
			rootVisible: false,
			displayField: 'text',
			store: this.templateStore,
			collapsible: false,
			collapsed: false,
			singleExpand: true,
			region: 'west',
			id: 'bs-checklist-template-tree',
			tools: [
				this.btnNew
			]
		});

		this.templateTree.on( 'select', this.onSelect, this );
		this.templateTree.on( 'beforedeselect', this.onBeforeDeselect, this );
		this.templateTree.on( 'beforeselect', this.onBeforeSelect, this );
		this.templateTree.on( 'beforecellclick', this.onBeforeItemclick, this );
		this.templateTree.on( 'beforeitemexpand', this.onBeforeItemExpand, this);

		this.items = [
			this.templateTree,
			this.pnlMain
		];

		this.callParent();

	},
	onItemChange: function() {
		BS.Checklist.Window.setDirty( true );
	},
	onBtnNewClick: function() {
		bs.util.prompt(
			"bs.checklist-dlg-new",
			{
				title: mw.message( 'bs-checklist-dlg-new-title' ).plain(),
				text: mw.message( 'bs-checklist-dlg-new-prompt' ).plain()
			},
			{
				ok: function( input ) {
					this.templateStore.tree.root.appendChild({
						id: input.value,
						text: input.value,
						leaf: true
					});
					var record = this.templateStore.getNodeById( input.value );
					this.templateTree.getSelectionModel().select( record )
				},
				scope: this
			}
		)
	},
	onBtnSaveClick: function() {
		var title = this.templateTree.getSelectionModel().getLastSelected().get( 'id' );
		var valueRecords = this.bsListItems.getValueRecords();
		var records = new Array();
		for ( var record in valueRecords ) {
			records.push( valueRecords[record].data.text );
		}

		this.setDirty( false );

		bs.api.tasks.exec( 'checklist', 'saveOptionsList', {
			title: title,
			records: records
		});
	},
	onBeforeItemExpand: function( p, animate, eOpts ) {return false;},
	onBeforeItemclick: function( sender, td, cellIndex, record, tr, rowIndex, e, eOpts  ) {
		//nothing
	},
	onBeforeSelect: function( sender, record, index, eOpts ) {
		if ( BS.Checklist.Window.isDirty == true ) {
			var dialog = Ext.create( 'BS.ConfirmDialog', {
				title: mw.message( 'bs-checklist-confirm-dirty-title' ).plain(),
				text: mw.message( 'bs-checklist-confirm-dirty-text' ).plain(),
			});
			dialog.on( 'ok', function( input ) {
				BS.Checklist.Window.setDirty( false );
				BS.Checklist.Window.templateTree.fireEvent( 'select', sender, record, index, eOpts );
			});
			BS.Checklist.Window.add( dialog );
			dialog.show();
			return false;
		}
	},
	onBeforeDeselect: function( sender, record, index, eOpts ) {
		//this code is not functional
		/*
		if ( BS.Checklist.Window.preventDeselect == true ) {
			BS.Checklist.Window.preventDeselect = false;
			return false;
		}
		*/
	},
	onSelect: function( sender, records, index, eOpts) {

		this.bsListItems.setValue( records.get( 'listOptions' ) );
		this.listSelected = records;
		this.setDirty( false );
		this.bsListItems.enable();
	},
	setData: function( data ) {
		this.bsListItems.setValue( data );
	},
	getData: function() {
		return this.listSelected;
	},
	setDirty: function( dirty ) {
		BS.Checklist.Window.isDirty = dirty;
		if ( dirty ) {
			this.btnSave.setText( "* " + mw.message( 'bs-checklist-dlg-save-list' ).plain() );
		} else {
			this.btnSave.setText( mw.message( 'bs-checklist-dlg-save-list' ).plain() );
		}
	}
});
