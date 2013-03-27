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
 * View that displays search results.
 * @class View.Views.ResultsView
 * @alias SUGAR.App.layout.ResultsView
 * @extends View.View
 */
    _meta: {
        "buttons": [
            {
                "name": "show_more_button",
                "type": "button",
                "label": "Show More",
                "class": "loading wide"
            }
        ]
    },
    events: {
        'click [name=name]': 'gotoDetail',
        'click .icon-eye-open': 'loadPreview',
        'click [name=show_more_button]': 'showMoreResults'
    },
    initialize: function(options) {
        this.options.meta = this._meta;
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail"; // will use detail sugar fields
    },
    /**
     * Uses query in context and fires a search request thereafter rendering
     */
    _render: function() {
        var self = this;
        self.lastQuery = self.context.get('query');

        self.fireSearchRequest(function(collection) {
            // Bug 57853: Will brute force dismiss search dropdown if still present.
            $('.search-query').searchahead('hide');

            // Add the records to context's collection
            if(collection && collection.length) {
                app.view.View.prototype._render.call(self);
                self.renderSubnav();
            } else {
                self.renderSubnav(app.lang.getAppString('LNK_SEARCH_NO_RESULTS'));
            }
        });
    },

    /**
     * Renders subnav based on search message appropriate for query term.
     */
    renderSubnav: function(overrideMessage) {
        if (this.context.get('subnavModel')) {
            this.context.get('subnavModel').set({
                'title': overrideMessage
                    || app.utils.formatString(app.lang.get('LBL_PORTAL_SEARCH_RESULTS_TITLE'),{'query' : this.lastQuery})
            });
        }
    },

    /**
     * Uses MixedBeanCollection to fetch search results.
     */
    fireSearchRequest: function (cb, offset) {
        var mlist = null, self = this, options;
        mlist = app.metadata.getModuleNames(true); // visible
        options = {
            //Show alerts for this request
            showAlerts: true,
            query: self.lastQuery, 
            success:function(collection) {
                cb(collection);
            },
            module_list: mlist,
            error:function(error) {
                cb(null); // lets callback know to dismiss the alert
            }
        };
        if (offset) options.offset = offset;
        this.collection.fetch(options);
    },
    /**
     * Show more search results
     */
    showMoreResults: function() {
        var self = this, options = {};
        options.add = true;
        //Show alerts for this request
        options.showAlerts = true;
        options.success = function() {
            app.view.View.prototype._render.call(self);
            window.scrollTo(0, document.body.scrollHeight);
        };
        this.collection.paginate(options);
    },
    gotoDetail: function(evt) {
        var href = this.$(evt.currentTarget).parent().parent().attr('href');
        window.location = href;
    },            
    /**
     * Loads the right side preview view when clicking icon for a particular search result.
     */
    loadPreview: function(e) {
        var localGGrandparent, correspondingResultId, model;
        localGGrandparent = this.$(e.currentTarget).parent().parent().parent();

        // Remove previous 'on' class on lists <li>'s; add to clicked <li>
        $(localGGrandparent).parent().find('li').removeClass('on');
        $(localGGrandparent).addClass("on");
        correspondingResultId = $(localGGrandparent).find('p a').attr('href').split('/')[1];

        // Grab search result model corresponding to preview icon clicked
        model = this.collection.get(correspondingResultId);

        // Fire on parent layout .. works nicely for relatively simple page ;=) 
        this.layout.layout.trigger("search:preview", model);
    }
})
