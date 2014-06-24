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
 * @class View.Views.Base.HistorySummaryPreviewView
 * @alias SUGAR.App.view.views.BaseHistorySummaryPreviewView
 * @extends View.Views.Base.PreviewView
 */
({
    extendsFrom: 'PreviewView',

    /**
     * @inheritdoc
     * @override
     *
     * Overridden to make custom calls by module to get activities
     */
    _renderPreview: function(model, collection, fetch, previewId) {
        // If there are drawers there could be multiple previews,
        // make sure we are only rendering preview for active drawer
        if (app.drawer && !app.drawer.isActive(this.$el)) {
            return;  //This preview isn't on the active layout
        }

        // Close preview if we are already displaying this model
        if (this.model && model &&
            (this.model.get("id") === model.get("id") && previewId === this.previewId)) {
            // Remove the decoration of the highlighted row
            app.events.trigger("list:preview:decorate", false);
            // Close the preview panel
            app.events.trigger('preview:close');
            return;
        }

        if (app.metadata.getModule(model.module).isBwcEnabled) {
            app.events.trigger('preview:close');
            app.alert.show('preview_bwc_error', {
                level: 'error',
                messages: app.lang.getAppString('LBL_PREVIEW_BWC_ERROR')
            });
            return;
        }

        if (model) {
            // Get the corresponding detail view meta for said module.
            // this.meta needs to be set before this.getFieldNames is executed.
            this.meta = _.extend({},
                app.metadata.getView(model.module, 'record'),
                app.metadata.getView(model.module, 'preview')
            );
            this.meta = this._previewifyMetadata(this.meta);
        }

        if (fetch) {
            var recordUrl = app.api.serverUrl + '/' + model.module + '/' + model.get('id'),
                callbacks = {
                    success: _.bind(function(newModel) {
                        newModel = app.data.createBean(model.module, newModel);
                        newModel.module = model.module;

                        this.renderPreview(newModel);
                    }, this)
                }

            app.api.call('read', recordUrl, null, callbacks);
        } else {
            this.renderPreview(model, collection);
        }

        this.previewId = previewId;
    }
})
