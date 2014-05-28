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
 * @class View.Fields.Base.SelectionField
 * @alias SUGAR.App.view.fields.BaseSelectionField
 * @extends View.Field
 */
({
    events: {
        'click input.selection': 'toggleSelect'
    },
    toggleSelect: function(evt) {
        var $el = $(evt.currentTarget).is(":checked");
        if($el) {
            this.check();
        } else {
            this.uncheck();
        }
    },
    check: function() {
        if(this.model) {
            this.context.set('selection_model', this.model);
        }
    },
    uncheck: function() {
        if(this.model) {
            this.context.unset('selection_model');
        }
    },
    bindDomChange: function() {
        //don't update the row's model & re-render, this is just a mechanism for selecting a row
    }
})
