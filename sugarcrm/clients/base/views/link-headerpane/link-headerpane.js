/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    extendsFrom: 'HeaderpaneView',
    linkModule: null,
    link: null,
    initialize: function (options) {
        this.plugins = _.clone(this.plugins) || [];
        this.plugins.push('LinkedModel');
        this.events = _.extend({}, this.events || {}, {
            'click [name=create_button]': 'createClicked',
            'click [name=cancel_button]': 'cancelClicked',
            'click [name=select_button]': 'selectClicked'
        });
        this.action = options.meta.action;
        var meta = app.metadata.getView(null, options.name);

        options.meta = _.extend({type: 'headerpane'}, options.meta, meta[this.action]);
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args:[options]});
        this.context.on("link:module:select", this.setModule, this);
    },
    setModule: function (meta) {
        if (meta) {
            this.linkModule = meta.module;
            this.link = meta.link;
        } else {
            this.linkModule = null;
            this.link = null;
        }

    },
    _dispose: function () {
        this.context.off("link:module:select", null, this);
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: '_dispose'});
    },

    selectClicked: function () {
        if (_.isEmpty(this.link)) {
            app.alert.show('invalid-data', {
                level: 'error',
                messages: app.lang.get('ERROR_EMPTY_LINK_MODULE'),
                autoClose: true
            });
            return;
        }

        var parentModel = this.model,
            module = app.data.getRelatedModule(this.model.module, this.link),
            link = this.link,
            self = this;

        app.drawer.open({
            layout: 'link-selection',
            context: {
                module: module
            }
        }, function (model) {
            if (!model) {
                return;
            }
            var relatedModel = app.data.createRelatedBean(parentModel, model.id, link),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function (model) {
                        app.drawer.closeImmediately(self.context, model);
                    },
                    error: function (error) {
                        app.alert.show('server-error', {
                            level: 'error',
                            messages: 'ERR_GENERIC_SERVER_ERROR',
                            autoClose: false
                        });
                    }
                };
            relatedModel.save(null, options);
        });
    },
    createClicked: function () {
        if (_.isEmpty(this.link)) {
            app.alert.show('invalid-data', {
                level: 'error',
                messages: app.lang.get('ERROR_EMPTY_LINK_MODULE'),
                autoClose: true
            });
            return;
        }

        var model = this.createLinkModel(this.model, this.link);

        app.drawer.open({
            layout: 'create-actions',
            context: {
                module: model.module,
                model: model,
                create: true
            }
        }, function (context, model) {
            if (!model) {
                return;
            }
            app.drawer.closeImmediately(context, model);
        });
    },
    cancelClicked: function () {
        app.drawer.close();
    }
})
