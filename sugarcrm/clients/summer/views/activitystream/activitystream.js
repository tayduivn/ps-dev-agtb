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
        'dragstart .activitystream-attachment': 'saveAttachment',
        'click .deleteRecord': 'deleteRecord',
        'mouseenter .hasDeleteButton': 'showDeleteButton',
        'mouseleave .hasDeleteButton': 'hideDeleteButton',
        'click [name=show_more_button]': 'showMoreRecords'
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

        // There maybe better way to make the following data available in hbt
        this.collection['oauth_token'] = App.api.getOAuthToken();
        this.collection['user_id'] = app.user.get('id');
        this.collection['full_name'] = app.user.get('full_name');
        var picture = app.user.get('picture');
        this.collection['picture_url'] = (picture) ? app.api.buildFileURL({
            module: 'Users',
            id: app.user.get('id'),
            field: 'picture'
        }) : "../clients/summer/views/imagesearch/anonymous.jpg";

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
    },

    showMoreRecords: function(event) {
        var self = this, options = {};
        app.alert.show('show_more_records', {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});
        options.params = this.opts.params;
        options.params.offset = this.collection.next_offset;
        options.params.limit = "";// use default
        // Indicates records will be added to those already loaded in to view
        options.add = true;

        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };
        this.collection.paginate(options);
    },

    showAllComments: function(event) {
        event.preventDefault();
        this.$(event.currentTarget).closest('li').hide();
        this.$(event.currentTarget).closest('ul').find('div.extend').show();
        this.$(event.currentTarget).closest('ul').closest('li').find('.activitystream-comment').show();
    },

    showAddComment: function(event) {
        event.preventDefault();
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
            var pending_attachments = self.$(event.currentTarget).siblings('.activitystream-pending-attachment');
            pending_attachments.each(function(index, el) {
                var id = $(el).attr('id');
                var seed = self.app.data.createBean('Notes', {
                    'parent_id': post_id,
                    'parent_type': 'ActivityComments'
                });
                var postSave = _.after(pending_attachments.length, function() {
                    self.collection.fetch(self.opts);
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
                                postSave();
                            }
                        });
                    }
                });
            });
            self.collection.fetch(options);
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
                    'parent_type': 'ActivityStream',
                    'team_id': 1
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
                                self.collection.fetch(self.opts);
                            }
                        });
                    }
                });
            });
            self.collection.fetch(self.opts);
        }});
    },

    showDeleteButton: function(event) {
        event.preventDefault();
        this.$(event.currentTarget).closest('li').find('.deleteRecord').css('display', 'block');
    },

    hideDeleteButton: function(event) {
        event.preventDefault();
        this.$(event.currentTarget).closest('li').find('.deleteRecord').hide();
    },

    deleteRecord: function(event) {
        var self = this,
        recordId = this.$(event.currentTarget).data('id'),
        recordModule = this.$(event.currentTarget).data('module'),
        myPostUrl = 'ActivityStream/'+recordModule+'/'+recordId;
        this.app.api.call('delete', this.app.api.buildURL(myPostUrl), {}, {success: function() {
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

    saveAttachment: function(event) {
        // The following is only true for Chrome.
        if(event.dataTransfer && event.dataTransfer.constructor == Clipboard &&
            event.dataTransfer.setData('DownloadURL', 'http://www.sugarcrm.com')) {
            var el = $(event.currentTarget),
                mime = el.data("mime"),
                name = el.data("filename"),
                file = el.data("url"),
                origin = document.location.origin,
                path = [];

            path = _.initial(document.location.pathname.split('/'));
            path = path.concat(file.split('/'));

            // Resolve .. and . in paths. Chrome doesn't do it for us.
            for(var i = 0; i < path.length; i++) {
                if(".." == path[i+1]) {
                    delete path[i+1];
                    delete path[i];
                    i--;
                }
                if("." == path[i]) {
                    delete path[i];
                    i--;
                }
            }
            path = _.compact(path);
            event.dataTransfer.setData("DownloadURL", mime+":"+name+":"+origin+"/"+path.join('/'));
        }
    },

    _renderHtml: function() {
        _.each(this.collection.models, function(model) {
            var comments = model.get("comments");
            if (comments.length > 2) {
                comments[0]['_starthidden'] = true;
                comments[comments.length - 3]['_stophidden'] = true;
            }
            _.each(comments, function(comment) {
                _.each(comment.notes, function(note) {
                    if(note.file_mime_type) {
                        note.url = App.api.buildURL("Notes/" + note.id + "/file/filename?oauth_token="+App.api.getOAuthToken());
                        note.image = (note.file_mime_type.indexOf("image") !== -1);
                    }
                });
            });

            _.each(model.get("notes"), function(note) {
                if(note.file_mime_type) {
                    note.url = App.api.buildURL("Notes/" + note.id + "/file/filename?oauth_token="+App.api.getOAuthToken());
                    note.image = (note.file_mime_type.indexOf("image") !== -1);
                }
            });

        }, this);

        // Sets correct offset and limit for future fetch if we are 'showing more'
        this.opts.params.offset = 0;
        if(this.collection.models.length > 0) {
            this.opts.params.limit = this.collection.models.length;
            this.opts.params.max_num = this.collection.models.length;
        }

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
