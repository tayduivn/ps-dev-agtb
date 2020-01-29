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
 * @class View.Fields.Base.ConsoleConfiguration.SortOrderSelectorField
 * @alias SUGAR.App.view.fields.BaseConsoleConfigurationSortOrderSelectorField
 * @extends View.Fields.Base.BaseField
 */
({
    events: {
        'click .sort-order-selector': 'setNewValue'
    },

    /**
     * Stores the name of the field that this field is conditionally dependent on
     */
    dependencyField: null,

    /**
     * @inheritdoc
     *
     * Grabs the name of the dependency field from the field options
     *
     * @param options
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        if (options.def && options.def.dependencyField) {
            this.dependencyField = options.def.dependencyField;
        }
    },

    /**
     * @inheritdoc
     *
     * Extends the parent bindDataChange to include a check of the value of
     * the dependency field
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        if (this.dependencyField) {
            this.model.on('change:' + this.dependencyField, function() {
                this._handleDependencyChange();
            }, this);
        }
    },

    /**
     * When this field first renders, check the dependency field to see if we
     * need to hide this
     *
     * @private
     */
    _render: function() {
        this._super('_render');
        this._handleDependencyChange();
    },

    /**
     * Checks the value of the dependency field. If it is empty, this field will
     * be set to 'descending' and hidden.
     *
     * @private
     */
    _handleDependencyChange: function() {
        if (this.model && this.$el) {
            if (_.isEmpty(this.model.get(this.dependencyField))) {
                this.$el.find('[name="desc"]').click();
                this.$el.hide();
            } else {
                this.$el.show();
            }
        }
    },

    /**
     * Sets the value of the selected sort order on the model
     *
     * @param event the button click event
     */
    setNewValue: function(event) {
        this.model.set(this.name, event.currentTarget.name);
    }
})
