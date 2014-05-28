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
 * @class View.Fields.Base.RelatedContactField
 * @alias SUGAR.App.view.fields.BaseRelatedContactField
 * @extends View.Field
 */
/**
 * RelatedContactField is needed because BaseField specifically gets values from specific keys
 * on the model, and for the HistorySummaryView list, we need to set values on the model
 * that are different from what BaseField uses so it doesn't conflict with the other values
 * on BaseField's model
 */
({
    events: {
        'click a': 'onLinkClicked'
    },

    /**
     * Holds the href for the field link
     */
    linkRoute: '',

    /**
     * @inheritdoc
     * @override
     *
     * Overriding since the parent buildHref would use 'id' not 'contact_id'
     * to create the href link
     */
    buildHref: function() {
        var defRoute = this.def.route ? this.def.route : {},
            module = this.model.module || this.context.get('module'),
            id = this.model.get('contact_id');
        this.linkRoute = '#' + app.router.buildRoute(module, id, defRoute.action);
        return this.linkRoute;
    },

    /**
     * Intercepts the clicked link, if the user clicked on the Contact
     * that the user was already viewing, refresh the page, because otherwise
     * the url will not change
     *
     * @param {jQuery.Event} evt The click event from the link
     */
    onLinkClicked: function(evt) {
        var currentRoute = '#' + Backbone.history.getFragment();
        if (currentRoute === this.linkRoute) {
            app.router.refresh();
        }
    }
})
