(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('taggable', ['view'], {
            events: {
                'keyup .taggable': 'getEntities',
                'blur .taggable': 'hideTypeahead',
                'mouseover ul.activitystream-tag-dropdown li': 'switchActiveTypeahead',
                'click ul.activitystream-tag-dropdown li': 'addTag'
            },

            _possibleLeaders: ['@', '#'],

            // Break parsing when encountering one of the following symbols.
            _terminators: ['.', ',', '!', '?'],

            _lastLeaderPosition: function(text) {
                var leaderIndex = _.max(_.map(this._possibleLeaders, function(leader) {
                    return text.lastIndexOf(leader);
                })),
                    terminatorIndex = _.max(_.map(this._terminators, function(terminator) {
                    return text.lastIndexOf(terminator);
                }));

                return (leaderIndex > terminatorIndex)? leaderIndex : -1;
            },

            _getTerm: function(leader, text) {
                var word;
                if (!leader) {
                    // If there are no leaders, don't do anything.
                    return;
                } else {
                    word = _.last(text.split(leader));
                }

                if (word.length > 2) {
                    // Limit the minimum length before calling the FTS.
                    return word;
                }
            },

            _getLeader: function(text) {
                var leaderIndex = this._lastLeaderPosition(text);

                return leaderIndex === -1 ? null : text.charAt(leaderIndex);
            },

            _getEntities: _.debounce(function(event) {
                var self = this,
                    el = this.$(event.currentTarget),
                    text = el.text(),
                    leader = this._getLeader(text),
                    list,
                    word,
                    searchParams,
                    parentModel;

                el.parent().find('ul.activitystream-tag-dropdown').remove();

                word = this._getTerm(leader, text);

                var callback = function(collection) {
                    clearTimeout(self._taggingTimeout);
                    var word = self._getTerm(leader, text);
                    // Do initial list filtering.
                    list = collection.filter(function(entity) {
                        return entity.get('name').toLowerCase().indexOf(word.toLowerCase()) !== -1;
                    });

                    // Rank the list and trim it to no more than 8 entries.
                    var begin = [], caseSensitive = [], caseInsensitive = [];

                    _.each(list, function(item) {
                        var name = item.get('name');
                        if (name.toLowerCase().indexOf(word.toLowerCase()) === 0) {
                            begin.push(item);
                        } else if (name.indexOf(word) !== -1) {
                            caseSensitive.push(item);
                        } else {
                            caseInsensitive.push(item);
                        }
                    });
                    list = _(begin.concat(caseSensitive, caseInsensitive)).first(8);

                    var ulParent = ul.parent();
                    ul.remove().empty();

                    if (list.length) {
                        _.each(list, function(el, index) {
                            var query = word.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&'),
                                htmlName = el.get('name').replace(new RegExp('(' + query + ')', 'ig'), function($1, match) {
                                    return '<strong>' + match + '</strong>';
                                }),
                                data = {
                                    module: el.get('_module'),
                                    id: el.get('id'),
                                    name: el.get('name'),
                                    htmlName: htmlName,
                                    noAccess: (el.get('has_access') === false) //only if false, undefined does not mean no access
                                },
                                i = $(this._tplTagList(data)).data(data);

                            if (index === 0) {
                                i.addClass('active');
                            }

                            ul.append(i);
                        }, this);
                    } else {
                        var noResults = app.lang.get('LBL_SEARCH_NO_RESULTS');
                        var i = $(blankItem).addClass('placeholder active').find('a').html(noResults + word).wrap('emph');
                        ul.append(i);
                        self._taggingTimeout = setTimeout(function() {
                            self.$("ul.activitystream-tag-dropdown").remove();
                        }, 1500);
                    }

                    ulParent.append(ul);
                };

                var ul = $("<ul/>").addClass('dropdown-menu activitystream-tag-dropdown');
                var blankItem = this._tplTagList({});
                var defaultItem = $(blankItem).addClass('placeholder active').find('a').html(word + '&hellip;').wrap('emph');

                ul.css('top', el.outerHeight());

                if (word) {
                    ul.html(defaultItem).appendTo(el.parent()).show();

                    searchParams = {q: word, limit: 8};
                    switch (leader) {
                        case '#':
                            app.api.search(searchParams, {success: function(response) {
                                var coll = app.data.createMixedBeanCollection(response.records);
                                callback.call(self, coll);
                            }});
                            break;
                        case '@':
                            searchParams.module_list = "Users";
                            parentModel = this._getParentModel('record', this.context);
                            if (parentModel) {
                                searchParams.has_access_module = parentModel.get('_module');
                                searchParams.has_access_record = parentModel.get('id');
                            }

                            // We cannot use the filter API here as we need to
                            // support users typing in full names, which are not
                            // stored in the database as fields.
                            app.api.search(searchParams, {success: function(response) {
                                var coll = app.data.createBeanCollection("Users", response.records);
                                callback.call(self, coll);
                            }});
                            break;
                    }
                }
            }, 250),

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

            getEntities: function(event) {
                var dropdown = this.$("ul.activitystream-tag-dropdown"),
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
                        this.selectNextDropdownOption(active, false);
                    }
                    // Down arrow.
                    if (event.keyCode == 40) {
                        this.selectNextDropdownOption(active, true);
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

            /**
             * Given the currently selected option, select the next option, whether it is
             * by going down or up.
             *
             * @param {jQuery DOM} $current
             * @param {boolean} down
             */
            selectNextDropdownOption: function($current, down) {
                var next = down ? $current.next() : $current.prev();

                if (next.length > 0) {
                    $current.removeClass('active');

                    if (next.hasClass('disabled')) {
                        this.selectNextDropdownOption(next, down);
                    } else {
                        next.addClass('active');
                    }
                }
            },

            hideTypeahead: function() {
                var self = this;
                setTimeout(function() {
                    self.$("ul.activitystream-tag-dropdown").remove();
                }, 150);
            },

            /**
             * Make the dropdown option the currently selected option on hover.
             * @param event
             */
            switchActiveTypeahead: function(event) {
                var currentTarget = this.$(event.currentTarget);

                if (!currentTarget.hasClass('disabled')) {
                    this.$("ul.activitystream-tag-dropdown .active").removeClass('active');
                    currentTarget.addClass('active');
                }
            },

            addTag: function(event) {
                var el = this.$(event.currentTarget),
                    body = this.$('.taggable'),
                    originalChildren = body.clone(true).children(),
                    lastIndex = this._lastLeaderPosition(body.html()),
                    data = el.data();

                if (el.hasClass('placeholder') || el.hasClass('disabled')) {
                    return;
                }

                var tag = $("<span />").addClass("label").addClass("label-" + data.module).html(data.name);
                tag.data("id", data.id).data("module", data.module).data("name", data.name);
                var substring = body.html().substring(0, lastIndex);
                body.html(substring).append(tag).append("&nbsp;");

                if(body.children().length == 1) {
                    // Fixes issue where a random font tag appears. ABE-128.
                    body.prepend("&nbsp;");
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

            _parseTags: function(text, tagList) {
                var pattern = new RegExp(/@\[([\d\w\s-]*):([\d\w\s-]*)\]/g),
                    self = this;

                return (!text || text.length === 0) ? text : text.replace(pattern, function(str, module, id) {
                    var name = _(tagList).find(function(el) {
                        return el.id == id;
                    }).name;
                    return self._tplTag({module: module, id: id, name: name});
                });
            },

            /**
             * Helper method to convert HTML from tags to a text-based format.
             * @param  {string} $el
             * @return {string}
             */
            getText: function($el) {
                var contents = '';
                $el.contents().each(function() {
                    if (this.nodeName == "#text") {
                        contents += this.data.replace('&nbsp;', ' ', 'g');
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
                return $.trim(contents);
            },

            getTags: function($el) {
                var tags = [];
                $el.contents().each(function() {
                    if (this.nodeName == "SPAN") {
                        var data = $(this).data();
                        tags.push(data);
                    }
                });
                return tags;
            },

            onAttach: function(component, plugin) {
                var self = this,
                    tplTag,
                    tplTagList;

                if (!_.has(Handlebars.templates, "p.taggable.tag")) {
                    tplTag = '<span class="label label-{{module}}"><a href="#{{module}}/{{id}}">{{name}}</a></span>';
                    Handlebars.templates['p.taggable.tag'] = Handlebars.compile(tplTag);
                }
                component._tplTag = Handlebars.templates['p.taggable.tag'];

                if (!_.has(Handlebars.templates, "p.taggable.taglist")) {
                    tplTagList = '<li{{#if noAccess}} class="disabled"{{/if}}>{{#if htmlName}}<a><div class="label label-module-mini label-{{module}} pull-left">{{firstChars module 2}}</div> {{{htmlName}}}{{/if}}{{#if noAccess}}<div class="pull-right">{{str "LBL_NO_ACCESS_LOWER"}}</div>{{/if}}</a></li>';
                    Handlebars.templates['p.taggable.taglist'] = Handlebars.compile(tplTagList);
                }
                component._tplTagList = Handlebars.templates['p.taggable.taglist'];

                component.on('render', function() {
                    component.$(".tagged").each(function() {
                        var $el = $(this),
                            tagList = _.isFunction(component.getTagList)? component.getTagList() : [];

                        $el.html(self._parseTags($el.html(), tagList));
                    });
                });
            }
        });
    });
})(SUGAR.App);
