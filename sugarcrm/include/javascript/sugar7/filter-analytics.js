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
(function(app) {

    /**
     * Track filter dropdown selection.
     */
    var trackFilterDropdown = function() {
        if (!app.view.views.BaseFilterFilterDropdownView.prototype) {
            return;
        }
        var _filterDropdownProto = _.clone(app.view.views.BaseFilterFilterDropdownView.prototype);
        _.extend(app.view.views.BaseFilterFilterDropdownView.prototype, {
            handleChange: function(id) {
                _filterDropdownProto.handleChange.apply(this,[id]);
                this.trackGA(id);
            },

            trackGA: function(id) {
                app.analytics.trackEvent('click', id + 'Filter-selected', id); //...more params
            }
        });
    };

    /**
     * Track filter field and operator selection.
     */
    var trackFilterFieldAndOperator = function() {
        var currentFieldName;

        if (!app.view.views.BaseFilterRowsView.prototype) {
            return;
        }
        var _filterFieldOperatorProto = _.clone(app.view.views.BaseFilterRowsView.prototype);
        _.extend(app.view.views.BaseFilterRowsView.prototype, {

            handleOperatorSelected: function(e) {

                _filterFieldOperatorProto.handleOperatorSelected.apply(this,[e]);
                var $el = this.$(e.currentTarget),
                operator = $el.val();
                app.analytics.trackEvent(e.type, currentFieldName + "With"+ operator, e);
            },
            handleFieldSelected: function(e) {
                _filterFieldOperatorProto.handleFieldSelected.apply(this,[e]);
                var $el = this.$(e.currentTarget),
                fieldName = $el.val();
                currentFieldName = fieldName;
            }


        });
    };


    app.events.on('app:sync:complete', function() {

        trackFilterDropdown();
        trackFilterFieldAndOperator();

    });
})(SUGAR.App);
