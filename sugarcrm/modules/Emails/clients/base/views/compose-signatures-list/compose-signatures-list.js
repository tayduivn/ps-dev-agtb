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
    extendsFrom: "BaselistView",

    initialize: function(options) {
        _.bindAll(this);
        app.view.views.BaselistView.prototype.initialize.call(this, options);

        // turn off sorting
        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                field.sortable = false;
            });
        });

        // remove links
        this.on("render", this._removeLinks, this);

        // hook into DataManager.sync to add a custom endpoint
        app.events.on("data:sync:start", this._sync, this);
    },

    /**
     * Add additional CSS classes to the table in the list view to provide more styles.
     *
     * @private
     */
    _renderHtml: function() {
        app.view.views.BaselistView.prototype._renderHtml.call(this);
        this.$("table.table-striped").addClass("duplicates highlight");
    },

    /**
     * Remove any surrounding anchor tags from content displayed within the list view; leaving just the text. It is
     * undesirable to allow users to click links that navigate them away from the page when in the context of a modal
     * operation, like a drawer.
     *
     * @private
     */
    _removeLinks: function() {
        this.$("a:not(.rowaction)").contents().unwrap();
    },

    addRowActions: function(panel, options) {
        panel = app.view.views.BaselistView.prototype.addRowActions.call(this, panel, options);

        if (options.meta.showPreview === true) {
            panel.fields = panel.fields.concat({
                type:      "rowaction",
                css_class: "btn",
                tooltip:   "LBL_PREVIEW",
                event:     "list:preview:fire",
                icon:      "icon-eye-open"
            });
        }

        return panel;
    },

    /**
     * Event callback for DataManager.sync in order to add a custom endpoint to the options.
     *
     * @param method
     * @param model
     * @param options
     * @private
     */
    _sync: function(method, model, options) {
        options          = options || {};
        options.endpoint = function(method, model, options, callbacks) {
            var url = app.api.buildURL("Signatures", method, null, options.params);
            return app.api.call(method, url, null, callbacks);
        };
    }
})
