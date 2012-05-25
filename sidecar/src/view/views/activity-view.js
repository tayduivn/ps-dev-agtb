(function(app) {

    /**
     * View that displays the activity stream.
     * @class View.Views.ActivityView
     * @alias SUGAR.App.layout.ActivityView
     * @extends View.View
     */
    app.view.views.ActivityView = app.view.View.extend({
        events: {
            'click .search': 'showSearch',
            'click .addNote': 'openNoteModal',
            'click .icon-eye-open': 'loadChildDetailView',
            'click [name=show_more_button_back]': 'showPreviousRecords',
            'click [name=show_more_button_forward]': 'showNextRecords'
        },
        render: function() {
            app.view.View.prototype.render.call(this);

            this.$("span[name=id]").each(function() {
                $(this).hide().parent().parent().parent().attr("data-id", $(this).text());
            });
        },
        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
                this.collection.on("add", this.render, this);
            }
        },

        // Delegate events
        showSearch: function() {
            var $searchEl = $('.search');
            $searchEl.toggleClass('active');
            $searchEl.parent().parent().parent().find('.dataTables_filter').toggle();
            $searchEl.parent().parent().parent().find('.form-search').toggleClass('hide');
            return false;
        },
        openNoteModal: function() {
            // triggers an event to show the modal
            this.layout.trigger("app:view:activity:editmodal");
            this.$('li.open').removeClass('open');
            return false;
        },
        loadChildDetailView: function(e) {
            // UI fix
            this.$("li.activity").removeClass("on");
            this.$(e.currentTarget).parent().parent().parent().addClass("on");

            // gets the activityId in the data attribute
            var activityId = this.$(e.currentTarget).parent().parent().parent().data("id");

            // gets the activity model
            var activity = this.collection.get(activityId);

            // clears the current listened model and push the new one
            this.model.clear().set(activity.toJSON());
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