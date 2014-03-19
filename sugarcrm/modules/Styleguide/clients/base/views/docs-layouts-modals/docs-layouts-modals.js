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

    // layouts modals
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('[rel=popover]').popover();

        this.$('.modal').tooltip({
          selector: '[rel=tooltip]'
        });
        this.$('#dp1').datepicker({
          format: 'mm-dd-yyyy'
        });
        this.$('#dp3').datepicker();
        this.$('#tp1').timepicker();
    }
})
