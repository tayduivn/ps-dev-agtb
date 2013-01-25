({
    events: {
        'click .addPost': 'addPost'
    },

    initialize: function(opts) {
        _.bindAll(this);
        this.template = app.template.get("l.activitystream");

        // Assets for the activity stream post avatar
        this.user_id = app.user.get('id');
        this.full_name = app.user.get('full_name');
        this.picture_url = (app.user.get('picture')) ? app.api.buildFileURL({
            module: 'Users',
            id: this.user_id,
            field: 'picture'
        }) : app.config.siteUrl + "/styleguide/assets/img/profile.png";

        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    _placeComponent: function(component) {
        this.$el.find(".activitystream-list").append(component.el);
    },

    addPost: function() {
        this.trigger("stream:addPost:fire");
    }
})