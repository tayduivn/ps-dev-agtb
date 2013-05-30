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
     * @class View.DupecheckListView
     * @alias SUGAR.App.view.views.DupecheckListView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    plugins: ['list-disable-sort', 'list-remove-links'],
    collectionSync: null,

    initialize: function(options) {
        _.bindAll(this);
        //turn off sorting & links for dupe check lists
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'initialize', args:[options]});

        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                field.sortable = false;
            });
        });

        this.context.on("dupecheck:fetch:fire", this.fetchDuplicates, this);

        if (this.context.has('dupeCheckModel')) {
            this.model = this.context.get('dupeCheckModel');
        }

        // Create an empty collection if it doesn't exist, since we need it for sync
        if(_.isUndefined(this.collection)){
            this.collection = app.data.createBeanCollection(this.module);
        }

        //save off the collection's sync so we can run our own and then run the original
        //this is so we can switch the endpoint out
        this.collectionSync = this.collection.sync;
        this.collection.sync = this.sync;
    },

    _renderHtml: function() {
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: '_renderHtml'});
        this.$('table.table-striped').addClass('duplicates highlight');
    },

    sync: function(method, model, options) {
        options = options || {};
        options.endpoint = this.endpoint;
        this.collectionSync(method, model, options);
    },

    endpoint: function(method, model, options, callbacks) {
        var url = app.api.buildURL(this.module, "duplicateCheck");
        return app.api.call('create', url, this.model.attributes, callbacks); //Dupe Check API requires POST
    },

    fetchDuplicates: function(model, options) {
        this.model = model;
        this.collection.fetch(options);
    },

    addActions: function() {
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'addActions'});
        if (this.meta.showPreview === true) {
            this.rightColumns.push({
                type: 'rowaction',
                css_class: 'btn',
                tooltip: 'LBL_PREVIEW',
                event: 'list:preview:fire',
                icon: 'icon-eye-open'
            });
        }
    }
})
