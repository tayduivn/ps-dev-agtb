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
    extendsFrom: 'RecordlistView',


    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args: [options]});
        this.collection.on('data:sync:complete', function () {
            this.init_();
        }, this);
    },

    init_: function () {
        _.each(this.fields, function (field) {
            if ( (field.name == 'cas_delegate_date') || (field.name == 'cas_due_date') )
                field.$el[0].textContent = app.date( field.$el[0].textContent + " UTC" ).formatUser();
        });
    }
})
