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
 * @class View.Views.Base.PipelineHeaderpaneView
 * @alias SUGAR.App.view.views.BasePipelineHeaderpaneView
 * @extends View.Views.Base.PipelineHeaderpaneView
 */
({
    events: {
        'click button[name=pipelineBtn]': 'changePipeline'
    },

    /**
     * Initializes various pipelineType fields and the table_header
     * @param options
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.context.on('button:pipeline_create_button:click', this.createNewRecord, this);
        this.pipelineTypes = [];
        this.table_header = app.metadata.getModule('VisualPipeline','config').table_header[this.module];
        _.each(this.meta.fields, function(field) {
            this.pipelineTypes.push(field);
        }, this);

        if (this.module === 'Opportunities') {
            this.context.get('model').set('pipeline_type', 'date_closed');
        } else {
            this.context.get('model').set('pipeline_type', this.table_header);
        }
    },

    /**
     * Gets triggered when switching pipeline tabs
     * Changes the css classes to reflect the selected tab
     * @param event
     */
    changePipeline: function(event) {
        if (this.$(event.currentTarget).hasClass('selected')) {
            return;
        }
        this.$('button[name=pipelineBtn]').removeClass('selected');
        this.$(event.currentTarget).addClass('selected');
        var pipelineType = this.$(event.currentTarget).data('pipeline');
        this.context.get('model').set('pipeline_type', pipelineType);
        this.context.trigger('pipeline:recordlist:filter:changed');
        this.context.trigger('filter:clear');
    },

    /**
     * Opens the create drawer for the user to create a new record when the create button on headerpane is clicked
     */
    createNewRecord: function() {
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: this.module,
            }
        }, _.bind(function(context, model) {
            if (model && model.dataFetched) {
                this.context.trigger('pipeline:recordlist:model:created', model);
            }
        }, this));
    }
})
