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
 * List view for the {@link View.Layouts.Base.GlobalSearchResultsLayout}
 * GlobalSearch Results layout.
 *
 * @class View.Views.Base.GsrListView
 * @alias SUGAR.App.view.views.BaseGsrListView
 * @extends View.View
 */
({

    /**
     * @InheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.collection.on('reset', function() {
            _.each(this.collection.models, function(model) {
                model.module = model.get('module');
            });
            this.render();
        }, this);
    }
})
