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
 * See similar {@link View.Views.Base.Meetings.SubpanelListView}
 *
 * FIXME: This will be removed once SC-447 gets in.
 *
 * @class View.Views.Base.Calls.SubpanelListView
 * @alias SUGAR.App.view.views.BaseCallsSubpanelListView
 * @extends View.Views.Base.SubpanelListView
 */
({
    /**
     * @inheritDoc
     */
    extendsFrom: 'SubpanelListView',

    /**
     * {@inheritDoc}
     *
     * Listen to changes on `date_start` and `date_end` fields in order to
     * update `duration_hours` and `duration_minutes` automatically.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.collection.on(
            'change:date_start change:date_end',
            this.updateDuration,
            this
        );
    },

    /**
     * Handler for when `date_start` and `date_end` change their values on the
     * model.
     *
     * @param {Data.Bean} model Model.
     */
    updateDuration: function(model) {
        var minutes = 0,
            hours = 0,
            start = app.date(model.get('date_start')),
            end = app.date(model.get('date_end'));

        if (start.isValid() && end.isValid() && start.isBefore(end)) {
            var duration = app.date.duration(end.diff(start));

            minutes = Math.floor(duration.minutes());
            hours = Math.floor(duration.asHours());
        }

        model.set('duration_minutes', minutes);
        model.set('duration_hours', hours);
    }
})
