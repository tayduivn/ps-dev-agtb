({
    events: {
        'click .reply': 'showAddComment',
        'click .reply-btn': 'addComment',
        'click .deleteRecord': 'deleteRecord',
        'click [name=show_more_button]': 'showMoreRecords',
        'click .preview-stream': 'previewRecord',
        'click .comment': 'toggleReplyBar',
        'click .more': 'fetchComments',
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    tagName: "li",
    className: "activitystream-posts-comments-container",
    plugins: ['timeago'],

    initialize: function(options) {
        var self = this;

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

        if(this.model.get('activity_type') === "update") {
            var updateTpl = Handlebars.compile(app.lang.get('TPL_ACTIVITY_UPDATE_FIELD', 'Activities')),
                parentType = this.model.get("parent_type"),
                fields = app.metadata.getModule(parentType).fields,
                data = this.model.get('data');

            data.updateStr = _.reduce(data.changes, function(memo, changeObj) {
                changeObj.field_label = app.lang.get(fields[changeObj.field_name].vname, parentType);

                if(memo) {
                    return updateTpl(changeObj) + ', ' + memo;
                }
                return updateTpl(changeObj);
            }, '');

            this.model.set('data', data);
        }
    },

    fetchComments: function() {
        var self = this;
        this.commentsCollection.fetch({relate: true, success: function(collection) {
            self.model.set("remaining_comments", 0);
            self.render();
        }});
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
        // TODO: Change to using a content-editable box instead of input tag.
        var self = this,
            parentId = this.model.id,
            $input = this.$('input.reply'),
            attachments = this.$('.activitystream-pending-attachment'),
            payload = {
                parent_id: parentId,
                data: {
                    value: $input.val()
                }
        };

        var bean = app.data.createBean('Comments');
        bean.save(payload, {
            success: function(model) {
                $input.val('');
                self.layout.prependPost(self.model);
                self.commentsCollection.add(model).trigger('reset');
                self.toggleReplyBar();
                // We need to add any attachments we may have over here.
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

    _renderHtml: function(model) {
        this.processAvatars();

        // Save state of the reply bar before rendering
        var isReplyBarOpen = this.$(".comment").hasClass("active") && this.$(".comment").is(":visible"),
            replyVal = this.$(".reply").val();

        app.view.View.prototype._renderHtml.call(this);

        // If the reply bar was previously open, keep it open (render hides it by default)
        if(isReplyBarOpen) {
            this.toggleReplyBar();
            this.$(".reply").val(replyVal);
        }
    },

    processAvatars: function() {
        var comments = this.model.get("comments");

        if(this.model.get("activity_type") === "post") {
            // TODO: Figure out a way to fall back to generic avatar if the user hasn't uploaded an avatar
            this.model.set("picture_url" , app.config.siteUrl + "/styleguide/assets/img/profile.png");
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
