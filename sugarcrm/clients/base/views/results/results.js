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
        'click span.label': 'sortByModuleName',
        'hover span.label': 'addPointer',
        'blur span.label': 'removePointer',
        'click [name=show_more_button]': 'showMoreResults',
    },
    initialize: function(options) {
        this.options.meta = this._meta;
        app.view.View.prototype.initialize.call(this, options);
        this.moduleListSingular = app.lang.getAppListStrings("moduleListSingular");
    },

    /**
     * Uses query in context and fires a search request thereafter rendering
     */
    _renderHtml: function() {
        var self = this;
        self.lastQuery = self.context.get('query');
        self.fireSearchRequest(function(data) {
            // Add the records to context's collection
            if(data && data.records && data.records.length) {
                self.updateCollection(data);
                app.view.View.prototype._renderHtml.call(self);
                self.renderSubnav();
            } else {
                self.renderSubnav('No results found for "'+self.lastQuery+'"');
            }
        });
    },
    /**
     * Updates the collection with search results.
     */
    updateCollection: function(data) {
        this.collection.add(data.records, {silent: true});
        this.collection.next_offset = this.nextOffset = data.next_offset ? data.next_offset : -1;
    },
    /**
     * Renders subnav based on search message appropriate for query term.
     */
    renderSubnav: function(overrideMessage) {
        if (this.context.get('subnavModel')) {
            this.context.get('subnavModel').set({
                'title': overrideMessage || 'Show search results for "'+this.lastQuery+'"'
            });
        }
    },
    /**
     * Helper to call api.search
     */
    fireSearchRequest: function (cb, offset) {
        var mlist = '', self = this, params;
        mlist = app.metadata.getDelimitedModuleList(',', true);
        params = {q: self.lastQuery, moduleList: mlist, max_num: app.config.maxQueryResult};
        if (offset) params.offset = offset;

        app.api.search(params, {
            success:function(data) {
                cb(data);
            },
            error:function(error) {
                cb(null); // dismiss the alert
                app.error.handleHttpError(error, self);
                app.logger.error("Failed to fetch search results " + this + "\n" + error);
            }
        });
    },
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
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
    },
    /**
     * Sorts by submodule name per spec.
     */
    sortByModuleName: function(evt) {
        var li = this.$('li.search').get();
        li.sort(function(a, b) {
            a = this.$('span.label', a).text();
            b = this.$('span.label', b).text();
            return (a < b) ? -1 : ((a > b) ? 1 : 0);
        });
        this.$('ul.nav-tabs').append(li).show();
    },
    addPointer: function(evt) {
        this.$(evt.currentTarget).css('cursor', 'pointer');
    },
    removePointer: function(evt) {
        this.$(evt.currentTarget).css('cursor', 'none');
    },
    /**
     * Show more search results
     */
    showMoreResults: function() {
        var self = this;
        app.alert.show('show_more_search_results', {level: 'process', title: 'Loading'});

        self.fireSearchRequest(function(data) {
            app.alert.dismiss('show_more_search_results');

            // Add the records to context's collection
            if(data && data.records && data.records.length) {
                self.updateCollection(data);
                app.view.View.prototype.render.call(self);
                self.renderSubnav();
            } 
        }, this.collection.next_offset);
    }

})
