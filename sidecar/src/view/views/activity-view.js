(function(app) {

    /**
     * View that displays the activity stream.
     * @class View.Views.ActivityView
     * @alias SUGAR.App.layout.ActivityView
     * @extends View.View
     */
    app.view.views.ActivityView = app.view.View.extend({
        events: {
            'click #saveNote': 'saveNote',
            'click .search': 'showSearch',
            'click .addNote': 'openNoteModal',
            'click .icon-eye-open': 'loadChildDetailView'
        },
        render: function() {
            app.view.View.prototype.render.call(this);

            this.$el.find("span[name=id]").each(function() {
                $(this).hide().parent().parent().parent().attr("data-id", $(this).text());
            })
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

            var newNote = app.data.createRelatedBean(this.app.controller.context.state.model, null, "notes", args);
            newNote.save(null, {
                relate: true,
                success: function(data) {
                    self.$el.find('#saveNote').button();
                    self.$el.find('#noteModal').modal('hide').find('form').get(0).reset();
                    self.context.state.collection.add(newNote);
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
        }
    });

})(SUGAR.App);