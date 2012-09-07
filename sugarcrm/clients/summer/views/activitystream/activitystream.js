({
    events:{
        'click .reply': 'showAddComment',
        'click .postReply': 'addComment',
        'click .addPost': 'addPost',
        'click .more': 'showAllComments',
        'click .filterAll': 'showAllActivities',
        'click .filterMyActivities': 'showMyActivities',
        'click .filterFavorites': 'showFavoritesActivities',
        'dragenter .sayit': 'expandNewPost',
        'dragover .sayit': 'dragoverNewPost',
        'dragleave .sayit': 'shrinkNewPost',
        'drop .sayit': 'dropAttachment',
    },

    initialize: function(options) {
    	this.opts = { params: {}};
    	this.collection = {};
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
        
    	this.collection['oauth_token'] = App.api.getOAuthToken();

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
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
            myPostId = this.$(event.currentTarget).data('id'),
            attachments = [];

        this.$(event.currentTarget).siblings('.activitystream-pending-attachment').each(function(index, el) {
            var id = $(el).attr('id');
            attachments.push({
                "name": $('#' + id + '_name', el).val(),
                "data": $('#' + id + '_data', el).val()
            });
        });

        this.app.api.call('create', this.app.api.buildURL('ActivityStream/ActivityStream/' + myPostId), {'value': myPostContents}, {success: function(post_id) {
            self.$(event.currentTarget).siblings('.activitystream-pending-attachment').each(function(index, el) {
                var id = $(el).attr('id');
                var seed = self.app.data.createBean('Notes', {
                    'parent_id': post_id,
                    'parent_type': 'ActivityStream'
                });
                seed.save({}, {
                    success: function(model) {
                        var data = new FormData();
                        data.append("filename", App.drag_drop[id]);

                        var url = App.api.buildURL("Notes/" + model.get("id") + "/file/filename");
                        url += "?oauth_token="+App.api.getOAuthToken();

                        $.ajax({
                            url: url,
                            type: "POST",
                            data: data,
                            processData: false,
                            contentType: false,
                            success: function() {
                                delete App.drag_drop[id];
                            }
                        });
                    }
                });
            });
            self.collection.fetch(self.opts)
        }});
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

        this.app.api.call('create', this.app.api.buildURL(myPostUrl), {'value': myPostContents}, {success: function(post_id) {
            myPost.find('.activitystream-pending-attachment').each(function(index, el) {
                var id = $(el).attr('id');
                var seed = self.app.data.createBean('Notes', {
                    'parent_id': post_id,
                    'parent_type': 'ActivityStream'
                });
                seed.save({}, {
                    success: function(model) {
                        var data = new FormData();
                        data.append("filename", App.drag_drop[id]);

                        var url = App.api.buildURL("Notes/" + model.get("id") + "/file/filename");
                        url += "?oauth_token="+App.api.getOAuthToken();

                        $.ajax({
                            url: url,
                            type: "POST",
                            data: data,
                            processData: false,
                            contentType: false,
                            success: function() {
                                delete App.drag_drop[id];
                            }
                        });
                    }
                });
            });
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

    expandNewPost: function(event) {
        this.$(event.currentTarget).attr("placeholder", "Drop a file to attach it to the comment.").addClass("dragdrop");
        return false;
    },

    dragoverNewPost: function(event) {
        return false;
    },

    shrinkNewPost: function(event) {
        event.stopPropagation();
        event.preventDefault();
        this.$(event.currentTarget).attr("placeholder", "Type your post").removeClass("dragdrop")
        return false;
    },

    dropAttachment: function(event) {
        event.stopPropagation();
        event.preventDefault();
        this.shrinkNewPost(event);
        $.each(event.dataTransfer.files, function(i, file) {
            var fileReader = new FileReader();

            // Set up the callback for the FileReader.
            fileReader.onload = (function(file) {
                return function(e) {
                    var sizes = ['B', 'KB', 'MB', 'GB'];
                    var size_index = 0;
                    var size = file.size;
                    while(size > 1024 && size_index < sizes.length - 1) {
                        size_index++;
                        size /= 1024;
                    }
                    size = Math.round(size);
                    var unique = _.uniqueId("activitystream_attachment");
                    App.drag_drop = App.drag_drop || {};
                    App.drag_drop[unique] = file;
                    var container = $("<div class='activitystream-pending-attachment' id='" + unique + "'></div>");
                    $('<a class="close">&times;</a>').on('click', function(e) {
                        $(this).parent().remove();
                        delete App.drag_drop[container.attr("id")];
                    }).appendTo(container);
                    container.append(file.name + " (" + size + " " + sizes[size_index] + ")");
                    if(file.type.indexOf("image/") !== -1) {
                        container.append("<img style='display:block;' src='" + e.target.result + "' />");
                    } else {
                        container.append("<div>No preview available</div>");
                    }
                    $(event.currentTarget).after(container);
                }
            })(file);

            fileReader.readAsDataURL(file);
        });
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
