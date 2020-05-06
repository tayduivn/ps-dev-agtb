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
 * @class View.Fields.Base.Opportunities.DateCascadeField
 * @alias SUGAR.App.view.fields.BaseOpportunitiesDateCascadeField
 * @extends View.Fields.Base.DateField
 */
({
    extendsFrom: 'DateField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['Cascade']);
        this._super('initialize', [options]);
        this.def.lblString = app.lang.get('LBL_UPDATE_OPPORTUNITIES_RLIS', 'Opportunities') +
            ' ' +
            app.lang.getModuleName('RevenueLineItems', {plural: true});
    },

    /**
     * @inheritdoc
     */
    _getAppendToTarget: function() {
        // Overriding this method to append the datepicker on the side-drawer for Renewals console
        // while parent method appends the datepicker on the main-pane || drawer || preview-pane only

        // Similar fix was used for datetimecombo.js for CS-153
        var $currentComponent = this.$el;

        // this algorithm does not work on list view
        if (this.view && (this.view.type === 'recordlist' || this.view.type === 'subpanel-list')) {
            return this._super('_getAppendToTarget');
        }

        // First, attempt to attach to a parent element with an appropriate data-type attribute.
        // bootstrap-datepicker requires that the append-to target be relatively positioned:
        // https://stackoverflow.com/questions/27966645/bootstrap-datepicker-appearing-at-incorrect-location-in-a-modal
        while ($currentComponent.length > 0) {
            var dataType = $currentComponent && $currentComponent.attr('data-type');
            if (dataType === this.type) {
                $currentComponent.css('position', 'relative');
                return $currentComponent;
            } else {
                $currentComponent = $currentComponent ? $currentComponent.parent() : {};
            }
        }

        // fall back to parent implementation if necessary
        return this._super('_getAppendToTarget');
    },
})
