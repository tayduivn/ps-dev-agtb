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
({
    extendsFrom: 'PanelTopView',

    /**
     * {@inheritDoc}
     */
    createRelatedClicked: function(event) {
        var self = this,
            parentModel = this.context.parent.get('model'),
            prefill = app.data.createBean(this.parentModule);

        parentModel.fetch({
            success: function() {
                prefill.copy(parentModel);
                self.model.trigger('duplicate:before', prefill);
                prefill.unset('id');

                app.drawer.open({
                    layout: 'create-actions',
                    context: {
                        create: true,
                        model: prefill,
                        copiedFromModelId: parentModel.get('id')
                    }
                }, function(context, newModel) {
                    if (newModel && newModel.id) {
                        app.router.navigate(
                            app.router.buildRoute(self.parentModule, newModel.id),
                            {trigger: true}
                        );
                    }
                });

                prefill.trigger('duplicate:field', parentModel);
            }
        });
    }
})
