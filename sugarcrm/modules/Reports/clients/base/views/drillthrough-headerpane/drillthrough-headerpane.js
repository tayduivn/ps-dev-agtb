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
 * @class View.Views.Base.Reports.DrillthroughHeaderpaneView
 * @alias SUGAR.App.view.views.BaseReportsDrillthroughHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',

    /**
     * @inheritdoc
     */
    _renderHtml: function() {
        this._super('_renderHtml');

        this.layout.on('drillthrough:closedrawer:fire', _.once(_.bind(function() {
            this.$el.off();
            app.drawer.close();
        }, this)));
    }
})
