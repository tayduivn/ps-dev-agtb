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
 *
 * @class View.Views.Base.SelectionListLinkView
 * @alias SUGAR.App.view.views.BaseSelectionListLinkView
 * @extends View.Views.Base.SelectionListView
 */
({
    extendsFrom: 'SelectionListView',

    /**
     * @inheritDoc
     */
    initializeEvents: function() {
        this._super('initializeEvents');
        this.context.on('selection-list:select', this._selectAndCloseImmediately, this);
    },

    /**
     * Selects the given model and closes the drawer immediately.
     *
     * @param {Data.Bean} model
     * @private
     */
    _selectAndCloseImmediately: function(model) {
        if (model) {
            app.drawer.closeImmediately(this._getModelAttributes(model));
        }
    }
})
