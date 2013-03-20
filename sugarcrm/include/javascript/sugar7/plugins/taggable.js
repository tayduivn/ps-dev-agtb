(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('taggable', ['view'], {
            events: {
                'keyup .taggable': 'getEntities',
                'blur .taggable': 'hideTypeahead',
                'mouseover ul.typeahead.activitystream-tag-dropdown li': 'switchActiveTypeahead',
                'click ul.typeahead.activitystream-tag-dropdown li': 'addTag'
            },

            _getEntities: _.debounce(function(event) {
                var list,
                    leader,
                    leaderIndex,
                    el = this.$(event.currentTarget),
                    word = event.currentTarget.innerText;

                el.parent().find("ul.typeahead.activitystream-tag-dropdown").remove();

                leaderIndex = _.max([word.lastIndexOf('@'), word.lastIndexOf('#')]);
                leader = leaderIndex === -1 ? null : word.charAt(leaderIndex);

                if (!leader) {
                    // If there are no leaders, don't do anything.
                    return;
                } else if (word.indexOf(leader) === 0) {
                    word = _.last(word.split(leader));
                } else {
                    // Prevent email addresses from being caught, even though emails
                    // can have spaces in them according to the RFCs (3696/5322/6351).
                    word = _.last(word.split(' ' + leader));
                }

                if (word.length < 3) {
                    // Limit the minimum length before calling the FTS.
                    return;
                }

                var callback = function(collection) {
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

                    var ul = $("<ul/>").addClass('typeahead dropdown-menu activitystream-tag-dropdown');
                    var blank_item = '<li><a></a></li>';
                    if (list.length) {
                        items = _.map(list, function(item) {
                            var data = {
                                module: item.get('_module'),
                                id: item.get('id'),
                                name: item.get('name')
                            };
                            var i = $(blank_item).data(data);
                            var query = word.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
                            i.find('a').html(function() {
                                return item.get('name').replace(new RegExp('(' + query + ')', 'ig'), function($1, match) {
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
                };

                switch (leader) {
                    case '#':
                        app.api.search({q: word, limit: 8}, {success: function(response) {
                            var coll = app.data.createMixedBeanCollection(response.records);
                            callback(coll);
                        }});
                        break;
                    case '@':
                        var coll = app.data.createBeanCollection('Users');
                        coll.filterDef = [
                            {
                                '$or':
                                [{
                                   'first_name': {'$starts': word}
                                }, {
                                   'last_name': {'$starts': word}
                                }]
                            }
                        ];
                        coll.fetch({limit: 8, success: callback});
                        break;
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
                var el = this.$(event.currentTarget),
                    body = this.$('.sayit'),
                    originalChildren = body.clone(true).children(),
                    lastIndex = body.html().lastIndexOf("@"),
                    data = el.data();

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

            _parseTags: function(text, tagList) {
                var pattern = new RegExp(/@\[([\d\w\s-]*):([\d\w\s-]*)\]/g);

                return (!text || text.length === 0) ? text : text.replace(pattern, function(str, module, id) {
                    var name = _(tagList).find(function(el) {
                        return el.id == id;
                    }).name;
                    return "<span class='label label-" + module + "'><a href='#" + module + '/' + id + "'>" + name + "</a></span>";
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
                var self = this;
                component.on('render', function() {
                    component.$(".tagged").each(function() {
                        var x = this.innerText;
                        $(this).html(self._parseTags(x, self.model.get('data').tags));
                    });
                });
            }
        });
    });
})(SUGAR.App);
