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
    events : {
        'click .actionLink' : '_handleActionLink'
    },

    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.user.on("change:module_list", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        // loadAdditionalComponents fires render before the private metadata is ready, check for this
        if( !(_.isEmpty(app.metadata.getStrings("mod_strings"))) ) {
            var moduleList = app.metadata.getModuleNames(true, "create");
            this.createMenuItems = this._getMenuMeta(moduleList);
            app.view.View.prototype._renderHtml.call(this);
        }
    },

    /**
     * Retrieve the createactions metadata from each module in the list
     * Uses the visible flag on the metadata to determine if admin has elected to hide the module from the list
     *
     * @param {Array} moduleList
     * @return {Array} list of visible menu item metadata
     */
    _getMenuMeta: function(moduleList) {
        var meta, menuItem, returnList = [];
        _.each(moduleList, function(module) {
            meta = app.metadata.getModule(module);
            if (meta && meta.menu && meta.menu.createaction) {
                menuItem = meta.menu.createaction.meta;
                if (_.isUndefined(menuItem.visible) || menuItem.visible === true) {
                    menuItem.module = module;
                    returnList.push(menuItem);
                }
            }
        }, this);
        return returnList;
    },

    /**
     * When menu item is clicked, warn if open drawers, reset drawers and open create
     * @param evt
     * @private
     */
    _handleActionLink: function(evt) {
        var $actionLink = $(evt.currentTarget),
            module = $actionLink.data('module'),
            layout = $actionLink.data('layout');

        if (app.drawer.count() > 0) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: 'WARN_UNSAVED_CHANGES',
                onConfirm: _.bind(function() {
                    app.drawer.reset();
                    this._openCreate(module, layout);
                }, this)
            });
        } else {
            this._openCreate(module, layout);
        }
    },

    /**
     * Open the appropriate quick create layout in a drawer
     *
     * @param module
     * @param [layout='create'] layout to open in the drawer
     * @private
     */
    _openCreate: function(module, layout) {
        layout = layout || 'create';

        app.drawer.open({
            layout: layout,
            context: {
                create: true,
                module: module
            }
        }, function (refresh) {
            if (refresh) {
                var collection = app.controller.context.get('collection');
                if (collection) {
                    collection.fetch();
                }
            }
        });
    },

    _dispose: function(){
        app.user.off("change:module_list", this.render);
        app.view.View.prototype._dispose.call(this);
    }
})
