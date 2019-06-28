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

// FILE SUGARCRM flav=ent ONLY

/**
 * @class View.Layouts.Portal.Cases.CreateLayout
 * @alias SUGAR.App.view.layouts.PortalCasesCreateLayout
 * @extends View.Layout
 */
({
    /**
     * @inheritdoc
     */
    initComponents: function(components, context, module) {
        var deflect = _.isUndefined(app.config.caseDeflection) || app.config.caseDeflection === 'enabled';
        var layout = deflect ? 'deflect' : 'create-case';
        var def = {
            layout: layout
        };
        this._super('initComponents', [[def], context, module]);
    }
})
