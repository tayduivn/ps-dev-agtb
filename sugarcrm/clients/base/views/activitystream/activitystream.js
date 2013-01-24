({
    events: {
        'click .reply': 'showAddComment',
        'click .postReply': 'addComment',
        'click .addPost': 'addPost',
        'click .more': 'showAllComments',
        'dragenter .sayit': 'expandNewPost',
        'dragover .sayit': 'dragoverNewPost',
        'dragleave .sayit': 'shrinkNewPost',
        'drop .sayit': 'dropAttachment',
        'dragstart .activitystream-attachment': 'saveAttachment',
        'click .deleteRecord': 'deleteRecord',
        'click [name=show_more_button]': 'showMoreRecords',
        'keyup .sayit': 'getEntities',
        'blur .sayit': 'hideTypeahead',
        'mouseover ul.typeahead.activitystream-tag-dropdown li': 'switchActiveTypeahead',
        'click ul.typeahead.activitystream-tag-dropdown li': 'addTag',
        'click .showAnchor': 'showAnchor',
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
        // Currently the "Home" module is dubbed ActivityStreem
        if (this.module !== "ActivityStream") {
            this.subcontext = this.context.getChildContext({module: "ActivityStream"});
            this.subcontext.prepare();

            this.opts = (this.context.get("modelId")) ? { params: { module: this.module, id: this.context.get("modelId") }} :
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

        this.user_id = app.user.get('id');
        this.full_name = app.user.get('full_name');
        this.picture_url = (app.user.get('picture')) ? app.api.buildFileURL({
            module: 'Users',
            id: app.user.get('id'),
            field: 'picture'
        }) : "../styleguide/assets/img/profile.png";

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
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
     * Helper method for adding a post or a comment. Handles attachments too.
     * @param {string} url         Endpoint for posting message
     * @param {string} contents    Some type of message (may have HTML due to tags)
     * @param {array}  attachments Attachments to save to the post.
     */
    _addPostComment: function(url, contents, attachments) {
        var self = this,
            callback = _.after(1 + attachments.length, function() {
                //self.streamCollection.fetch(self.opts);
            });

        app.api.call('create', url, {'value': contents}, {success: function(post_id) {
            // TODO: Fix this to be less hacky. Perhaps a flag in arguments?
            var parent_type = (url.indexOf("ActivityStream/ActivityStream") === -1)? 'ActivityStream' : 'ActivityComments';

            attachments.each(function(index, el) {
                var id = $(el).attr('id'),
                    seed = app.data.createBean('Notes', {
                        'parent_id': post_id,
                        'parent_type': parent_type,
                        'team_id': 1
                    });

                seed.save({}, {
                    success: function(model) {
                        var data = new FormData(),
                            url = app.api.buildURL("Notes/" + model.get("id") + "/file/filename");

                        data.append("filename", app.drag_drop[id]);
                        url += "?oauth_token=" + app.api.getOAuthToken();

                        $.ajax({
                            url: url,
                            type: "POST",
                            data: data,
                            processData: false,
                            contentType: false,
                            success: function() {
                                delete app.drag_drop[id];
                                callback();
                            }
                        });
                    }
                });
            });
            callback();
        }});
    },

    /**
     * Helper method to convert HTML from tags to a text-based format.
     * @param  {string} postHTML
     * @return {string}
     */
    _processTags: function(postHTML) {
        var contents = '';
        $(postHTML).contents().each(function() {
            if (this.nodeName == "#text") {
                contents += this.data;
            } else if (this.nodeName == "SPAN") {
                var el = $(this);
                var data = el.data();

                // Check if the span is a tag, else append el text to the post's content
                if( data.module && data.id ) {
                    contents += '@[' + data.module + ':' + data.id + ']';
                } else {
                    contents += el.text();
                }
            }
        }).html();
        return contents.replace(/&nbsp;/gi, ' ');
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

        myPostContents = this._processTags(myPost.find('div.sayit'));
        this._addPostComment(myPostUrl, myPostContents, attachments);
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

        if (myPostModule !== "ActivityStream") {
            myPostUrl += '/' + myPostModule;
            if (!_.isUndefined(myPostId)) {
                myPostUrl += '/' + myPostId;
            }
        }

        myPostUrl = app.api.buildURL(myPostUrl);
        myPostContents = this._processTags(myPost.find('div.sayit'));
        this._addPostComment(myPostUrl, myPostContents, attachments);
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
        this.$(event.currentTarget).attr("placeholder", "Type your post").removeClass("dragdrop");
        return false;
    },

    dropAttachment: function(event) {
        event.stopPropagation();
        event.preventDefault();
        this.shrinkNewPost(event);
        _.each(event.dataTransfer.files, function(file, i) {
            var fileReader = new FileReader();
            var self = this;

            // Set up the callback for the FileReader.
            fileReader.onload = (function(file) {
                return function(e) {
                    var container,
                        sizes = ['B', 'KB', 'MB', 'GB'],
                        size_index = 0,
                        size = file.size,
                        unique = _.uniqueId("activitystream_attachment");

                    while (size > 1024 && size_index < sizes.length - 1) {
                        size_index++;
                        size /= 1024;
                    }

                    size = Math.round(size);

                    app.drag_drop = app.drag_drop || {};
                    app.drag_drop[unique] = file;
                    container = $("<div class='activitystream-pending-attachment' id='" + unique + "'></div>");

                    // TODO: Review creation of inline HTML
                    $('<a class="close">&times;</a>').on('click',function(e) {
                        $(this).parent().remove();
                        delete app.drag_drop[container.attr("id")];
                    }).appendTo(container);

                    container.append(file.name + " (" + size + " " + sizes[size_index] + ")");

                    if (file.type.indexOf("image/") !== -1) {
                        container.append("<img style='display:block;' src='" + e.target.result + "' />");
                    } else {
                        container.append("<div>No preview available</div>");
                    }

                    container.appendTo(self.$(event.currentTarget).parent());
                };
            })(file);

            fileReader.readAsDataURL(file);
        }, this);
    },

    /**
     * Handles dragging an attachment off the page.
     * @param  {Event} event
     */
    saveAttachment: function(event) {
        // The following is only true for Chrome.
        if (event.dataTransfer && event.dataTransfer.constructor == Clipboard &&
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
            for (var i = 0; i < path.length; i++) {
                if (".." == path[i + 1]) {
                    delete path[i + 1];
                    delete path[i];
                    i--;
                }
                if ("." == path[i]) {
                    delete path[i];
                    i--;
                }
            }
            path = _.compact(path);
            event.dataTransfer.setData("DownloadURL", mime + ":" + name + ":" + origin + "/" + path.join('/'));
        }
    },

    _getEntities: _.debounce(function(event) {
        var list,
            el = this.$(event.currentTarget),
            word = event.currentTarget.innerText;

        el.parent().find("ul.typeahead.activitystream-tag-dropdown").remove();

        if (word.indexOf("@") === -1) {
            // If there's no @, don't do anything.
            return;
        } else if (word.indexOf("@") === 0) {
            word = _.last(word.split('@'));
        } else {
            // Prevent email addresses from being caught, even though emails
            // can have spaces in them according to the RFCs (3696/5322/6351).
            word = _.last(word.split(' @'));
        }

        // Do initial list filtering.
        list = _.filter(app.entityList, function(entity) {
            return entity.name.toLowerCase().indexOf(word.toLowerCase()) !== -1;
        });

        // Rank the list and trim it to no more than 8 entries.
        list = (function(list, query) {
            var begin = [], caseSensitive = [], caseInsensitive = [], item = list.shift(), i;
            for (i = 0; i < 8 && item; i++) {
                if (item.name.toLowerCase().indexOf(query.toLowerCase()) === 0) {
                    begin.push(item);
                } else if (item.name.indexOf(query) !== -1) {
                    caseSensitive.push(item);
                } else {
                    caseInsensitive.push(item);
                }
                item = list.shift();
            }
            return begin.concat(caseSensitive, caseInsensitive);
        })(list, word);

        var ul = $("<ul/>").addClass('typeahead dropdown-menu activitystream-tag-dropdown');
        var blank_item = '<li><a href="#"></a></li>';
        if (list.length) {
            items = _.map(list, function(item) {
                var i = $(blank_item).data(item);
                var query = word.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
                i.find('a').html(function() {
                    return item.name.replace(new RegExp('(' + query + ')', 'ig'), function($1, match) {
                        return '<strong>' + match + '</strong>';
                    });
                });

                return i[0];
            });

            items[0] = ($(items[0]).addClass('active'))[0];

            ul.css({
                top: el.position().top + el.height(),
                left: el.position().left
            });

            ul.html(items).appendTo(el.parent()).show();
        }
    }, 250),

    getEntities: function(event) {
        var dropdown = this.$("ul.typeahead.activitystream-tag-dropdown"),
            currentTarget = this.$(event.currentTarget);
        // Coerce integer to a boolean.
        var dropdownOpen = !!(dropdown.length);

        if (dropdownOpen) {
            var active = dropdown.find('.active');
            // Enter or tab. Tab doesn't work in some browsers.
            if (event.keyCode == 13 || event.keyCode == 9) {
                event.preventDefault();
                event.stopPropagation();
                dropdown.find('.active').click();
            }
            // Up arrow.
            if (event.keyCode == 38) {
                var prev = active.prev();
                if (!prev.length) {
                    prev = dropdown.find('li').last();
                }
                active.removeClass('active');
                prev.addClass('active');
            }
            // Down arrow.
            if (event.keyCode == 40) {
                var next = active.next();
                if (!next.length) {
                    next = dropdown.find('li').first();
                }
                active.removeClass('active');
                next.addClass('active');
            }
        }

        currentTarget.find('.label').each(function() {
            var el = $(this);
            if (el.data('name') !== el.text()) {
                el.remove();
            }
        });

        // If we're typing text.
        if (event.keyCode > 47) {
            this._getEntities(event);
        }
    },

    hideTypeahead: function() {
        var self = this;
        setTimeout(function() {
            self.$("ul.typeahead.activitystream-tag-dropdown").remove();
        }, 150);
    },

    switchActiveTypeahead: function(event) {
        this.$("ul.typeahead.activitystream-tag-dropdown .active").removeClass('active');
        this.$(event.currentTarget).addClass('active');
    },

    addTag: function(event) {
        var el = this.$(event.currentTarget);
        var body = this.$(el.parents()[1]).find(".sayit");
        var originalChildren = body.clone(true).children();
        var lastIndex = body.html().lastIndexOf("@");
        var data = el.data();

        var tag = $("<span />").addClass("label").addClass("label-" + data.module).html(data.name);
        tag.data("id", data.id).data("module", data.module).data("name", data.name);
        var substring = body.html().substring(0, lastIndex);
        $(body).html(substring).append(tag).append("&nbsp;");

        if($(body).children().length == 1) {
            // Fixes issue where a random font tag appears. ABE-128.
            $(body).prepend("&nbsp;");
        }

        // Since the data is stored as an object, it's not preserved when we add the tag.
        // For this reason, we need to add it again.
        body.children().each(function(i) {
            if (originalChildren[i]) {
                var tagChild = this;
                _($.data(originalChildren[i])).each(function(value, key) {
                    $.data(tagChild, key, value);
                });
            }
        });
        if (document.createRange) {
            var range = document.createRange();
            range.selectNodeContents(body[0]);
            range.collapse(false);
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
        this.hideTypeahead();

        event.stopPropagation();
        event.preventDefault();
    },

    _parseTags: function(text) {
        var pattern = new RegExp(/@\[([\d\w\s-]*):([\d\w\s-]*)\]/g);

        return (!text || text.length === 0) ? text : text.replace(pattern, function(str, module, id) {
            var name = _(app.entityList).find(function(el) {
                return el.id == id;
            }).name || "A record";
            return "<span class='label label-" + module + "'><a href='#" + module + '/' + id + "'>" + name + "</a></span>";
        });
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
        if (this.module === "ActivityStream") {
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
                var url = "../styleguide/assets/img/profile.png";
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
                activity_data.value = self._parseTags(activity_data.value);
                model.set("activity_data", activity_data);
            }

            processPicture(model);

            if (comments.length > 1) {
                comments[1]['_starthidden'] = true;
                comments[comments.length - 1]['_stophidden'] = true;
                comments[comments.length - 1]['_morecomments'] = comments.length - 1;
            }

            _.each(comments, function(comment) {
                comment.value = self._parseTags(comment.value);
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
