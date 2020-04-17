/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.ShiftExceptions.CreateView
 * @alias SUGAR.App.view.views.ShiftExceptionsCreateView
 * @extends View.Views.Base.CreateView
 */
({
    extendsFrom: 'CreateView',

    timeFields: '.record-cell[data-name="start_time"], .record-cell[data-name="end_time"]',

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        this.model.on('change:all_day', function() {
            const isAllDay = this.model.get('all_day');
            $(this.timeFields).toggle(!isAllDay);
        }, this);
    },

    /**
     * @inheritdoc
     */
    render: function() {
        this._super('render');
        $(this.timeFields).hide();
    }
});
