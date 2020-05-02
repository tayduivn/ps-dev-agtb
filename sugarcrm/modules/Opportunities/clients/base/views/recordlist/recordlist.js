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
 * @class View.Views.Base.OpportunitiesRecordlistView
 * @alias SUGAR.App.view.views.BaseOpportunitiesRecordlistView
 * @extends View.Views.Base.RecordlistView
 */
({
    extendsFrom: 'RecordlistView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['CommittedDeleteWarning']);
        this._super("initialize", [options]);
    },

    /**
     * @inheritdoc
     */
    parseFieldMetadata: function(options) {
        options = this._super('parseFieldMetadata', [options]);

        app.utils.hideForecastCommitStageField(options.meta.panels);

        return options;
    },

    /**
     * Set min-width on cascade fields on entering edit mode
     *
     * @param model
     * @param field
     */
    editClicked: function(model, field) {
        this._super('editClicked', [model,field]);
        $('td[data-type="date-cascade"]').css('min-width', '210px');
        $('td[data-type="enum-cascade"]').css('min-width', '210px');
    },

    /**
     * Remove the min-width on leaving the edit mode
     *
     * @param modelId
     * @param isEdit
     */
    toggleRow: function(modelId, isEdit) {
        if (!isEdit) {
            $('td[data-type="date-cascade"]').css('min-width', '');
            $('td[data-type="enum-cascade"]').css('min-width', '');
            this.resize();
        }
        this._super('toggleRow', [modelId,isEdit]);
    },
})
