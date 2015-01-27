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
 * This view displays the selected records at the top of a selection list. It
 * also allows to unselect them.
 *
 * @class View.Views.Base.SelectionListContextView
 * @alias SUGAR.App.view.views.BaseSelectionListContextView
 * @extends View.View
 */

({
    className: 'selection-context',

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        console.log('selection-context-initialized');
     }

})
