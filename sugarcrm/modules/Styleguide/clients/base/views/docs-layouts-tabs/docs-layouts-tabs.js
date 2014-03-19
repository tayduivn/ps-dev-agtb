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
({
    className: 'container-fluid',

    // layouts tabs
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('#nav-tabs-pills')
            .find('ul.nav-tabs > li > a, ul.nav-list > li > a, ul.nav-pills > li > a')
            .on('click.styleguide', function(e){
                e.preventDefault();
                e.stopPropagation();
                $(this).tab('show');
            });
    },

    _dispose: function() {
        this.$('#nav-tabs-pills')
            .find('ul.nav-tabs > li > a, ul.nav-list > li > a, ul.nav-pills > li > a')
            .off('click.styleguide');

        this._super('_dispose');
    }
})
