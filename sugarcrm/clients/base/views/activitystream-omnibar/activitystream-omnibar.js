({
	events: {
        'click .addPost': 'addPost'
    },

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
            myPost = this.$(".activitystream-post"),
            myPostId = this.context.get("modelId"),
            myPostModule = this.module,
            myPostUrl = 'ActivityStream',
            myPostContents,
            attachments = myPost.find('.activitystream-pending-attachment');

        if (myPostModule !== "Home") {
            myPostUrl += '/' + myPostModule;
            if (!_.isUndefined(myPostId)) {
                myPostUrl += '/' + myPostId;
            }
        }

        myPostUrl = app.api.buildURL(myPostUrl);
        myPostContents = this.layout._processTags(myPost.find('div.sayit'));
        this.layout._addPostComment(myPostUrl, myPostContents, attachments);
    }
})
