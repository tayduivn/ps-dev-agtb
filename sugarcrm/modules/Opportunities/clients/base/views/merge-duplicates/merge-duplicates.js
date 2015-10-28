/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'MergeDuplicatesView',

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        var config = app.metadata.getModule('Forecasts', 'config');
        if(config && config.is_setup && config.forecast_by === 'Opportunities') {
            // make sure forecasts exists and is setup
            this.collection.on('change:sales_stage', function(model) {
                var salesStage = model.get('sales_stage');
                if(salesStage) {
                    if(_.contains(config.sales_stage_won, salesStage)) {
                        // check if the sales_stage has changed to a Closed Won stage
                        if(config.commit_stages_included.length) {
                            // set the commit_stage to the first included stage
                            model.set('commit_stage', _.first(config.commit_stages_included))
                        } else {
                            // otherwise set the commit stage to just "include"
                            model.set('commit_stage', 'include');
                        }
                    } else if(_.contains(config.sales_stage_lost, salesStage)) {
                        // check if the sales_stage has changed to a Closed Lost stage
                        // set the commit_stage to exclude
                        model.set('commit_stage', 'exclude');
                    }
                }
            }, this);
        }
    }
})
