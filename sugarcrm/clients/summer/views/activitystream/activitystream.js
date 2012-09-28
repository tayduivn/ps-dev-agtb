({
    events: {
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
        'click [name=show_more_button]': 'showMoreRecords',
        'keyup .sayit': 'getEntities',
        'blur .sayit': 'hideTypeahead',
        'mouseover ul.typeahead.activitystream-tag-dropdown li': 'switchActiveTypeahead',
        'click ul.typeahead.activitystream-tag-dropdown li': 'addTag',
        'click .sayit .label a.close': 'removeTag',
        'click .showAnchor': 'showAnchor',
        'click .icon-eye-open': 'previewRecord',
        'click .toggleView': 'toggleView'
    },

    initialize: function(options) {
        var self = this;
        this.opts = {params: {}};
        this.collection = {};

        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "ActivityStream") {
            if (this.context.get("modelId")) {
                this.opts = { params: { module: this.module, id: this.context.get("modelId") }};
            } else {
                this.opts = { params: { module: this.module }};
            }
        }

        this.collection = app.data.createBeanCollection("ActivityStream");
        // By default, show all posts.
        this.showAllActivities();

        // Fetch taggable entities.
        var url = app.api.buildURL("CustomReport/EntityList");
        if(this.opts.params.module) {
            url += "?module=" + this.opts.params.module;
            if (this.opts.params.id) {
                url += "&id=" + this.opts.params.id;
            }
        }

        app.api.call('GET', url, null, {success: function(o) {
            self.entityList = o;
        }});


        this.user_id = app.user.get('id');
        this.full_name = app.user.get('full_name');
        var picture = app.user.get('picture');
        this.picture_url = (picture) ? app.api.buildFileURL({
            module: 'Users',
            id: app.user.get('id'),
            field: 'picture'
        }) : "../clients/summer/views/imagesearch/anonymous.jpg";

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
    },

    toggleView: function(event) {
        event.preventDefault();
        var view = this.$(event.currentTarget).data('view');
        if(view == 'timeline') {
            $('#activitystream-timeline').show();
            $('#activitystream-calendar').hide();
            if(!this.timelineRendered) {
                this._renderTimeline();
            }
        }
        else if(view == 'calendar') {
            $('#activitystream-calendar').show();
            $('#activitystream-timeline').hide();
            if(!this.calendarRendered) {
                this._renderCalendar();
            }
        }
        else {
            $('#activitystream-timeline').hide();
            $('#activitystream-calendar').hide();
        }
    },

    showAnchor: function(event) {
        event.preventDefault();
        var myId = this.$(event.currentTarget).data('id');
        $('html, body').animate({ scrollTop: $('#'+myId).offset().top - 50 }, 'slow');
    },

    showMoreRecords: function() {
        var self = this, options = {};

        app.alert.show('show_more_records', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_LOADING')});

        options.params = this.opts.params;
        options.params.offset = this.collection.next_offset;
        options.params.limit = ""; // use default
        options.add = true; // Indicates records will be added to those already loaded in to view

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
        this.$(event.currentTarget).closest('li').find('.activitystream-comment').toggle();
        this.$(event.currentTarget).closest('li').find('.activitystream-comment').find('.sayit').focus();
    },

    _addPostComment: function(url, contents, attachments) {
        var self = this;
        app.api.call('create', url, {'value': contents}, {success: function(post_id) {
            attachments.each(function(index, el) {
                var id = $(el).attr('id');
                var seed = app.data.createBean('Notes', {
                    'parent_id': post_id,
                    'parent_type': 'ActivityStream',
                    'team_id': 1
                });
                seed.save({}, {
                    success: function(model) {
                        var data = new FormData();
                        data.append("filename", app.drag_drop[id]);

                        var url = app.api.buildURL("Notes/" + model.get("id") + "/file/filename");
                        url += "?oauth_token="+app.api.getOAuthToken();

                        $.ajax({
                            url: url,
                            type: "POST",
                            data: data,
                            processData: false,
                            contentType: false,
                            success: function() {
                                delete app.drag_drop[id];
                                self.collection.fetch(self.opts);
                            }
                        });
                    }
                });
            });
            self.collection.fetch(self.opts);
        }});
    },

    _processTags: function(postHTML) {
        var contents = '';
        $(postHTML).contents().each(function() {
            if (this.nodeName == "#text") {
                contents += this.data;
            } else if (this.nodeName == "SPAN") {
                var el = $(this);
                el.find('a').remove();
                var data = el.data();
                contents += '@[' + data.module + ':' + data.id + ':' + el.text() + ']';
            }
        }).html();
        return contents.replace(/&nbsp;/gi, ' ');
    },

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
            if (myPostId !== undefined) {
                myPostUrl += '/' + myPostId;
            }
        }

        myPostUrl = this.app.api.buildURL(myPostUrl);
        myPostContents = this._processTags(myPost.find('div.sayit'));
        this._addPostComment(myPostUrl, myPostContents, attachments);
    },

    deleteRecord: function(event) {
        var self = this,
            recordId = this.$(event.currentTarget).data('id'),
            recordModule = this.$(event.currentTarget).data('module'),
            myPostUrl = 'ActivityStream/' + recordModule + '/' + recordId;
        this.app.api.call('delete', this.app.api.buildURL(myPostUrl), {}, {success: function() {
            self.collection.fetch(self.opts);
        }});
    },

    showAllActivities: function() {
        this.opts.params.filter = 'all';
        this.opts.params.offset = 0;
        this.opts.params.limit = 20;
        this.collection.fetch(this.opts);
    },

    showMyActivities: function() {
        this.opts.params.filter = 'myactivities';
        this.opts.params.offset = 0;
        this.collection.fetch(this.opts);
    },

    showFavoritesActivities: function() {
        this.opts.params.filter = 'favorites';
        this.opts.params.offset = 0;
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
        this.$(event.currentTarget).attr("placeholder", "Type your post").removeClass("dragdrop");
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
                    while (size > 1024 && size_index < sizes.length - 1) {
                        size_index++;
                        size /= 1024;
                    }
                    size = Math.round(size);
                    var unique = _.uniqueId("activitystream_attachment");
                    app.drag_drop = app.drag_drop || {};
                    app.drag_drop[unique] = file;
                    var container = $("<div class='activitystream-pending-attachment' id='" + unique + "'></div>");
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
                    $(event.currentTarget).after(container);
                };
            })(file);

            fileReader.readAsDataURL(file);
        });
    },

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
        var el = this.$(event.currentTarget);
        el.parent().find("ul.typeahead.activitystream-tag-dropdown").remove();
        var word = event.currentTarget.innerText;
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
        var list = _.filter(this.entityList, function(entity) {
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

            var pos = $.extend({}, el.offset(), {
                height: el[0].offsetHeight
            });

            ul.html(items).appendTo(el.parent()).show();
        }
    }, 250),

    getEntities: function(event) {
        this._getEntities(event);
    },

    hideTypeahead: function() {
        setTimeout(function() {
            self.$("ul.typeahead.activitystream-tag-dropdown").remove();
        }, 150);
    },

    switchActiveTypeahead: function(event) {
        this.$("ul.typeahead.activitystream-tag-dropdown .active").removeClass('active');
        this.$(event.currentTarget).addClass('active');
    },

    addTag: function(event) {
        event.stopPropagation();
        event.preventDefault();
        var el = $(event.currentTarget);
        var body = $(el.parents()[1]).find(".sayit")[0];
        var lastIndex = body.innerHTML.lastIndexOf("@");
        var data = $(event.currentTarget).data();

        var tag = $("<span />").addClass("label").addClass("label-" + data.module).html(data.name + '<a class="close">×</a>');
        tag.attr("data-id", data.id).attr("data-module", data.module);
        body.innerHTML = body.innerHTML.substring(0, lastIndex) + " " + tag[0].outerHTML + "&nbsp;";
        if (document.createRange) {
            var range = document.createRange();
            range.selectNodeContents(body);
            range.collapse(false);
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
        this.hideTypeahead();
    },

    removeTag: function(event) {
        this.$(event.currentTarget).parent().remove();
    },

    _parseTags: function(text) {
        var pattern = new RegExp(/@\[([\d\w\s-]*):([\d\w\s-]*):([\d\w\s-]*)\]/g);
        return text.replace(pattern, function(str, module, id, text) {
            return "<span class='label label-" + module + "'><a href='#" + module + '/' + id + "'>" + text + "</a></span>";
        });
    },

    _addTimelineEvent: function(model) {
        var events = [], self = this;
        var parseDate = function(datestring) {
            var d = new Date(datestring);
            var s = (d.getMonth()+1)+"/";
            s += d.getDate() + "/";
            s += d.getFullYear() + " ";
            s += d.getHours() + ":";
            s += d.getMinutes() + ":";
            s += d.getSeconds();
            return s;
        };

        _.each(model.get('comments'), function(comment) {
            if(comment.value) {
                var event = {};
                event.tag = "commented";
                event.startDate = parseDate(comment.date_created);
                event.text = event.tag + " by " + comment.created_by_name;
                event.headline = comment.value;
                events.push(event);
            }
            _.each(comment.notes, function(attachment) {
                var event = {};
                event.tag = "attached";
                event.startDate = parseDate(attachment.date_entered);
                event.text = event.tag + " by " + attachment.created_by_name;
                event.headline = attachment.filename;
                events.push(event);
            });
        });

        if(model.get("target_name") || self._parseTags(model.get("activity_data").value)) {
            var event = {};
            event.startDate = parseDate(model.get("date_created"));
            event.text = model.get("activity_type") + " by " + model.get("created_by_name");
            event.headline = model.get("target_name") || self._parseTags(model.get("activity_data").value);
            event.tag = model.get("activity_type");
            event.asset = {
                media: "<img src='"+model.get("created_by_picture_url")+"' />",
                caption: model.get("created_by_name")
            };
            events.push(event);
        }
        _.each(model.get('notes'), function(attachment) {
            var event = {};
            event.tag = "attached";
            event.startDate = parseDate(attachment.date_entered);
            event.text = event.tag + " by " + attachment.created_by_name;
            event.headline = attachment.filename;
            if(attachment.file_type == "image") {
                event.asset = {
                    media: "<img src='"+attachment.url+"' />",
                    caption: attachment.filename
                };
            }
            events.push(event);
        });
        return events;
    },

    _addCalendarMonthEvent: function(models) {
        var events = [], counts = {};
        var getDate = function(datestring) {
            var d = new Date(datestring);
            var s = d.toDateString();
            return s;
        };

        $.each(models, function(index, model) {
            var dateStr = getDate(model.get('date_created'));
            if(typeof counts[dateStr] != 'undefined') {
                counts[dateStr].count += 1;
            }
            else {
                counts[dateStr] = {};
                counts[dateStr].count = 1;
                counts[dateStr].id = model.get('id');
                counts[dateStr].start = new Date(model.get('date_created'));
            }
        });

        $.each(counts, function(dateStr, data) {
            var event = {"allDay":true,"id":data.id};
            event.start = data.start;
            event.title = data.count + ' event(s)';
            events.push(event);
        });

        return events;
    },

    _addCalendarWeekEvent: function(model) {
        var events = [];

        var event = {allDay:false};
        event.id = model.get('id');
        event.start = new Date(model.get("date_created"));
        event.title =  model.get("created_by_name") + " " + model.get("activity_type");
        events.push(event);

        return events;
    },

    _addCalendarDayEvent: function(model) {
        var events = [], activityType = model.get('activity_type');

        var event = {allDay:false};
        event.id = model.get('id');
        event.start = new Date(model.get("date_created"));
        event.title = model.get("created_by_name") + " " + model.get("activity_type") + " ";

        switch (activityType) {
            case "posted":
                event.title += model.get('activity_data').value;
                if(model.get('target_name')) {
                    event.title += "on " + model.get('target_name');
                }
                break;
            case "created":
                event.title += model.get('target_name');
                break;
            case "related":
                event.title += model.get('activity_data').relate_name + " to " + model.get('target_name');
                break;
            case "updated":
                $.each(model.get('activity_data'), function(index, value) {
                    if(index !== 0) {
                        event.title += ', ';
                    }
                    event.title += value.get('field_name');
                });
                event.title += " on "+model.get('target_name');
                break;
            default:
                break;
        }

        events.push(event);
        return events;
    },

    previewRecord: function(event) {
        console.log("Previewing", this.context);
        testing = this;

        var self = this;
        var root = this.$(event.currentTarget).parent().parent().parent();
        var hash = root.find("p a:last").attr("href").replace('#', '');
        var arr = hash.split('/');
        var module = arr[0], id = arr[1];

        // Grab model corresponding to preview icon clicked
        var model = App.data.createBean(module);
        model.set("id", id);
        model.fetch({
            success: function(model) {
                console.log ("Done with model.", model, self.layout.meta, self.context);
                model.set("_module", module);
                // Fire on parent layout .. works nicely for relatively simple page ;=)
                self.context.trigger("togglePreview", model);
            }
        });
    },

    _renderHtml: function() {
        var self = this;
        _.each(this.collection.models, function(model) {
            var activity_data = model.get("activity_data");
            var picture = model.get("created_by_picture");
            var comments = model.get("comments");
            if (activity_data && activity_data.value) {
                activity_data.value = self._parseTags(activity_data.value);
                model.set("activity_data", activity_data);
            }

            model.set("created_by_picture_url", (picture) ? app.api.buildFileURL({
                module: 'Users',
                id: model.get('created_by'),
                field: 'picture'
            }) : "../clients/summer/views/imagesearch/anonymous.jpg");


            if (comments.length > 1) {
                comments[1]['_starthidden'] = true;
                comments[comments.length - 1]['_stophidden'] = true;
                comments[comments.length - 1]['_morecomments'] = comments.length - 1;
            }
            _.each(comments, function(comment) {
                comment.value = self._parseTags(comment.value);

                comment.created_by_picture_url = (comment.created_by_picture) ? app.api.buildFileURL({
                    module: 'Users',
                    id: model.get('created_by'),
                    field: 'picture'
                }) : "../clients/summer/views/imagesearch/anonymous.jpg";

                _.each(comment.notes, function(note, index) {
                    if(note.file_mime_type) {
                        note.url = app.api.buildURL("Notes/" + note.id + "/file/filename?oauth_token="+app.api.getOAuthToken());
                        note.file_type = note.file_mime_type.indexOf("image") !== -1 ? 'image' : (note.file_mime_type.indexOf("pdf") !== -1 ? 'pdf' : 'other');
                        note.newline = (index % 2) == 1 && (index + 1) != model.get("notes").length; // display two items in each row
                    }
                });
            });

            _.each(model.get("notes"), function(note, index) {
                if(note.file_mime_type) {
                    note.url = app.api.buildURL("Notes/" + note.id + "/file/filename?oauth_token="+app.api.getOAuthToken());
                    note.file_type = note.file_mime_type.indexOf("image") !== -1 ? 'image' : (note.file_mime_type.indexOf("pdf") !== -1 ? 'pdf' : 'other');
                    note.newline = (index % 2) == 1 && (index + 1) != model.get("notes").length; // display two items in each row
                }
            });

        }, this);

        // Sets correct offset and limit for future fetch if we are 'showing more'
        this.opts.params.offset = 0;
        if (this.collection.models.length > 0) {
            this.opts.params.limit = this.collection.models.length;
            this.opts.params.max_num = this.collection.models.length;
        }

        // Start the user focused in the activity stream input.
        _.defer(function() {
            $(".activitystream-post .sayit").focus();
        });

        return app.view.View.prototype._renderHtml.call(this);
    },

    _renderTimeline: function() {
        var self = this;
        // Construct the timeline data.
        var timeline ={
            "timeline": {
                "type":"default"
            }
        };

        var objarrays = _.map(this.collection.models, this._addTimelineEvent);
        timeline.timeline.date = _.flatten(objarrays);

        //var objarrays = _.map(this.collection.models, this._addTimelineEvent);
        //timeline.timeline.date = _.flatten(objarrays);

        if(timeline.timeline.date.length) {
            createStoryJS({
                type:       'timeline',
                width:      '100%',
                height:     '400',
                start_at_end:true,
                js: 'lib/TimelineJS/js/timeline.js',
                source: timeline,
                embed_id:   'activitystream-timeline'           // ID of the DIV you want to load the timeline into
            });
        }
        this.timelineRendered = true;

    },

    _renderCalendar: function() {
        var self = this;
        // Construct the calendar data.
        var calendar ={
                height:'400',
                header: {
                    left: 'prev,next,today',
                    center: 'title',
                    right: 'month,basicWeek,basicDay'
                },
                editable: false,
                viewDisplay: function(view) {
                    $('#activitystream-calendar').fullCalendar( 'refetchEvents' );
                },
                events: function(start, end, callback) {
                    var events = [], view = $('#activitystream-calendar').fullCalendar('getView'), objarrays;
                    if(view.name == 'month') {
                        events = self._addCalendarMonthEvent(self.collection.models);
                    }
                    else if(view.name == 'basicWeek') {
                        objarrays = _.map(self.collection.models, self._addCalendarWeekEvent);
                        events = _.flatten(objarrays);
                    }
                    else {
                        objarrays = _.map(self.collection.models, self._addCalendarDayEvent);
                        events = _.flatten(objarrays);
                    }
                    callback(events);
                },
                eventClick: function(calEvent, jsEvent, view) {
                    $('html, body').animate({ scrollTop: $('#'+calEvent.id).offset().top - 50 }, 'slow');
                }
        };

        if(typeof self.collection.models != 'undefined' && self.collection.models.length) {
            $('#activitystream-calendar').html('');
            $('#activitystream-calendar').fullCalendar(calendar);
            this.calendarRendered = true;
        }
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
