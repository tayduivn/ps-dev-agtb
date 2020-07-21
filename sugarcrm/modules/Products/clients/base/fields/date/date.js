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
 * @class View.Fields.Base.Quotes.DateField
 * @alias SUGAR.App.view.fields.BaseQuotesDateField
 * @extends View.Fields.Base.DateField
 */
({
    extendsFrom: 'DateField',

    // BEGIN SUGARCRM flav=ent ONLY

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        if (this.model && this.name && this.name === 'service_start_date') {
            this.model.on('change:' + this.name, this.handleRecalculateServiceDuration, this);
        }
    },

    /**
     * If this is a coterm QLI, recalculate the service duration when the start date
     * changes so that the end date remains constant.
     */
    handleRecalculateServiceDuration: function() {
        if (!_.isEmpty(this.model.get('add_on_to_id'))) {
            var startDate = app.date(this.model.get('service_start_date'));
            var endDate = app.date(this.model.get('service_end_date'));

            var diffDays = endDate.diff(startDate, 'days');
            if (startDate.isSameOrBefore(endDate)) {
                diffDays += 1; // we want to be inclusive of the end date
            }

            // For now, we always use days as our unit
            this.model.set('service_duration_unit', 'day');
            this.model.set('service_duration_value', diffDays);
        }
    },

    // END SUGARCRM flav=ent ONLY

    /**
     * @inheritdoc
     */
    _dispose: function() {
        // FIXME: this is a bad "fix" added -- when SC-2395 gets done to upgrade bootstrap we need to remove this
        if (this._hasDatePicker && this.$(this.fieldTag).data('datepicker')) {
            $(window).off('resize', this.$(this.fieldTag).data('datepicker').place);
        }
        this._hasDatePicker = false;

        this._super('_dispose');
    }
})
