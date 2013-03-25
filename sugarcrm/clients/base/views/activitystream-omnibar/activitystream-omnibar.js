({
    events: {
        'click .addPost': 'addPost',
        'change div[data-placeholder]': 'checkPlaceholder',
        'keydown div[data-placeholder]': 'checkPlaceholder',
        'keypress div[data-placeholder]': 'checkPlaceholder',
        'input div[data-placeholder]': 'checkPlaceholder'
    },

    className: "row omnibar activitystream-post",

    plugins: ['dragdrop_attachments', 'taggable'],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // Assets for the activity stream post avatar
        this.user_id = app.user.get('id');
        this.full_name = app.user.get('full_name');
        this.picture_url = (app.user.get('picture')) ? app.api.buildFileURL({
            module: 'Users',
            id: this.user_id,
            field: 'picture'
        }) : app.config.siteUrl + "/styleguide/assets/img/profile.png";
    },

    /**
     * Creates a new post.
     */
    addPost: function() {
        var self = this,
            parentId = this.context.parent.get("model").id,
            parentType = this.context.parent.get("model").module,
            attachments = this.$('.activitystream-pending-attachment'),
            payload = {
                activity_type: "post",
                parent_id: parentId || null,
                parent_type: parentType !== "Activities" ? parentType : null,
                data: {}
            };

        payload.data.value = this.getText(this.$('div.sayit'));
        if (this.getTags) {
            payload.data.tags = this.getTags(this.$('div.sayit'));
        }

        if (payload.data.value) {
            var bean = app.data.createBean('Activities');
            bean.save(payload, {
                success: function(model) {
                    self.$('div.sayit').html('').trigger('change').focus();
                    model.set('picture_url', self.picture_url);
                    self.layout.prependPost(model);
                }
            });
        }
        this.trigger("attachments:process");
    },

    getText: function($el) {
        return $el.contents().html();
    },

    checkPlaceholder: function(e) {
        var el = e.currentTarget;
        if (el.textContent) {
            el.dataset.hidePlaceholder = true;
        } else {
            delete el.dataset.hidePlaceholder;
        }
    }
})
