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
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('KBSContent', ['view'], {

            CONTENT_LOCALIZATION: 1,
            CONTENT_REVISION: 2,

            /**
             * Attach events to create localization and revisions.
             *
             * @param {Object} component
             * @param {Object} plugin
             * @return {void}
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    this.context.on('button:create_localization_button:click', this.createLocalization, this);
                    this.context.on('button:create_revision_button:click', this.createRevision, this);
                });
            },

            createLocalization: function() {
                this.createRelatedContent(this.model, self.CONTENT_LOCALIZATION);
            },

            createRevision: function() {
                this.createRelatedContent(this.model, self.CONTENT_REVISION);
            },

            /**
             * Creates revision or localization for KB.
             * @param {Data.Bean} parentModel Parent model object.
             * @param {Number} type Type of created content.
             */
            createRelatedContent: function(parentModel, type) {
                var self = this,
                    prefill = app.data.createBean('KBSContents');

                parentModel.fetch({
                    success: function() {
                        prefill.copy(parentModel);
                        prefill.unset('id');
                        if (type === self.CONTENT_LOCALIZATION) {
                            prefill.unset('kbsarticle_id', {silent: true});
                        }

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
                                    app.router.buildRoute('KBSContents', newModel.id),
                                    {trigger: true}
                                );
                            }
                        });

                        prefill.trigger('duplicate:field', parentModel);
                    }
                });
            }
        });
    });
})(SUGAR.App);
