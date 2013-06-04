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
 *  (i) the "Powered by SugarCRM" logo and
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
    extendsFrom:'ResultsView',
    sidebarClosed: false,
    closeSidebar: function () {
        if (!this.sidebarClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.sidebarClosed = true;
        }
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this._addPreviewEvents();
    },
    _render: function() {
        var self = this;
        self.lastQuery = self.context.get('query');
        self.fireSearchRequest(function(collection) {
            // Bug 57853: Will brute force dismiss search dropdown if still present.
            $('.search-query').searchahead('hide');
            // Add the records to context's collection
            if(collection && collection.length) {
                app.view.View.prototype._render.call(self);
                self.setHeaderpaneTitle();
            } else {
                self.setHeaderpaneTitle(app.lang.getAppString('LNK_SEARCH_NO_RESULTS'));
            }
        });
    },
    _addPreviewEvents: function() {
        app.events.on("list:preview:decorate", this.decorateRow, this);
        this.collection.on("reset", function() {
            //When fetching more records, we need to update the preview collection
            app.events.trigger("preview:collection:change", this.collection);
            if (this._previewed) {
                this.decorateRow(this._previewed);
            }
        }, this);
    },
    setHeaderpaneTitle: function(overrideMessage) {
        // Once the sidebartoggle rendered we close the sidebar so the arrows are updated SP-719. Note we don't
        // start listening for following event until we set title (since that will cause toggle render again!)
        app.controller.context.on("sidebarRendered", this.closeSidebar, this);
        // Actually sets the title on the headerpane
        this.context.trigger("headerpane:title", overrideMessage ||
            app.utils.formatString(app.lang.get('LBL_PORTAL_SEARCH_RESULTS_TITLE'),{'query' : this.lastQuery}));
    },
    // Highlights current result row. Also, executed when preview view fires an
    // preview:decorate event (e.g. user clicks previous/next arrows on side preview)
    decorateRow: function(model) {
        this._previewed = model;
        this.$("li.search").removeClass("on");
        if (model) {
            this.$("li.search[data-id=" + model.get("id") + "]").addClass("on");
        }
    },
    // Loads the right side preview view when clicking icon for a particular search result.
    loadPreview: function(e) {
        var searchRow, selectedResultId, model;
        if (this.sidebarClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.sidebarClosed = false;
        }
        searchRow = this.$(e.currentTarget).closest('li.search');
        // Grab search result model corresponding to preview icon clicked
        selectedResultId = $(searchRow).data("id");
        model = this.collection.get(selectedResultId);
        this.decorateRow(model);
        // This will result in result's data being displayed on preview
        app.events.trigger("preview:render", model, this.collection, false);
    }
})
