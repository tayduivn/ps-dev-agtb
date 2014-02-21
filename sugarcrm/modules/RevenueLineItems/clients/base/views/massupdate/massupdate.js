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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'MassupdateView',
    
    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        this.plugins = _.clone(this.plugins) || [];
        this.plugins.push('DisableMassDelete', 'MassQuote');
        this._super("initialize", [options]);
    },

    /**
     * {@inheritdoc}
     */
    delegateListFireEvents: function() {
        this.layout.on("list:records:deleted", this.deleteCommitWarning, this);
        this._super("delegateListFireEvents");
    },
    
    /**
     * Shows a warning message if a RLI that is included in a forecast is deleted.
     * @return string message
     */
    deleteCommitWarning: function(lastSelectedModels) {
        var message = null;
        
        if (!_.isUndefined(_.find(lastSelectedModels, function(model) {
            if (model.get("commit_stage") == "include") {
                return true;
            }
            return false;
        }))) {
            var forecastModuleSingular = app.lang.get('LBL_MODULE_NAME_SINGULAR', 'Forecasts');
            message = app.lang.get("WARNING_DELETED_RECORD_LIST_RECOMMIT_1", "RevenueLineItems")
                + '<a href="#Forecasts">' + forecastModuleSingular + '</a>.  '
                + app.lang.get("WARNING_DELETED_RECORD_LIST_RECOMMIT_2", "RevenueLineItems")
                + '<a href="#Forecasts">' + forecastModuleSingular + '</a>.';
            app.alert.show("included_list_delete_warning", {
                level: "warning",
                messages: message,
                onLinkClick: function() {
                    app.alert.dismissAll();
                }
            });
        }
        
        return message;
    }
})
