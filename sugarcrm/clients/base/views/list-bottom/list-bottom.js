({
/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListViewBottom
 * @alias SUGAR.App.layout.ListViewBottom
 * @extends View.View
 */
    events: {
        'click [name=show_more_button]': 'showMoreRecords',
        'click [name=show_more_button_back]': 'showPreviousRecords',
        'click [name=show_more_button_forward]': 'showNextRecords',
        'click .search': 'showSearch'
    },
    render: function() {
        var self = this;
        if (app.acl.hasAccess('create', this.module)) {
            this.context.set('isCreateEnabled', true);
        }
        app.view.View.prototype.render.call(self);
    },        
    showMoreRecords: function() {
        var self = this, options;
        app.alert.show('show_more_records', {level:'process', title:'Loading'});

        // If we're in search mode and set search-specific options
        options = self.isInSearchMode() ? self.getSearchOptions() : {};

        options.add = true;
        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };
        app.controller.context.get('collection').paginate(options);
    },
    showPreviousRecords: function() {
        var self = this;
        app.alert.show('show_previous_records', {level:'process', title:'Loading'});
        app.controller.context.get('collection').paginate({
            page: -1,
            success: function() {
                app.alert.dismiss('show_previous_records');
                self.layout.trigger("list:paginate:success");
                self.render();
            }
        });
    },
    showNextRecords: function() {
        var self = this;
        app.alert.show('show_next_records', {level:'process', title:'Loading'});
        app.controller.context.get('collection').paginate({
            success: function() {
                app.alert.dismiss('show_next_records');
                self.layout.trigger("list:paginate:success");
                self.render();
            }
        });
    },
    showSearch: function() {
        // Toggle on search filter and off the pagination buttons
        this.$('.search').toggleClass('active');
        this.layout.trigger("list:search:toggle");
    },
    getSearchOptions: function() {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if(app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if(previousTerms) {
                term = previousTerms[this.module];
            } 
        }
        // build search-specific options and return
        options = {
            params: { 
                q: term,
            },
            fields: collection.fields ? collection.fields : app.controller.context.get('collection')
        };
        return options;
    },

    isInSearchMode: function() {
        return this.$('.search').hasClass('active');
    },

    bindDataChange: function() {
        if(this.context.get('collection')) {
            this.context.get('collection').on("reset", this.render, this);
        }
    }

})
