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
    toggledClosed: false,
    closeSidebar: function () {
        if (!this.toggledClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = true;
        }
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
    setHeaderpaneTitle: function(overrideMessage) {
        // Once the sidebartoggle rendered we close the sidebar so the arrows are updated SP-719. Note we don't
        // start listening for following event until we set title (since that will cause toggle render again!)
        app.controller.context.on("sidebartoggle:rendered", this.closeSidebar, this);
        // Actually sets the title on the headerpane
        this.context.trigger("headerpane:title", overrideMessage ||
            app.utils.formatString(app.lang.get('LBL_PORTAL_SEARCH_RESULTS_TITLE'),{'query' : this.lastQuery}));
    },
    // Loads the right side preview view when clicking icon for a particular search result.
    loadPreview: function(e) {
        var searchRow, selectedResultId, model;
        if (this.toggledClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = false;
        }
        // Get the currently selected search result
        searchRow = this.$(e.currentTarget).closest('li');
        // Remove previous 'on' class on lists and apply to clicked
        $(searchRow).parent().find('li').removeClass('on');
        $(searchRow).addClass("on");
        // Grab search result model corresponding to preview icon clicked
        selectedResultId = $(searchRow).find('p a').attr('href').split('/')[1];
        model = this.collection.get(selectedResultId);
        // This will result in result's data being displayed on preview
        app.events.trigger("preview:render", model);
    }
})
