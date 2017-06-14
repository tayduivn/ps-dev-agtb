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
 * @class View.Layouts.Base.Reports.DrillthroughListLayout
 * @alias SUGAR.App.view.layouts.BaseReportsDrillthroughListLayout
 * @extends View.Views.Base.ListLayout
 */
({
    extendsFrom: 'ListLayout',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        options.module = options.context.get('chartModule');
        this._super('initialize', [options]);
    }
})