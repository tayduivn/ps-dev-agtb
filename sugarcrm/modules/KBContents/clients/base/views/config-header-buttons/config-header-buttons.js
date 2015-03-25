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
 * @class View.Views.Base.SchedulersJobsConfigHeaderButtonsView
 * @alias SUGAR.App.view.layouts.BaseSchedulersJobsConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: 'ConfigHeaderButtonsView',

    initialize: function(options) {
        this._super('initialize', [options]);

        // Standard ConfigHeaderButtonsView doesn't use doValidate
        var model = this.context.get('model');
        model._save = model.save;
        model.save = function(key, val, options) {
            this.doValidate(null, function(isValid){
                if (isValid) {
                    model._save(key, val, options)
                } else {
                    val.error();
                }
            });
        }
    }
})
