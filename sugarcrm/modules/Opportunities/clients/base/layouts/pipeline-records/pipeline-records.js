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
 * @class View.Layouts.Base.OpportunitiesPipelineRecordsLayout
 * @alias SUGAR.App.view.layouts.BaseOpportunitiesPipelineRecordsLayout
 */
({
    className: 'pipeline-records',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.alerts = {
            showNoAccessError: function() {
                if (!this instanceof app.view.View) {
                    app.logger.error('This method should be invoked by Function.prototype.call(), passing in as' +
                        'argument an instance of this view.');
                    return;
                }
                // dismiss the default error
                app.alert.dismiss('data:sync:error');
                // display no access error
                app.alert.show('server-error', {
                    level: 'warning',
                    title: 'ERR_NO_VIEW_ACCESS_TITLE',
                    messages: app.utils.formatString(app.lang.get('ERR_NO_VIEW_ACCESS_MSG'), [this.module]),
                    autoclose: true
                });
            }
        };
    }
})
