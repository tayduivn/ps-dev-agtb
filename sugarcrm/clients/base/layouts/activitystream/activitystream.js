({
    events: {
        'keyup .sayit': 'getEntities',
        'blur .sayit': 'hideTypeahead',
        'mouseover ul.typeahead.activitystream-tag-dropdown li': 'switchActiveTypeahead',
        'click ul.typeahead.activitystream-tag-dropdown li': 'addTag'
    },

    className: "block filtered tabs-left activitystream-layout",

    initialize: function(opts) {
        var self = this;
        this.opts = opts;

        // The layout needs to keep track of the collection of activities so it can feed each
        // model for rendering via the activitystream view.
        this.collection = opts.context.get('collection');
        this.renderedActivities = {};

        app.view.Layout.prototype.initialize.call(this, opts);

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on('add', this.renderPost, this);
            this.collection.on('reset', function() {
                _.each(this.renderedActivities, function(view) {
                    view._dispose();
                });
                this.renderedActivities = {};
                this.collection.each(function(post) {
                    this.renderPost(post);
                }, this);
            }, this);
        }
    },

    prependPost: function(model) {
        var view = this.renderPost(model);
        view.$el.parent().prepend(view.$el);
    },

    loadData: function(options) {
        var self = this, endpoint = function(method, model, options, callbacks) {
            var real_module = self.opts.context.parent.get('module'),
                modelId = self.opts.context.parent.get('modelId'), url;
            if (real_module !== "Home") {
                url = app.api.buildURL(real_module, model.module, {id: modelId}, options.params);
            } else {
                url = app.api.buildURL(model.module, null, {}, options.params);
            }
            return app.api.call("read", url, null, callbacks);
        };

        options = _.extend({
            endpoint: endpoint,
            success: function(collection) {
                collection.each(_.bind(self.renderPost, self));
            }
        }, options);
        this.context.set("collectionOptions", options);
        this.collection.fetch(options);
    },

    renderPost: function(model) {
        var view;
        if(_.has(this.renderedActivities, model.id)) {
            view = this.renderedActivities[model.id];
        } else {
            view = app.view.createView({
                context: this.context,
                name: "activitystream",
                module: this.module,
                layout: this,
                model: model
            });
            this.addComponent(view);
            this.renderedActivities[model.id] = view;
            view.render();
        }
        return view;
    },

    _placeComponent: function(component) {
        if(component.name === "activitystream") {
            this.$el.find(".activitystream-list").append(component.el);
        } else if(component.name === "activitystream-bottom") {
            this.$el.append(component.el);
            component.render();
        } else {
            this.$el.prepend(component.el);
        }
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
        return contents;
    }
})
