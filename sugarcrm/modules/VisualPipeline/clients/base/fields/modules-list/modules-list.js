// FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.VisualPipeline.ModulesListField
 * @alias SUGAR.App.view.fields.BaseVisualPipelineModulesListField
 * @extends View.Fields.Base.EnumField
 */
({
    extendsFrom: 'EnumField',

    plugins: ['EllipsisInline'],

    /**
     * HTML tag of the append tag checkbox.
     *
     * @property {string}
     */
    appendTagInput: 'input[name=append_tag]',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        var items = {};
        if (options.def.name === 'enabled_modules') {
            items = this.context.get('allowedModules');
        }
        if (options.def.name === 'tile_body_fields') {
            var tabContent = this.model.get('tabContent');
            items = tabContent.fields;
        }
        this.items = items;
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');
        if (this.name === 'enabled_modules') {
            this.attachEvents();
        }
    },

    /**
     * Set up events for the field to add and remove items from the collection.
     */
    attachEvents: function() {
        this.handleRemoveItemHandler = _.bind(this._handleRemoveItemFromCollection, this);
        this.handleAddItemHandler = _.bind(this._handleAddItemToCollection, this);

        this.$el.on('select2-removed', this.handleRemoveItemHandler);
        this.$el.on('select2-selecting', this.handleAddItemHandler);
    },

    /**
     * Handles triggering the removal of the model from the collection
     */
    _handleRemoveItemFromCollection: function(e) {
        if (!_.isEmpty(e.val)) {
            this.context.trigger('pipeline:config:model:remove', e.val);
        }
    },

    /**
     * Handles triggering for adding a model from the collection
     */
    _handleAddItemToCollection: function(e) {
        if (!_.isEmpty(e.val)) {
            this.context.trigger('pipeline:config:model:add', e.val);
        }
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.$el.off('select2-removed', this.handleRemoveItemHandler);
        this.$el.off('select2-selecting', this.handleAddItemHandler);
        this._super('_dispose');
    }
});
