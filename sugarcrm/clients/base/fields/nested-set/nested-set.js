/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * @class View.Fields.Base.NestedSetField
 * @alias SUGAR.App.view.fields.BaseNestedSetField
 * @extends View.Field
 */
({
    /**
     * Root ID of a shown NestedSet.
     * @property {String}
     */
    categoryRoot: null,

    /**
     * Module which implements NestedSet.
     */
    moduleRoot: null,

    /**
     * {@inheritDoc}
     */
    extendsFrom: 'BaseField',

    /**
     * {@inheritDoc}
     */
    plugins: ['JSTree', 'NestedSetCollection'],

    /**
     * Selector for tree's placeholder.
     * @property {String}
     */
    ddEl: '[data-menu=dropdown]',

    /**
     * Flag indicates if input for new node shown.
     * @property {Bool}
     */
    inCreation: false,

    /**
     * Callback to handle global dropdown click event.
     * @property {Callback}
     */
    dropdownCallback: null,

    /**
     * {@inheritDoc}
     */
    events: {
        'click [data-role=treeinput]': 'openDropDown',
        'click': 'handleClick',
        'keydown [data-role=secondinput]': 'handleKeyDown',
        'click [data-action=full-screen]': 'fullScreen',
        'click [data-action=create-new]': 'switchCreate',
        'keydown [data-role=add-item]': 'handleKeyDown'

    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        var treeOptions = {};
        this._super('_render', []);
        if (this.$(this.ddEl).length !== 0 && this.action === 'edit') {
            this.$(this.ddEl).dropdown();
            this.$(this.ddEl).data('dropdown').opened = false;
            this.$(this.ddEl).off('click.bs.dropdown');
            $('body').off('click.bs.dropdown.data-api', this.dropdownCallback);
            $('body').on('click.bs.dropdown.data-api', this.dropdownCallback);
            treeOptions = {
                category_root: this.categoryRoot,
                module_root: this.moduleRoot
            };
            this._renderTree($('[data-place=tree]'), treeOptions, {'onSelect': _.bind(this.selectedNode, this)});
        }
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(opts) {
        this._super('initialize', [opts]);
        var module = this.def.config_provider || this.context.get('module'),
            config = app.metadata.getModule(module, 'config');
        this.categoryRoot = this.def.category_root || config.category_root || '';
        this.moduleRoot = this.def.data_provider || module;
        this.dropdownCallback = _.bind(this.handleGlobalClick, this);
    },

    /**
     * Show dropdown.
     * @param {Event} evt Triggered mouse event.
     */
    openDropDown: function(evt) {
        if (this.action === 'edit') {
            var dropdown = this.$(this.ddEl).data('dropdown');
            if (dropdown.opened === false) {
                evt.stopPropagation();
                evt.preventDefault();
                _.defer(function (dropdown, self) {
                    self.$(self.ddEl).dropdown('toggle');
                    self.$('[data-role=secondinput]').val('');
                    dropdown.opened = true;
                    self.$('[data-role=secondinput]').focus();
                }, dropdown, this);
            }
        }
    },

    /**
     * Handle global dropdown clicks.
     * @param evt Triggered mouse event.
     */
    handleGlobalClick: function(evt) {
        if (this.action === 'edit') {
            this.closeDD();
            evt.preventDefault();
            evt.stopPropagation();
        }
    },

    /**
     * Handle all clicks for the field.
     * Need to catch for preventing external events.
     * @param evt
     */
    handleClick: function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
    },

    /**
     *  Search in the tree.
     */
    confirmInput: function() {
        var val = this.$('[data-role=secondinput]').val();
        this.SearchNode(val);
    },

    /**
     * Override `Editable` plugin event to prevent default behavior.
     */
    bindKeyDown: function() {},

    /**
     * Override `Editable` plugin event to prevent default behavior.
     */
    bindDocumentMouseDown: function() {},

    /**
     * Override `Editable` plugin event to prevent default behavior.
     */
    focus: function() {
        if (this.action === 'edit') {
            this.$('[data-role=treeinput]').click();
        }
    },

    /**
     * Handle key events in input fields.
     * @param evt Triggered key event.
     */
    handleKeyDown: function(evt) {
        var role = $(evt.currentTarget).data('role');
        if (evt.keyCode !== 13 && evt.keyCode !== 27) {
            return;
        }
        evt.preventDefault();
        evt.stopPropagation();
        switch (evt.keyCode) {
            case 13:
                switch (role) {
                    case 'secondinput':
                        this.confirmInput(evt);
                        break;
                    case 'add-item':
                        this.addNew(evt);
                        break;
                }
                break;
            case 27:
                switch (role) {
                    case 'secondinput':
                        this.closeDD();
                        break;
                    case 'add-item':
                        this.switchCreate();
                        break;
                }
                break;
        }
    },

    /**
     * Set value of a model.
     * @param {String} id
     * @param {String} val
     */
    setValue: function(id, val) {
        this.model.set(this.def.id_name, id);
        this.model.set(this.def.name, val);
    },

    /**
     * {@inheritDoc}
     */
    bindDataChange: function() {
        this._super('bindDataChange', []);
        if (this.action === 'edit') {
            this.$('[name=' + this.def.name + ']').html(this.model.get(this.def.name));
            this.$('[name=' + this.def.id_name + ']').val(this.model.get(this.def.id_name));
        }
    },

    /**
     * {@inheritDoc}
     */
    _dispose: function() {
        if (this.action === 'edit') {
            $('body').off('click.bs.dropdown.data-api', this.dropdownCallback);
        }
        this._super('_dispose');
    },

    /**
     * Close dropdown.
     * @return {Boolean}
     */
    closeDD: function() {
        var dropdown = this.$(this.ddEl).data('dropdown');
        if (!dropdown) {
            return false;
        }
        if (dropdown.opened === true) {
            this.$(this.ddEl).dropdown('toggle');
            if (this.inCreation) {
                this.switchCreate();
            }
            dropdown.opened = false;
            return true;
        }
        return false;
    },

    /**
     * Open drawer with tree list.
     */
    fullScreen: function() {
        var treeOptions = {
            category_root: this.categoryRoot,
            module_root: this.moduleRoot,
            isDrawer: true
        };
        app.drawer.open({
            layout: 'nested-set-list',
            context: {
                module: 'Categories',
                parent: this.context,
                treeoptions: treeOptions
            }
        }, _.bind(this.selectedNode, this));
    },

    /**
     * Add new element to the tree.
     * @param {Event} evt Triggered key event.
     */
    addNew: function(evt) {
        var name = $(evt.target).val().trim();
        this.addNode(name);
        this.switchCreate();
    },

    /**
     * Create and hide input for new element.
     */
    switchCreate: function() {
        var $a = $('[data-action=create-new]'),
            $el = $('[data-role=add-item]');
        if (this.inCreation === false) {
            $el = $('<input />', {'data-role': 'add-item', 'style': 'width: 80%;', 'type': 'text'});
            $a.hide();
            $el.insertAfter($a);
            $el.focus();
        } else {
            $el.remove();
            $a.show();
        }
        this.inCreation = !this.inCreation;
    },

    /**
     * Callback to handle selection of the tree.
     * @param data
     */
    selectedNode: function(data) {
        if (_.isEmpty(data)) {
            return;
        }
        var id = data.id,
            val = data.name;
        this.setValue(id, val);
        this.bindDataChange();
        this.closeDD();
    }
})
