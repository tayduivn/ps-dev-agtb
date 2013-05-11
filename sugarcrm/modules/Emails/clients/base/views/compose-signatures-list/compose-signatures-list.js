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
    /**
     * @class EmailTemplates.View.ComposeTemplates
     * @alias SUGAR.App.view.views.EmailTemplatesComposeTemplatesView
     * @extends View.SelectionListView
     */
    extendsFrom: "SelectionListView",
    plugins: ['list-disable-sort'],
    _beanCollectionSync: null,

    initialize: function(options) {
        _.bindAll(this);
        app.view.invokeParent(this, {type: 'view', name: 'selection-list', method: 'initialize', args: [options]});

        // remove links
        this.on("render", this._removeLinks);

        // treat the DataManager.sync override like a before_sync callback in order to add additional options to the
        // call
        // copying the original DataManager.sync into an instance variable allows us to call it again from within our
        // override, in order to continue with normal procedures after injecting our options
        this._beanCollectionSync = this.collection.sync;
        this.collection.sync     = this._sync;
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

    /**
     * Override of DataManager.sync in order to add a custom endpoint to the options.
     *
     * @param method
     * @param model
     * @param options
     * @return {*}
     * @private
     */
    _sync: function(method, model, options) {
        options          = options || {};
        options.endpoint = function(method, model, options, callbacks) {
            var url = app.api.buildURL("Signatures", method, null, options.params);
            return app.api.call(method, url, null, callbacks);
        };

        return this._beanCollectionSync(method, model, options);
    }
})
