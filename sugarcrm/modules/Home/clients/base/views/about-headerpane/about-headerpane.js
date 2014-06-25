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
 * @class View.Views.Base.Home.AboutHeaderpaneView
 * @alias SUGAR.App.view.views.BaseHomeAboutHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',
    /**
     * {@inheritDoc}
     *
     * Override the title to pass the context with the server info.
     */
    _renderHtml: function() {
        var title = this.title || this.module;
        this.title = app.lang.get(title, this.module, app.metadata.getServerInfo());

        app.view.View.prototype._renderHtml.call(this);
    }
})
