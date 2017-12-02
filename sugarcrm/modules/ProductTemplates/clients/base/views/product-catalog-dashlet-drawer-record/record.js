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
({
    extendsFrom: 'BaseRecordView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        var i;
        var j;
        var panel;
        var field;

        options.name = 'record';

        for (i = 0; i < options.meta.panels.length; i++) {
            panel = options.meta.panels[i];
            for (j = 0; j < panel.fields.length; j++) {
                field = panel.fields[j];
                field.readonly = true;
            }
        }

        this._super('initialize', [options]);
    },

    /**
     * Overriding this function to just listen to the buttons on the record
     *
     * @inheritdoc
     */
    delegateButtonEvents: function() {
        this.context.on('button:cancel_button:click', this._drawerCancelClicked, this);
        this.context.on('button:add_to_quote_button:click', this._drawerAddToQuoteClicked, this);
    },

    /**
     * Handles when the Cancel button is clicked in the ProductCatalogDashlet drawer.
     * It just triggers the event that the tree should re-enable, and closes the drawer.
     *
     * @private
     */
    _drawerCancelClicked: function() {
        this.context.parent.trigger('productCatalogDashlet:add:complete');
        app.drawer.close();
    },

    /**
     * Handles when the Add To Quote button is clicked in the ProductCatalogDashlet drawer.
     * It strips out unnecessary ProductTemplate fields and sends the data to the context.
     *
     * @private
     */
    _drawerAddToQuoteClicked: function() {
        var data = this.model.toJSON();

        data.position = 0;
        data._forcePosition = true;

        // copy Template's id and name to where the QLI expects them
        data.product_template_id = data.id;
        data.product_template_name = data.name;

        // remove ID/etc since we dont want Template ID to be the record id
        delete data.id;
        delete data.date_entered;
        delete data.date_modified;

        this.context.parent.trigger('productCatalogDashlet:add', data);
        app.drawer.close();
    }
})
