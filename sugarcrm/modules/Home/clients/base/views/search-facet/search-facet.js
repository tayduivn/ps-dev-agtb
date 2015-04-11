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
 * @class View.Views.Base.Home.SearchFacetView
 * @alias SUGAR.App.view.views.BaseSearchFacetView
 * @extends View.View
 */
({

    initialize: function(options) {
        this._super('initialize', [options]);

        //Here we'll listen at `change:facets` in the context to rerender.
//        this.context.on('change:facets', function(model, value) {

        //Put stuff in the view to be displayed like :
//        this.modules = model.get('facets').modules;
//            this.render();
//        }, this);
    }
})
