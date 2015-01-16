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

({
    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.moduleList = this.context.get('convertModuleList');
        this.requiredModules = _.where(this.moduleList, {required: true});
        this.removedModules = [];
        this.context.on('lead:convert-panel:complete', this.handleAutoAddRequest, this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');

        this.initializeSelect2();
        this.$select2.on('change', _.bind(this.handleChange, this));
        this.$select2.on('select2-selecting', this.handleSelection);
    },

    /**
     * Initialize select2 widget
     */
    initializeSelect2: function() {
        this.$select2 = this.$('.select2').select2({
            data: this.moduleList,
            placeholder: '',
            multiple: true,
            closeOnSelect: true,
            containerCssClass: 'select2-choices-pills-close'
        });

        this.$select2.select2('data', this.requiredModules);
        this.updateModelFromSelect2();
    },

    /**
     * Sync the model to the currently selected values in the select2 widget
     */
    updateModelFromSelect2: function() {
        this.model.set(this.name, this.$select2.select2('val'));
    },

    /**
     * Handle changes to the select2 widget
     *
     * When any change occurs, update the model. When removing a module, add it
     * to a list of modules that should now no longer be auto-added.
     *
     * @param {Event} event
     */
    handleChange: function(event) {
        this.updateModelFromSelect2();
        if (event.removed && event.removed.id) {
            this.removedModules.push(event.removed.id);
        }
    },

    /**
     * Handle request to add a module to the list - ignore any requests where
     * the module was previously removed from the list.
     *
     * @param {string} moduleToAdd
     */
    handleAutoAddRequest: function(moduleToAdd) {
        var module, selectedModules;

        //don't add if previously removed
        if (_.contains(this.removedModules, moduleToAdd)) {
            return;
        }

        module = _.findWhere(this.moduleList, {id: moduleToAdd});
        selectedModules = this.$select2.select2('data');
        if (!_.contains(selectedModules, module)) {
            selectedModules.push(module);
            this.$select2.select2('data', selectedModules);
        }
    },

    /**
     * Avoid rendering process on Select2 change in order to keep focus
     * @override
     */
    bindDataChange: function() {
    },

    /**
     * Override to remove default DOM change listener, we use Select2 events instead
     * @override
     */
    bindDomChange: function() {
    },

    /**
     * @inheritdoc
     */
    unbindDom: function() {
        this.$('.select2field').select2('destroy');
        this._super('unbindDom');
    }
})
