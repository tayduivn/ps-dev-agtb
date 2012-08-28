({
    events:{
        'click .reply': 'showAddComment',
        'click .postReply': 'addComment',
        'click .post': 'showAddPost',
        'click .addPost': 'addPost',
        'click .more': 'showAllComments'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

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
        $(event.currentTarget).closest('li').hide();
        $(event.currentTarget).closest('ul').find('div.extend').show();
        $(event.currentTarget).closest('ul').closest('li').find('.activitystream-comment').show();
    },

    showAddComment: function(event) {
        $(event.currentTarget).closest('li').find('.activitystream-comment').show();
        $(event.currentTarget).closest('li').find('.activitystream-comment').find('.sayit').focus();
    },

    addComment: function(event) {
        var self = this;
        var myPost = $(event.currentTarget).closest('li');
        var myPostContents = myPost.find('input.sayit')[0].value;
        var myPostId = $(event.currentTarget).data('id');
        
        this.app.api.call('create',this.app.api.buildURL('ActivityStream/ActivityStream/'+myPostId),{'value':myPostContents},{success:function(){self.collection.fetch(self.opts)}});
    },
    
    showAddPost: function(event) {
        this.$(".activitystream-post").show();
    },

    addPost: function(event) {
        var self = this;
        var myPost = this.$(".activitystream-post");
        var myPostContents = myPost.find('input.sayit')[0].value;
        var myPostId = this.context.get("modelId");
        var myPostModule = this.module;

        if(myPostModule == "ActivityStream") {
        	myPostModule = '';	
        }
        this.app.api.call('create',this.app.api.buildURL('ActivityStream'),{'module':myPostModule,'id':myPostId,'value':myPostContents},{success:function(){self.collection.fetch(self.opts)}});
    },

    _renderHtml: function() {
        for (var i=0; i<this.collection.models.length; i++) {
            if (this.collection.models[i].attributes.comments.length > 2) {
                this.collection.models[i].attributes.comments[0]['_starthidden'] = true;
                this.collection.models[i].attributes.comments[this.collection.models[i].attributes.comments.length-3]['_stophidden'] = true;
            }
        }

        return app.view.View.prototype._renderHtml.call(this);
    },

    bindDataChange: function() {
        var self = this;
        if (self.model) {
            self.model.on("change", function() {
                self.render();
            }, self);
        }

        if (self.collection) {
            self.collection.on("reset", self.render, self);
        }
    }
})