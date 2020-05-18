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
 * @class View.Views.Base.OpportunitiesSubpanelListView
 * @alias SUGAR.App.view.views.BaseOpportunitiesSubpanelListView
 * @extends View.Views.Base.SubpanelListView
 */
({
    extendsFrom: 'SubpanelListView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['CommittedDeleteWarning']);
        this._super('initialize', [options]);
    },

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Set min-width on cascade fields on entering edit mode
     *
     * @param model
     * @param field
     */
    editClicked: function(model, field) {
        this._super('editClicked', [model,field]);
        $('td[data-type="date-cascade"]').addClass('cascade-width');
        $('td[data-type="enum-cascade"]').addClass('cascade-width');
    },

    /**
     * Remove the min-width on leaving the edit mode
     *
     * @param modelId
     * @param isEdit
     */
    toggleRow: function(modelId, isEdit) {
        if (!isEdit) {
            $('td[data-type="date-cascade"]').removeClass('cascade-width');
            $('td[data-type="enum-cascade"]').removeClass('cascade-width');
            this.resize();
        }
        this._super('toggleRow', [modelId,isEdit]);
    },

    /**
     * Override the way recordlist.js combines metadata from base record views
     * with subviews to ensure Opps Subpanels don't lose metadata needed by
     * cascade fields when they are removed from Studio and added back.
     *
     * @param recordListMeta Base List view metadata
     * @param subViewMeta Subpanel Metadata
     * @return {Object} Combined metadata from recordlist.js
     * @override
     */
    combineMeta: function(recordListMeta, subViewMeta) {
        var viewBy = app.metadata.getModule('Opportunities', 'config').opps_view_by;
        if (viewBy !== 'RevenueLineItems') {
            return this._super('combineMeta', [recordListMeta, subViewMeta]);
        }
        var cascadeFieldsMeta = this._getCascadeMeta(recordListMeta);

        // Go through our subpanel view metadata, swapping field defs as needed
        // to maintain cascade fields in subpanels
        _.first(subViewMeta.panels).fields = _.map(_.first(subViewMeta.panels).fields, function(field) {
            // if our field is a cascade field whose type doesn't contain
            // 'cascade', it's been added to a custom subpanel via studio
            if (_.has(cascadeFieldsMeta, field.name) && field.type.indexOf('cascade') === -1) {
                return cascadeFieldsMeta[field.name];
            }
            return field;
        }, this);
        return this._super('combineMeta', [recordListMeta, subViewMeta]);
    },

    /**
     * Convert the metadata from record list view into an object containing
     * field defs for our cascade fields.
     *
     * @param recordListMeta List view metadata
     * @return {Object} key/value pairs mapping field names to their defs e.g.
     *      {
     *          'sales_stage': {def for sales stage...},
     *          ...
     *      }
     * @private
     */
    _getCascadeMeta: function(recordListMeta) {
        var cascadeFieldsMeta = {};
        if (_.isEmpty(recordListMeta)) {
            return cascadeFieldsMeta;
        }
        _.each(_.first(recordListMeta.panels).fields, function(field) {
            if (field.type.indexOf('cascade') !== -1 && _.has(field, 'disable_field')) {
                cascadeFieldsMeta[field.name] = field;
            }
        }, this);
        return cascadeFieldsMeta;
    },
    //END SUGARCRM flav=ent ONLY
})
