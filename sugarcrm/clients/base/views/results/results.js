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
        app.alert.show('show_more_search_results', {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});
        options.add = true;
        options.success = function() {
            app.alert.dismiss('show_more_search_results');
            console.log(self.collection);
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
