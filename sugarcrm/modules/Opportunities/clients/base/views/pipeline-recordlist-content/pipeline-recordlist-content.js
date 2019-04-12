// FILE SUGARCRM flav=ent ONLY
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
 * @class View.Views.Base.Opportunities.PipelineRecordlistContentView
 * @alias App.view.views.BaseOpportunitiesPipelineRecordlistContentView
 * @extends View.Views.Base.PipelineRecordlistContentView
 */
({
    extendsFrom: 'PipelineRecordlistContentView',

    /**
     * @inheritdoc
     */
    saveModel: function(model, ui) {
        var self = this;
        var ctxModel = this.context.get('model');

        if (ctxModel && ctxModel.get('pipeline_type') === 'date_closed') {
            var $ulEl = this.$(ui.item).parent('ul');
            var dateData = $ulEl.data('column-name');
            var dateClosed = app.date(dateData, 'MMMM YYYY')
                .endOf('month')
                .formatUser(true);

            model.set('date_closed', dateClosed);
        } else {
            model.set(this.headerField, this.$(ui.item).parent('ul').data('column-name'));
        }

        model.save({}, {
            success: function(model) {
                self._super('render');
                self.postRender();
            },
            error: function(data) {
                self._super('render');
                self.postRender();
            }
        });
    }
});
