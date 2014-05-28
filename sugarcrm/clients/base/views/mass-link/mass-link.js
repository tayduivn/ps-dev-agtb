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
 * @class View.Views.Base.MassLinkView
 * @alias SUGAR.App.view.views.BaseMassLinkView
 * @extends View.Views.Base.MassupdateView
 */
({
    extendsFrom: 'MassupdateView',
    massUpdateViewName: 'masslink-progress',
    _defaultLinkSettings: {
        mass_link_chunk_size: 20
    },

    initialize: function(options) {
        this._super('initialize', [options]);

        var configSettings = (app.config.massActions && app.config.massActions.massLinkChunkSize) ?
                {mass_link_chunk_size: app.config.massActions.massLinkChunkSize} :
                {};

        this._settings = _.extend(
            {},
            this._defaultLinkSettings,
            configSettings,
            this.meta && this.meta.settings || {}
        );
    },

    /**
     * Overrides parent. Sets mass link related events
     */
    delegateListFireEvents: function() {
        this.layout.on('list:masslink:fire', _.bind(this.beginMassLink, this));
    },

    /**
     * Link multiple records in chunks
     */
    beginMassLink: function(options) {
        var parentModel = this.context.get('recParentModel'),
            link = this.context.get('recLink'),
            massLink = this.getMassUpdateModel(this.module),
            progressView = this.getProgressView();

        massLink.setChunkSize(this._settings.mass_link_chunk_size);

        //Extend existing model with a link function
        massLink = _.extend({}, massLink, {
            maxLinkAllowAttempt: options && options.maxLinkAllowAttempt || this.maxAllowAttempt,
            link: function(options) {
                //Slice a new chunk of models from the mass collection
                this.updateChunk();
                var model = this,
                    apiMethod = 'create',
                    linkCmd = 'link',
                    parentData = {
                        id: parentModel.id
                    },
                    url = app.api.buildURL(parentModel.module, linkCmd, parentData),
                    linkData = {
                        link_name: link,
                        ids: _.pluck(this.chunks, 'id')
                    },
                    callbacks = {
                        success: function(data, response) {
                            model.attempt = 0;
                            model.updateProgress();
                            if (model.length === 0) {
                                model.trigger('massupdate:end');
                                if (_.isFunction(options.success)) {
                                    options.success(model, data, response);
                                }
                            } else {
                                model.trigger('massupdate:always');
                                model.link(options);
                            }
                        },
                        error: function() {
                            model.attempt++;
                            model.trigger('massupdate:fail');
                            if (model.attempt <= this.maxLinkAllowAttempt) {
                                model.link(options);
                            } else {
                                app.alert.show('error_while_mass_link', {
                                    level: 'error',
                                    title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'),
                                    messages: app.lang.getAppString('ERR_HTTP_500_TEXT')
                                });
                            }
                        }
                    };
                app.api.call(apiMethod, url, linkData, callbacks);
            }
        });

        progressView.initCollection(massLink);
        massLink.link({
            success: _.bind(function() {
                this.layout.trigger('list:masslink:complete');
            }, this)
        });
    }
})
