({
    events:{
        'click .reply': 'showAddComment',
        'click .postReply': 'addComment',
        'click .post': 'showAddPost',
        'click .addPost': 'addPost',
        'click .more': 'showAllComments',
        'click .filterAll': 'showAllActivities',
        'click .filterMyActivities': 'showMyActivities', 
        'click .filterFavorites': 'showFavoritesActivities'        
    },

    initialize: function(options) {
    	this.opts = { params: {}};
        app.view.View.prototype.initialize.call(this, options);

        _.bindAll(this);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "ActivityStream") {
            if (this.context.get("modelId")) {
                this.opts = { params: { module: this.module, id: this.context.get("modelId") }};
            } else {
                this.opts = { params: { module: this.module }};
            }

            this.collection = app.data.createBeanCollection("ActivityStream");
            this.collection.fetch(this.opts);
        }
    },

    showAllComments: function(event) {
        event.preventDefault();
        this.$(event.currentTarget).closest('li').hide();
        this.$(event.currentTarget).closest('ul').find('div.extend').show();
        this.$(event.currentTarget).closest('ul').closest('li').find('.activitystream-comment').show();
    },

    showAddComment: function(event) {
        this.$(event.currentTarget).closest('li').find('.activitystream-comment').show();
        this.$(event.currentTarget).closest('li').find('.activitystream-comment').find('.sayit').focus();
    },

    addComment: function(event) {
        var self = this,
            myPost = this.$(event.currentTarget).closest('li'),
            myPostContents = myPost.find('input.sayit')[0].value,
            myPostId = this.$(event.currentTarget).data('id');

        this.app.api.call('create', this.app.api.buildURL('ActivityStream/ActivityStream/' + myPostId), {'value': myPostContents}, {success: function() {
            self.collection.fetch(self.opts)
        }});
    },

    showAddPost: function() {
        this.$(".activitystream-post").show();
    },

    addPost: function() {
        var self = this,
            myPost = this.$(".activitystream-post"),
            myPostContents = myPost.find('input.sayit')[0].value,
            myPostId = this.context.get("modelId"),
            myPostModule = this.module,
            myPostUrl = 'ActivityStream';

        if(myPostModule !== "ActivityStream") {
            myPostUrl += '/'+myPostModule;
            if(myPostId !== undefined) {
                myPostUrl += '/'+myPostId;
            }
        }

        this.app.api.call('create', this.app.api.buildURL(myPostUrl), {'value': myPostContents}, {success: function() {
            self.collection.fetch(self.opts)
        }});
    },

    showAllActivities: function(event) {
        this.opts.params.filter = 'all'; 
        this.collection.fetch(this.opts);
    },
    
    showMyActivities: function(event) {
        this.opts.params.filter = 'myactivities';
        this.collection.fetch(this.opts);
    },
    
    showFavoritesActivities: function(event) {
        this.opts.params.filter = 'favorites';  
        this.collection.fetch(this.opts);
    }, 
    
    _renderHtml: function() {
        _.each(this.collection.models, function(model) {
            var comments = model.get("comments");
            if (comments.length > 2) {
                comments[0]['_starthidden'] = true;
                comments[comments.length - 3]['_stophidden'] = true;
            }
        }, this);

        return app.view.View.prototype._renderHtml.call(this);
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                this.collection.fetch(this.opts);
            }, this);
        }

        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})