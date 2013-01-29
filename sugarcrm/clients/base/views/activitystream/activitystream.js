({
    events: {
        'click .reply': 'showAddComment',
        'click .postReply': 'addComment',
        'click .more': 'showAllComments',
        'click .deleteRecord': 'deleteRecord',
        'click [name=show_more_button]': 'showMoreRecords',
        'click .preview-stream': 'previewRecord'
    },

    initialize: function(options) {
        var self = this;

        _.bindAll(this);
        this.opts = {params: {}};

        app.view.View.prototype.initialize.call(this, options);

        this.layout.off("stream:more:fire", null, this);
        this.layout.on("stream:more:fire", function(collection) {
            app.events.trigger("preview:collection:change", collection);
        }, this);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "Home") {
            this.subcontext = this.context.getChildContext({module: "Home"});
            this.subcontext.prepare();

            this.opts = (this.context.get("model").id) ? { params: { module: this.module, id: this.context.get("model").id }} :
            { params: { module: this.module }};

            this.streamCollection = this.subcontext.get("collection");
        } else {
            this.streamCollection = this.collection;
        }

        if (this.context.get("link")) {
            this.opts.params.link = this.context.get("link");
            this.opts.params.parent_module = this.layout.layout.module;
            this.opts.params.parent_id = this.layout.layout.model.id;
        }

        // By default, show all posts.
        this.opts.params.filter = 'all';
        this.opts.params.offset = 0;
        this.opts.params.limit = 20;
        //this.streamCollection.fetch(this.opts);
    },

    showAnchor: function(event) {
        var myId = this.$(event.currentTarget).data('id');

        event.preventDefault();
        $('html, body').animate({ scrollTop: $('#' + myId).offset().top - 50 }, 'slow');
    },

    showMoreRecords: function() {
        var self = this, options = {};

        app.alert.show('show_more_records', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_LOADING')});

        options.params = this.opts.params;
        options.params.offset = this.streamCollection.next_offset;
        options.params.limit = ""; // use default
        options.add = true; // Indicates records will be added to those already loaded in to view

        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.layout.trigger("stream:more:fire", self.streamCollection, self);
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };

        this.streamCollection.paginate(options);
    },

    showAllComments: function(event) {
        var currentTarget = this.$(event.currentTarget);

        currentTarget.closest('li').hide();
        currentTarget.closest('ul').find('div.extend').show();
        currentTarget.closest('ul').closest('li').find('.activitystream-comment').show();

        event.preventDefault();
    },

    /**
     * Event handler for clicking comment button -- shows a post's comment box.
     * @param  {Event} e
     */
    showAddComment: function(e) {
        var currentTarget = this.$(e.currentTarget);

        currentTarget.closest('li').find('.activitystream-comment').toggle();
        currentTarget.closest('li').find('.activitystream-comment').find('.sayit').focus();

        e.preventDefault();
    },

    /**
     * Creates a new comment on a post.
     * @param {Event} event
     */
    addComment: function(event) {
        var self = this,
            myPost = this.$(event.currentTarget).closest('li'),
            myPostId = this.$(event.currentTarget).data('id'),
            myPostUrl = app.api.buildURL('ActivityStream/ActivityStream/' + myPostId),
            myPostContents,
            attachments = this.$(event.currentTarget).siblings('.activitystream-pending-attachment');

        myPostContents = this.layout._processTags(myPost.find('div.sayit'));
        this.layout._addPostComment(myPostUrl, myPostContents, attachments);
    },

    deleteRecord: function(event) {
        var self = this,
            currentTarget = this.$(event.currentTarget),
            recordId = currentTarget.data('id'),
            recordModule = currentTarget.data('module'),
            myPostUrl = 'ActivityStream/' + recordModule + '/' + recordId;

        app.api.call('delete', app.api.buildURL(myPostUrl), {}, {success: function() {
            // self.streamCollection.fetch(self.opts);
        }});
    },

    /**
     * Handler for previewing a record listed on the activity stream.
     * @param  {Event} event
     */
    previewRecord: function(event) {
        var self = this,
            el = this.$(event.currentTarget),
            data = el.data(),
            module = data.module,
            id = data.id,
            postId = data.postid;

        // If module/id data attributes don't exist, this user
        // doesn't have access to that record due to team security.
        if (module.length && id.length) {
            var model = app.data.createBean(module);

            model.set("id", id);
            model.set("postId", postId);
            model.fetch({
                success: function(model) {
                    model.set("_module", module);
                    app.events.trigger("preview:open");
                    app.events.trigger("preview:render", model, self.streamCollection);
                }
            });
        } else {
            app.alert.show("no_access", {level: "error", title: "Permission Denied",
                messages: "Sorry, you do not have access to preview this specific record.", autoClose: true});
        }
    },

    _focusOnPost: _.once(function() {
        // Only focus on the home page. Change this when we have a home module.
        if (this.module === "Home") {
            _.defer(function() {
                this.$(".activitystream-post .sayit").focus();
            });
        }
    }),

    _renderHtml: function() {
        var self = this,
            processAttachment = function(note, i, all) {
                if (note.file_mime_type) {
                    note.url = app.api.buildFileURL({module: 'Notes', field: 'filename', id: note.id});
                    note.file_type = note.file_mime_type.indexOf("image") !== -1 ? 'image' : (note.file_mime_type.indexOf("pdf") !== -1 ? 'pdf' : 'other');
                    note.newline = (i % 2) == 1 && (i + 1) != all.length; // display two items in each row
                }
            },
            processPicture = function(obj) {
                var isModel = (obj instanceof Backbone.Model);
                var created_by = obj.created_by || obj.get('created_by');
                var url = app.config.siteUrl + "/styleguide/assets/img/profile.png";
                if (obj.created_by_picture || (isModel && obj.get('created_by_picture'))) {
                    url = app.api.buildFileURL({
                        module: 'Users',
                        id: created_by,
                        field: 'picture'
                    });
                }
                if (isModel) {
                    obj.set('created_by_picture_url', url);
                } else {
                    obj.created_by_picture_url = url;
                }
            };

        _.each(this.streamCollection.models, function(model) {
            var activity_data = model.get("activity_data"),
                comments = model.get("comments");

            if (activity_data && activity_data.value) {
                activity_data.value = self.layout._parseTags(activity_data.value);
                model.set("activity_data", activity_data);
            }

            processPicture(model);

            if (comments.length > 1) {
                comments[1]['_starthidden'] = true;
                comments[comments.length - 1]['_stophidden'] = true;
                comments[comments.length - 1]['_morecomments'] = comments.length - 1;
            }

            _.each(comments, function(comment) {
                comment.value = self.layout._parseTags(comment.value);
                processPicture(comment);
                _.each(comment.notes, processAttachment);
            });

            _.each(model.get("notes"), processAttachment);

        }, this);

        // Sets correct offset and limit for future fetch if we are 'showing more'
        this.opts.params.offset = 0;

        if (this.streamCollection.models.length > 0) {
            this.opts.params.limit = this.streamCollection.models.length;
            this.opts.params.max_num = this.streamCollection.models.length;
        }

        // Start the user focused in the activity stream input.
        this._focusOnPost();

        return app.view.View.prototype._renderHtml.call(this);
    },

    /**
     * Data change event.
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                // this.streamCollection.fetch(this.opts);
            }, this);
        }

        if (this.streamCollection) {
            this.streamCollection.on("reset", this.render, this);
        }
    }
})
