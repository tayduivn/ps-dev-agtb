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
 * Headerpane view for the {@link View.Layouts.Base.GlobalSearchResultsLayout}
 * GlobalSearch Results layout.
 *
 * @class View.Views.Base.GsrHeaderpaneView
 * @alias SUGAR.App.view.views.BaseGsrHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.searchTerm = this.context.get('searchTerm');
    }
})
