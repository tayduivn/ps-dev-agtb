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
            var dateClosed = app.date(dateData)
                .endOf('month')
                .format(app.user.getPreference('date_pref'));

            if (dateClosed.indexOf('T') !== -1) {
                dateClosed = dateClosed.split('T')[0];
            }

            model.set('date_closed', dateClosed);
        }

        var config = app.metadata.getModule(this.module, 'config');
        if (config.opps_view_by === 'Opportunities') {
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
        } else {
            var parameters = {
                id: model.get('id'),
                date_closed: model.get('date_closed')
            };

            app.api.call('update', app.api.buildURL('Opportunities', 'updateOpportunityCloseDate'), parameters, {
                success: function(data) {
                    var literal = model.toJSON();
                    literal = self.addTileVisualIndicator([literal]);
                    model.set('tileVisualIndicator', literal[0].tileVisualIndicator);
                    self._super('render');
                    self.postRender();
                }
            });
        }
    }
});
