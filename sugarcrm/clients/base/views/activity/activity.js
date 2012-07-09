/**
 * View that displays the activity stream.
 * @class View.Views.ActivityView
 * @alias SUGAR.App.layout.ActivityView
 * @extends View.View
 */
({
    events: {
        'click .search': 'showSearch',
        'click .addNote': 'openNoteModal',
        'click .activity a': 'loadChildDetailView',
        'click [name=show_more_button]': 'showMoreRecords'
    },
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },
    showSearch: function() {
        var $searchEl = $('.search');
        $searchEl.toggleClass('active');
        $searchEl.parent().parent().parent().find('.dataTables_filter').toggle();
        $searchEl.parent().parent().parent().find('.form-search').toggleClass('hide');
        return false;
    },
    openNoteModal: function() {
        this.$('#noteModal').modal('show');
        // triggers an event to show the modal
        this.layout.trigger("app:view:activity:editmodal");
        this.$('li.open').removeClass('open');
        return false;
    },
    loadChildDetailView: function(e) {
        // UI fix
        this.$("li.activity").removeClass("on");
        var $parent = this.$(e.currentTarget).parents("li.activity");
        $parent.addClass("on");

        // gets the activityId in the data attribute
        var activityId = $parent.data("id");

        // gets the activity model
        var activity = this.collection.get(activityId);

        // clears the current listened model and push the new one
        this.model.clear().set(activity.toJSON());
    },
    showMoreRecords: function(evt) {
        var self = this, options;
        app.alert.show('show_more_records', {level:'process', title:'Loading'});

        // If in "search mode" (the search filter is toggled open) set q:term param
        options = self.filterOpened ? self.getSearchOptions() : {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;

        if (this.collection.link) {
            options.relate = true;
        }

        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };
        options.limit = this.limit;
        this.collection.paginate(options);
    }
})
