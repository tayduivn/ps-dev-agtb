(function(app) {

    /**
     * View that displays the activity stream.
     * @class View.Views.ResultsView
     * @alias SUGAR.App.layout.ResultsView
     * @extends View.View
     */
    app.view.views.ResultsView = app.view.View.extend({
        events: {
            'click [name=name]': 'gotoDetail',
            'click #saveNote': 'saveNote',
            'click .search': 'showSearch',
            'click .addNote': 'openNoteModal',
            'click .icon-eye-open': 'loadChildDetailView',
            'click [name=show_more_button_back]': 'showPreviousRecords',
            'click [name=show_more_button_forward]': 'showNextRecords'
        },
        render: function() {
            var self = this;
            self.lastQuery = self.context.get('query');
            self.fireSearchRequest(function(data) {
                // Add the records to context's collection
                if(data && data.records && data.records.length) {
                    self.context.get('collection').add(data.records, {silent: true});
                    app.view.View.prototype.render.call(self);

                    self.renderSubnav();
                } else {
                    // TODO: No results message???
                    app.logger.debug("TODO: No search results found .. need to display no results message. ");
                }
            });
        },
        renderSubnav: function() {
            if (app.additionalComponents.staticSubnav) {
                app.additionalComponents.staticSubnav.display('Show search results for "'+this.lastQuery+'"');
            }
        },
        fireSearchRequest: function (cb) {
            var mlist = '', self = this;
            mlist = app.metadata.getDelimitedModuleList(',');

            app.api.search(self.lastQuery, 'name, id', mlist, app.config.maxQueryResult, {
                success:function(data) {
                    cb(data);
                },
                error:function(xhr, e) {
                    app.error.handleHTTPError(xhr, e, self);
                    app.logger.error("Failed to fetch search results " + this + "\n" + e);
                }
            });
        },
        
        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
            }
        },


        // Delegate events
        saveNote: function() {
            var self = this;
            this.$el.find('#saveNote').button('loading');

            var args = {
                name: this.$el.find('[name=subject]').val(),
                description: this.$el.find('[name=description]').val(),
                portal_flag: true
            }

            var newNote = app.data.createRelatedBean(this.app.controller.context.attributes.model, null, "notes", args);
            newNote.save(null, {
                relate: true,
                success: function(data) {
                    self.$el.find('#saveNote').button();
                    self.$el.find('#noteModal').modal('hide').find('form').get(0).reset();
                    self.context.attributes.collection.add(newNote);
                    self.render();
                },
                error: function(data) {
                    self.$el.find('#saveNote').button();
                    self.$el.find('#noteModal').modal('hide').find('form').get(0).reset();
                }
            });
        },
        showSearch: function() {
            var $searchEl = $('.search');
            $searchEl.toggleClass('active');
            $searchEl.parent().parent().parent().find('.dataTables_filter').toggle();
            $searchEl.parent().parent().parent().find('.form-search').toggleClass('hide');
            return false;
        },
        gotoDetail: function(evt) {
            // TODO: Find root cause why link is not followed
            var href = this.$(evt.currentTarget).parent().parent().attr('href');
            window.location = href;
        },            
        openNoteModal: function() {
            this.$el.find('#noteModal').modal('show');
            this.$el.find('li.open').removeClass('open');
            return false;
        },
        loadChildDetailView: function(e) {
            var activityId = $(e.currentTarget).parent().parent().parent().attr("data-id");
            $("li.activity").removeClass("on");
            $(e.currentTarget).parent().parent().parent().addClass("on");

            var activity = this.collection.get(activityId);
            app.events.trigger("app:view:activity:subdetail", activity);
        },
        showPreviousRecords: function() {
            var self = this;
            app.alert.show('show_previous_records', {level: 'process', title: 'Loading'});
            this.context.get("collection").paginate({
                page: -1,
                success: function() {
                    app.alert.dismiss('show_previous_records');
                    self.render();
                },
                relate: true
            });
        },
        showNextRecords: function() {
            var self = this;
            app.alert.show('show_next_records', {level: 'process', title: 'Loading'});
            this.context.get("collection").paginate({
                success: function() {
                    app.alert.dismiss('show_next_records');
                    self.render();
                },
                relate: true
            });
        }

    });

})(SUGAR.App);
