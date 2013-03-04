({
	events: {
        'click .addPost': 'addPost'
    },

    className: "row omnibar activitystream-post",

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
            parent_type: parentType || null,
            data: {
                value: this.layout._processTags(this.$('div.sayit'))
            }
        };

        var bean = app.data.createBean('Activities');
        bean.save(payload, {
            success: function(model) {
                self.$('div.sayit').html('');
                self.layout.prependPost(model);
                // We need to add any attachments we may have over here.
            }
        });
    }
})
