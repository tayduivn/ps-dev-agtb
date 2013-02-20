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
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',

    initialize: function(options) {
        app.view.views.FlexListView.prototype.initialize.call(this, options);

        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                field.sortable = false;
            });
        });

        this.on("render", this._removeLinks, this);

        //set the filter on the collection
        if(!_.isEmpty(this.meta.filterDef)) {
            this.collection.filterDef = this.meta.filterDef;
        }
    },

    _renderHtml: function() {
        app.view.views.FlexListView.prototype._renderHtml.call(this);
        this.$('table.table-striped').addClass('duplicates highlight');
    },

    _removeLinks: function() {
        this.$('a:not(.rowaction)').contents().unwrap();
    },

    addActions: function() {
        app.view.views.FlexListView.prototype.addActions.call(this);

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
