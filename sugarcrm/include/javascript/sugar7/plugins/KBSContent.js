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

            events: {
                'click [name=template]': 'launchTemplateDrawer'
            },

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

            /**
             * Handler to create localization.
             * @param {Data.Model} model Parent model.
             */
            createLocalization: function(model) {
                this.createRelatedContent(model, this.CONTENT_LOCALIZATION);
            },

            /**
             * Handler to create revision.
             * @param {Data.Model} model Parent model.
             */
            createRevision: function(model) {
                this.createRelatedContent(model, this.CONTENT_REVISION);
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
                            self._onCreateLocalization(prefill, parentModel);
                        } else {
                            self._onCreateRevision(prefill, parentModel);
                        }
                    }
                });
            },

            /**
             * Method called on create localization.
             *
             * Setup additional model properties for localization.
             * If no available langs for localizations it shows alert message.
             *
             * @param {Data.Model} prefill New created model.
             * @param {Data.Model} parentModel Parent model.
             * @private
             */
            _onCreateLocalization: function(prefill, parentModel) {

                if (!this.checkCreateLocalization(parentModel)) {
                    app.alert.show('localizations', {
                        level: 'warning',
                        title: app.lang.get('LBL_CANNOT_CREATE_LOCALIZATION', 'KBSContents'),
                        autoClose: false
                    });
                    return;
                }

                prefill.set(
                    'related_languages',
                    this.getAvailableLangsForLocalization(parentModel),
                    {silent: true}
                );
                prefill.unset('language', {silent: true});
                prefill.unset('kbsarticle_id', {silent: true});

                this._openCreateRelatedDrawer(prefill, parentModel);
            },

            /**
             * Method called on create localization.
             *
             * Setup additional model properties for revision.
             *
             * @param {Data.Model} prefill New created model.
             * @param {Data.Model} parentModel Parent model.
             * @private
             */
            _onCreateRevision: function(prefill, parentModel) {
                prefill.set('useful', parentModel.get('useful'));
                prefill.set('notuseful', parentModel.get('notuseful'));
                prefill.set(
                    'related_languages',
                    [parentModel.get('language')],
                    {silent: true}
                );

                this._openCreateRelatedDrawer(prefill, parentModel);
            },

            /**
             * Open drawer for create form.
             * @param {Data.Model} prefill New created model.
             * @param {Data.Model} parentModel Parent model.
             * @private
             */
            _openCreateRelatedDrawer: function(prefill, parentModel) {
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
            },

            /**
             * Checks if there are available lang for localization.
             *
             * @param {Data.Model} model Parent model.
             * @return {boolean} True on success otherwise false.
             */
            checkCreateLocalization: function(model) {
                var langs = this.getAvailableLangsForLocalization(model),
                    config = app.metadata.getModule('KBSDocuments', 'config');

                if (!langs || !config['languages']) {
                    return true;
                }

                if (!config['languages'] || config['languages'].length == langs.length) {
                    return false;
                }

                return true;
            },

            /**
             * Returns array of langs for that there is localization.
             * @param {Data.Model} model Parent model.
             * @return {Array} Array of langs.
             */
            getAvailableLangsForLocalization: function(model) {
                return model.get('related_languages') || [];
            },

            /**
             * Open the drawer with the KBSContentTemplates selection list layout and override the
             * kbdocument_body field with selected template.
             */
            launchTemplateDrawer: function() {
                app.drawer.open({
                        layout: 'selection-list',
                        context: {
                            module: 'KBSContentTemplates'
                        }
                    },
                    _.bind(function(model) {
                        if (!model) {
                            return;
                        }
                        var self = this;
                        var template = app.data.createBean('KBSContentTemplates', { id: model.id });
                        template.fetch({
                            success: function(template) {
                                if (this.disposed === true) {
                                    return;
                                }
                                var replace = function() {
                                    self.model.set('kbdocument_body', template.get('body'));
                                };
                                if (!self.model.get('kbdocument_body')) {
                                    replace();
                                } else {
                                    app.alert.show('override_confirmation', {
                                        level: 'confirmation',
                                        messages: app.lang.get('LBL_TEMPATE_LOAD_MESSAGE', self.module),
                                        onConfirm: replace
                                    });
                                }
                            }
                        });
                    }, this)
                );
            }
        });
        
    });
})(SUGAR.App);
