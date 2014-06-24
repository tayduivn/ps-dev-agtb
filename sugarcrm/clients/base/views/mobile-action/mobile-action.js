/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.MobileActionView
 * @alias SUGAR.App.view.views.BaseMobileActionView
 * @extends View.View
 */
({
    tagName: 'span',
    events: {
        'click [data-action=mobile]': 'navigateToMobile'
    },
    navigateToMobile: function () {
        if (document.cookie.indexOf('sugar_mobile=') !== -1) {
            // kill sugar_mobile=0 cookie
            document.cookie = 'sugar_mobile=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
        // navigate to the same route of mobile site
        window.location = app.utils.buildUrl('mobile/') + window.location.hash;
    }
})
