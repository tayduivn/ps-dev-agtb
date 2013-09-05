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
    events: {
        'click .actionLink[data-event="true"]' : '_handleActionLink'
    },

    plugins: ['LinkedModel'],

    /**
     * When menu item is clicked, warn if open drawers, reset drawers and open create
     * @param evt
     * @private
     */
    _handleActionLink: function(evt) {
        var $actionLink = $(evt.currentTarget),
            module = $actionLink.data('module');
        this.actionLayout = $actionLink.data('layout');

        if (app.drawer.count() > 0) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: 'LBL_WARN_UNSAVED_EDITS',
                onConfirm: _.bind(function() {
                    app.drawer.reset();
                    this.createRelatedRecord(module);
                }, this)
            });
        } else {
            this.createRelatedRecord(module);
        }
    },

    /**
     * Route to Create Related record UI for a BWC module.
     *
     * @param {String} module Module name.
     */
    routeToBwcCreate: function(module) {
        var context = this.getRelatedContext(module);
        if (context) {
            app.bwc.createRelatedRecord(module, this.context.get('model'), context.link);
        } else {
            var route = app.bwc.buildRoute(module, null, 'EditView');
            app.router.navigate(route, {trigger: true});
        }
    },

    /**
     * Returns context link and module name
     * if possible to create a record with context.
     *
     * @param {String} module Module name.
     * @return {Array/Undefined}
     */
    getRelatedContext: function(module) {
        var meta = app.metadata.getModule(module),
            context;

        if (meta && meta.menu.quickcreate.meta.related) {
            var parentModel = this.context.get('model');
            context = _.find(
                meta.menu.quickcreate.meta.related,
                function(metadata) {
                    return metadata.module === parentModel.module;
                }
            );
        }

        return context;
    },

    /**
     * Open the appropriate quick create layout in a drawer
     *
     * @param {String} module Module name.
     */
    openCreateDrawer: function(module) {
        var relatedContext = this.getRelatedContext(module),
            model = null;

        if (relatedContext) {
            model = this.createLinkModel(this.context.get('model'), relatedContext.link);
        }
        app.drawer.open({
            layout: this.actionLayout || 'create-actions',
            context: {
                create: true,
                module: module,
                model: model,
            }
        }, _.bind(function (refresh, model) {
            if (refresh) {
                if (model && relatedContext) {
                    // Refresh the subpanel.
                    this.context.trigger('panel-top:refresh', relatedContext.link);
                    return;
                }
                //Check main context to see if it needs to be updated
                this._loadContext(app.controller.context, module);
                //Also check child contexts for updates
                if (app.controller.context.children) {
                    _.each(app.controller.context.children, function(context){
                        this._loadContext(context, module);
                    }, this);
                }
            }
        }, this));
    },
    /**
     * Conditionally load context if it is for given module
     * @param context Context to load
     * @param module Module name to check
     * @private
     */
    _loadContext: function(context, module){
        var collection = context.get('collection');
        if (collection && collection.module === module) {
            var options = {
                //Don't show alerts for this request, background update
                showAlerts: false
            };
            collection.resetPagination();
            context.resetLoadFlag(false);
            context.set('skipFetch', false);
            options = _.extend(options, context.get('collectionOptions'));
            context.loadData(options);
        }
    }
})
