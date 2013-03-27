({
    events: {
        'change div[data-placeholder]': 'checkPlaceholder',
        'keydown div[data-placeholder]': 'checkPlaceholder',
        'keypress div[data-placeholder]': 'checkPlaceholder',
        'input div[data-placeholder]': 'checkPlaceholder',
        'click .reply': 'showAddComment',
        'click .reply-btn': 'addComment',
        'click .deleteRecord': 'deleteRecord',
        'click .preview': 'previewRecord',
        'click .comment': 'toggleReplyBar',
        'click .more': 'fetchComments',
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    tagName: "li",
    className: "activitystream-posts-comments-container",
    plugins: ['timeago', 'file_dragoff', 'taggable'],

    initialize: function(options) {
        _.bindAll(this);
        this.opts = {params: {}};

        app.view.View.prototype.initialize.call(this, options);

        var lastComment = this.model.get("last_comment");
        this.commentsCollection = app.data.createRelatedCollection(this.model, "comments");

        if(lastComment && !_.isUndefined(lastComment.id)) {
            this.commentsCollection.reset([lastComment]);
        }

        this.model.set("comments", this.commentsCollection);

        // If comment_count is 0, we don't want to decrement the count by 1 since -1 is truthy.
        var count = parseInt(this.model.get('comment_count'), 10);
        this.model.set("remaining_comments", 0);
        this.more_tpl = "TPL_MORE_COMMENT";
        if(count) {
            this.model.set("remaining_comments", count - 1);

            // Pluralize the comment count label
            if (count > 2) {
                this.more_tpl += "S";
            }
        }

        this.tpl = "TPL_ACTIVITY_" + this.model.get('activity_type').toUpperCase();
        var data;
        if(this.model.get('activity_type') === "update") {
            var updateTpl = Handlebars.compile(app.lang.get('TPL_ACTIVITY_UPDATE_FIELD', 'Activities')),
                parentType = this.model.get("parent_type"),
                fields = app.metadata.getModule(parentType).fields;

            data = this.model.get('data');

            data.updateStr = _.reduce(data.changes, function(memo, changeObj) {
                changeObj.field_label = app.lang.get(fields[changeObj.field_name].vname, parentType);

                if(memo) {
                    return updateTpl(changeObj) + ', ' + memo;
                }
                return updateTpl(changeObj);
            }, '');

            this.model.set('data', data);
        } else if (this.model.get('activity_type') === 'attach') {
            data = this.model.get('data');
            var url = app.api.buildFileURL({
                module: 'Notes',
                id: data.noteId,
                field: 'filename'
            });
            data.url = url;
            this.$el.data(data);
            this.model.set('data', data);
            this.model.set('parent_type', 'Files');
        }
    },

    fetchComments: function() {
        var self = this;
        this.commentsCollection.fetch({
            //Don't show alerts for this request
            showAlerts: false,
            relate: true,
            success: function(collection) {
                self.model.set("remaining_comments", 0);
                self.render();
            }
        });
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
            parentId = this.model.id,
            $el = this.$('div.reply'),
            payload = {
                parent_id: parentId,
                data: {}
        };

        payload.data.value = this.getText($el);
        if (this.getTags) {
            payload.data.tags = this.getTags($el);
        }

        var bean = app.data.createRelatedBean(this.model, null, 'comments');
        bean.save(payload, {
            //Show alerts for this request
            showAlerts: true,
            relate: true,
            success: function(model) {
                $el.html('').trigger('change');
                self.layout.prependPost(self.model);
                self.commentsCollection.add(model).trigger('reset');
                self.toggleReplyBar();
            }
        });
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
            id = data.id;

        // If module/id data attributes don't exist, this user
        // doesn't have access to that record due to team security.
        if (module && id) {
            var model = app.data.createBean(module),
                collection = this.context.get("collection");

            model.set("id", id);
            app.events.trigger("preview:render", model, collection, true, this.cid);
        }

        event.preventDefault();
    },

    _renderHtml: function(model) {
        this.processAvatars();

        // Save state of the reply bar before rendering
        var isReplyBarOpen = this.$(".comment").hasClass("active") && this.$(".comment").is(":visible"),
            replyVal = this.$(".reply").html();

        app.view.View.prototype._renderHtml.call(this);

        // If the reply bar was previously open, keep it open (render hides it by default)
        if(isReplyBarOpen) {
            this.toggleReplyBar();
            this.$(".reply").html(replyVal);
        }
    },

    processAvatars: function() {
        var comments = this.model.get("comments");

        if(this.model.get("activity_type") === "post" && !this.model.get("picture_url")) {
            var picture = (this.model.get("picture")) ? app.api.buildFileURL({
                module: "Users",
                id: this.model.get("created_by"),
                field: "picture"
            }) : app.config.siteUrl + "/styleguide/assets/img/profile.png";
            this.model.set("picture_url", picture);
        }

        if(comments) {
            _.each(comments.models, function(commentsModel) {
                commentsModel.set("picture_url" , app.config.siteUrl + "/styleguide/assets/img/profile.png");
            });
        }
    },

    toggleReplyBar: function() {
        this.$(".comment").toggleClass("active");
        this.$(".acomment").toggleClass("hide");
    },

    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
    },

    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
    },

    getText: function($el) {
        return $el.contents().html();
    },

    getTagList: function() {
        var tagList = this.model.get('data').tags || [];
        var childTagLists = this.commentsCollection.map(function(comment) {
            return comment.get('data').tags || [];
        });
        tagList = _.uniq(_.reduce(childTagLists, function(memo, el) {
            return memo.concat(el);
        }, tagList));

        return tagList;
    },

    checkPlaceholder: function(e) {
        var el = e.currentTarget;
        if (el.textContent) {
            el.dataset.hidePlaceholder = true;
        } else {
            delete el.dataset.hidePlaceholder;
        }
    },

    /**
     * Data change event.
     */
    bindDataChange: function() {
        if (this.commentsCollection) {
            this.commentsCollection.on("reset", this.render, this);
            this.commentsCollection.on("add", function() {
                this.model.set('comment_count', this.model.get('comment_count') + 1);
            }, this);
        }
    }
})
