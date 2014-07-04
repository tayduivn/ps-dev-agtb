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
(function(app) {

    /**
     * Overrides View::_renderHtml() to enable bootstrap widgets after the element has been added to the DOM
     */
    var __superViewRender__ = app.view.View.prototype._renderHtml;
    app.view.View.prototype._renderHtml = function() {
        __superViewRender__.call(this);
        if ($.fn.timeago) {
            $("span.relativetime").timeago({
                logger: SUGAR.App.logger,
                date: SUGAR.App.date,
                lang: SUGAR.App.lang,
                template: SUGAR.App.template
            });
        }
    };

})(SUGAR.App);
