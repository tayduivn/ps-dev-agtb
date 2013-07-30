({
    events: {
        'change div[data-placeholder]': 'checkPlaceholder',
        'keydown div[data-placeholder]': 'checkPlaceholder',
        'keypress div[data-placeholder]': 'checkPlaceholder',
        'input div[data-placeholder]': 'checkPlaceholder',
        'click .reply': 'showAddComment',
        'click .reply-btn': 'addComment',
        'click .preview-btn:not(.disabled)': 'previewRecord',
        'click .comment-btn': 'toggleReplyBar',
        'click .more': 'fetchComments',
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    tagName: "li",
    className: "activitystream-posts-comments-container",
    plugins: ['timeago', 'file_dragoff', 'taggable'],
    cacheNamePrefix: "user:avatars:",
    cacheNameExpire: ":expiry",
    expiryTime: 36000000,   //1 hour in milliseconds

    initialize: function(options) {
        this.opts = {params: {}};
        this.readonly = !!options.readonly;

        app.view.View.prototype.initialize.call(this, options);

        var lastComment = this.model.get("last_comment");
        this.commentsCollection = app.data.createRelatedCollection(this.model, "comments");

        if(lastComment && !_.isUndefined(lastComment.id)) {
            this.commentsCollection.reset([lastComment]);
        }

        this.model.set("comments", this.commentsCollection);

        // If comment_count is 0, we don't want to decrement the count by 1 since -1 is truthy.
        var count = parseInt(this.model.get('comment_count'), 10);
        this.remaining_comments = 0;
        this.more_tpl = "TPL_MORE_COMMENT";
        if(count) {
            this.remaining_comments = count - 1;

            // Pluralize the comment count label
            if (count > 2) {
                this.more_tpl += "S";
            }
        }

        this.preview = this.getPreviewData();
        var data = this.model.get('data');
        var activity_type = this.model.get('activity_type');
        this.tpl = "TPL_ACTIVITY_" + activity_type.toUpperCase();

        switch(activity_type) {
            case 'post':
                if (!data.value) {
                    this.tpl = null;
                }
                break;
            case 'update':
                var updateTpl = Handlebars.compile(app.lang.get('TPL_ACTIVITY_UPDATE_FIELD', 'Activities')),
                    parentType = this.model.get("parent_type"),
                    fields = app.metadata.getModule(parentType).fields;

                data.updateStr = _.reduce(data.changes, function(memo, changeObj) {
                    changeObj.field_label = app.lang.get(fields[changeObj.field_name].vname, parentType);

                    if(memo) {
                        return updateTpl(changeObj) + ', ' + memo;
                    }
                    return updateTpl(changeObj);
                }, '');

                this.model.set('data', data);
                break;

            case 'attach':
                var url = app.api.buildFileURL({
                    module: 'Notes',
                    id: data.noteId,
                    field: 'filename'
                });

                if (data.mimetype && data.mimetype.indexOf("image/") === 0) {
                    data.embed = {
                        type: "image",
                        src: url
                    };
                }

                data.url = url;
                this.$el.data(data);
                this.model.set('data', data);
                this.model.set('parent_type', 'Files');
                break;
        }

        this.processEmbed();

        // Resize video when the browser window is resized
        this.resizeVideo = _.bind(_.throttle(this.resizeVideo, 500), this);
        $(window).on('resize.' + this.cid, this.resizeVideo);

        // specify the record that the tags are associated with
        this.setTaggableRecord(this.model.get('parent_type'), this.model.get('parent_id'));
    },

    /**
     * Determine if embeded links exist
     */
    processEmbed: function() {
        var data = this.model.get('data'),
            embedTpl, typeParts, type;

        if (_.isObject(data.embed) && data.embed.type) {
            typeParts = data.embed.type.split('.');
            type = typeParts.shift();

            _.each(typeParts, function(part) {
                type = type + part.charAt(0).toUpperCase() + part.substr(1);
            });

            embedTpl = app.template.get(this.name + '.' + type + 'Embed');
            this.embed = embedTpl ? embedTpl(data.embed) : "No embed partial found for " + data.embed.type;
        }
    },

    fetchComments: function() {
        var self = this;
        this.commentsCollection.fetch({
            //Don't show alerts for this request
            showAlerts: false,
            relate: true,
            success: function(collection) {
                self.remaining_comments = 0;
                self.render();
            }
        });
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
    addComment: function (event) {
        var self = this,
            parentId = this.model.id,
            payload = {
                parent_id: parentId,
                data: {}
            },
            bean;

        payload.data = this.getComment();

        if (payload.data.value && (payload.data.value.length > 0)) {
            bean = app.data.createRelatedBean(this.model, null, 'comments');
            bean.save(payload, {
                relate: true,
                success: _.bind(self.addCommentCallback, self)
            });
        }
    },

    /**
     * Callback for rendering a newly added comment into the activity stream view
     * @param  {Object} model
     */
    addCommentCallback: function (model) {
        var template, data;

        this.$('div.reply').empty().trigger('change');
        this.commentsCollection.add(model);
        this.toggleReplyBar();

        template = app.template.getView('activitystream.comment');

        data = model.get('data');
        data.value = this.formatTags(data.value);

        this.processAvatars();
        this.$('.comments').prepend(template(model.attributes));
        if ($.fn.timeago) {
            this.$("span.relativetime").timeago({
                logger: SUGAR.App.logger,
                date: SUGAR.App.date,
                lang: SUGAR.App.lang,
                template: SUGAR.App.template
            });
        }
        this.trigger('activitystream:post:prepend', this.model);
    },

    /**
     * Handler for previewing a record listed on the activity stream.
     * @param  {Event} event
     */
    previewRecord: function(event) {
        var el = this.$(event.currentTarget),
            data = el.data(),
            module = data.module,
            id = data.id;

        // If module/id data attributes don't exist, this user
        // doesn't have access to that record due to team security.
        if (module && id) {
            var model = app.data.createBean(module),
                collection = this.context.get("collection");

            model.set("id", id);
            app.events.trigger("preview:module:update", this.context.get("module"));
            app.events.trigger("preview:render", model, collection, true, this.cid);
        }

        event.preventDefault();
    },

    _renderHtml: function(model) {
        this.processAvatars();
        this.formatAllTags();

        // Save state of the reply bar before rendering
        var isReplyBarOpen = this.$(".comment-btn").hasClass("active") && this.$(".comment-btn").is(":visible"),
            replyVal = this.$(".reply").html();

        app.view.View.prototype._renderHtml.call(this);
        this.resizeVideo();

        // If the reply bar was previously open, keep it open (render hides it by default)
        if(isReplyBarOpen) {
            this.toggleReplyBar();
            this.$(".reply").html(replyVal);
        }
    },

    /**
     * Format tags in post and comments.
     */
    formatAllTags: function() {
        var post = this.model.get('data');
        if (post) {
            post.value = this.formatTags(post.value);
        }

        this.commentsCollection.each(function(model) {
            var data = model.get('data');
            data.value = this.formatTags(data.value);
        }, this);
    },

    /**
     * Resize the iframe that embeds video
     */
    resizeVideo: function() {
        var data = this.model.get('data'),
            $embed = this.$('.embed'),
            $iframe, iframeWidth, iframeHeight;

        if (data.embed && data.embed.type === 'video') {
            $iframe = $embed.find('iframe');
            if ($iframe.length > 0) {
                iframeWidth = Math.min($embed.width(), 480);
                iframeHeight = parseInt($iframe.prop('height'), 10) * (iframeWidth / parseInt($iframe.prop('width'), 10));

                $iframe.prop({
                    width: iframeWidth,
                    height: iframeHeight
                });
            }
        }
    },

    /**
     * Sets the profile picture for activities based on the created by user.
     */
    processAvatars: function () {
        var comments = this.model.get('comments'),
            postPictureUrl;

        if (this.model.get('activity_type') === 'post' && !this.model.get('picture_url')) {
            postPictureUrl = this.getAvatarUrlForUser(this.model, 'post');
            this.model.set('picture_url', postPictureUrl);
        }

        if (comments) {
            comments.each(function (comment) {
                var commentPictureUrl = this.getAvatarUrlForUser(comment, 'comment');
                comment.set('picture_url', commentPictureUrl);
            }, this);
        }
    },

    /**
     * Builds and returns the url for the user's profile picture based on fetching from cache
     * @param model
     * @param activityType
     * @returns string
     */
    getAvatarUrlForUser: function (model, activityType){
        var createdBy = model.get('created_by'),
            isCached, pictureUrl;

        isCached = this.fetchAndCacheAvatar(model, activityType);

        pictureUrl = isCached ? app.api.buildFileURL({
            module: 'Users',
            id: createdBy,
            field: 'picture'
        }) : '';

        return pictureUrl;
    },

    /**
     * Retrieves a user and caches the results of whether the user has a profile picture.
     * Replaces the default icon of the comment box with an image tag of the profile picture.
     * @param model
     * @param activityType
     * @returns {boolean}
     */
    fetchAndCacheAvatar: function (model, activityType) {
        var self = this,
            createdBy = model.get('created_by'),
            cached = app.cache.get(this.cacheNamePrefix + createdBy),
            cachedTTL = app.cache.get(this.cacheNamePrefix + createdBy + self.expiryTime),
            isCached = true;

        if ((_.isUndefined(cached) || cachedTTL < $.now())) {
            var user = app.data.createBean('Users', {id: createdBy});
            user.fetch({
                fields: ["picture"],
                success: function () {
                    app.cache.set(self.cacheNamePrefix + createdBy, !_.isEmpty(user.get('picture')));
                    app.cache.set(self.cacheNamePrefix + createdBy + self.cacheNameExpire, $.now() + self.expiryTime);

                    var pictureUrl = app.api.buildFileURL({
                        module: 'Users',
                        id: createdBy,
                        field: 'picture'
                    });

                   //Replace the activity image with the users profile picture
                   self.$('#avatar-' + activityType + '-' + model.get('id')).html("<img src='" + pictureUrl + "' alt='" + model.get('created_by_name') + "'>");

                },
                error: function () {
                    app.cache.set(self.cacheNamePrefix + createdBy, false);
                    app.cache.set(self.cacheNamePrefix + createdBy + self.cacheNameExpire, $.now() + self.expiryTime);
                }
            });

            isCached = false;
        }

        return isCached;
    },

    toggleReplyBar: function() {
        this.$(".comment-btn").toggleClass("active");
        this.$(".reply-area").toggleClass("hide");
    },

    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
    },

    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
    },

    /**
     * Retrieve comment entered inside content editable and translate any tags into text format
     * so that it can be saved in the database as JSON string.
     *
     * @returns {String}
     */
    getComment: function() {
        return this.unformatTags(this.$('div.reply'));
    },

    /**
     * Determine the status and label for the preview button
     *
     * @returns {object} preview object
     */
    getPreviewData: function () {
        var parentModel,
            preview = {
                enabled: true,
                label: 'LBL_PREVIEW'
            };

        if (this.model.get("activity_type") === 'attach') { //no preview for attachments
            preview.enabled = false;
            preview.label = 'LBL_PREVIEW_DISABLED_ATTACHMENT';
        } else if (_.isEmpty(this.model.get('parent_id')) || _.isEmpty(this.model.get('parent_type'))) {  //no related record
            preview.enabled = false;
            preview.label = 'LBL_PREVIEW_DISABLED_NO_RECORD';
        } else if (!app.acl.hasAccess("view", this.model.get('parent_type'))) { //no access to related record
            preview.enabled = false;
            preview.label = 'LBL_PREVIEW_DISABLED_NO_ACCESS';
        } else if (this.model.get('preview_enabled') === false) { //deleted or no team access to related record
            preview.enabled = false;
            preview.label = this.model.get('preview_disabled_reason');
        } else {
            parentModel = this._getParentModel('record', this.context);
            if (parentModel && parentModel.id === this.model.get('parent_id')) { //same record as context
                preview.enabled = false;
                preview.label = 'LBL_PREVIEW_DISABLED_SAME_RECORD';
            }
        }

        return preview;
    },

    /**
     * Traverse up the context hierarchy and look for given layout, retrieve the model from the layout's context
     *
     * @param layoutName to look for up the context hierarchy
     * @param context start of context hierarchy
     * @returns {*}
     * @private
     */
    _getParentModel: function(layoutName, context) {
        if (context) {
            if (context.get('layout') === layoutName) {
                return context.get('model');
            } else {
                return this._getParentModel(layoutName, context.parent);
            }
        } else {
            return null;
        }
    },

    checkPlaceholder: function(e) {
        // We can't use any of the jQuery methods or use the dataset property to
        // set this attribute because they don't seem to work in IE 10. Dataset
        // isn't supported in IE 10 at all.
        var el = e.currentTarget;
        if (el.textContent) {
            el.setAttribute('data-hide-placeholder', 'true');
        } else {
            el.removeAttribute('data-hide-placeholder');
        }
    },

    /**
     * Data change event.
     */
    bindDataChange: function () {
        if (this.commentsCollection) {
            this.commentsCollection.on("add", function () {
                this.model.set('comment_count', this.model.get('comment_count') + 1);
            }, this);
        }
        app.view.View.prototype.bindDataChange.call(this);
    },

    unbindData: function() {
        if (this.commentsCollection) {
            this.commentsCollection.off();
        }
        app.view.View.prototype.unbindData.call(this);
    },

    _dispose: function() {
        $(window).off('resize.' + this.cid);
        app.view.View.prototype._dispose.call(this);
        this.commentsCollection = null;
        this.opts = null;
    }
})
