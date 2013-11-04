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
    extendsFrom:"PanelLayout",

    /**
     * @override
     */
    initialize: function(opts) {
        opts.type = "panel";
        //Check for the override_subpanel_list_view from the parent layout metadata and replace the list view if found.
        if (opts.meta && opts.def && opts.def.override_subpanel_list_view) {
            _.each(opts.meta.components, function(def){
                if (def.view && def.view == "subpanel-list") {
                    def.view = opts.def.override_subpanel_list_view;
                }
            });
            // override last_state.id with "override_subpanel_list_view" for unique state name.
            if(opts.meta.last_state.id) {
                opts.meta.last_state.id = opts.def.override_subpanel_list_view;
            }
        }
        app.view.invokeParent(this, {type: 'layout', name: 'panel', method: 'initialize', args:[opts]});

        // binding so subpanels can trigger other subpanels to reload by link name
        // example: ctx.trigger('subpanel:reload', {links: ['opportunities','revenuelineitems']});
        this.context.parent.on('subpanel:reload', function(args) {
            if (!_.isUndefined(args) && _.isArray(args.links) && _.contains(args.links, this.context.get('link'))) {
                this.context.reloadData({recursive: false});
            }
        }, this);
    },

    /**
     * dispose events
     * @private
     */
    _dispose: function() {
        if (this.context.parent) {
            this.context.parent.off('subpanel:reload', null, this);
        }
        app.view.invokeParent(this, {type: 'layout', name: 'panel', method: '_dispose'});
    }
})
