({
    timelineRendered: false,

    initialize: function(opts) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, opts);

        this.id = _.uniqueId("timeline-");
    },

    _renderTimeline: function() {
        var timelineObj = {
            "timeline": {
                "type": "default"
            }
        };

        timelineObj.timeline.date = _.flatten(_.map(this.collection.models, this._addTimelineEvent));

        // Initialize the timeline
        createStoryJS({
            type: 'timeline',
            width: '100%',
            height: '400',
            start_at_end: true,
            js: 'lib/TimelineJS/js/timeline.js',
            source: timelineObj,
            id: 'storyjs-' + this.id,
            embed_id: this.id // ID of the DIV you want to load the timeline into
        });

        this.timelineRendered = true;
    },

    /**
     * Process model data into a format the timeline can understand
     * @param {Backbone.Model} model
     * @return {Array}
     * @private
     */
    _addTimelineEvent: function(model) {
        var events = [],
            self = this;

        _.each(model.get('comments'), function(comment) {
            if (comment.value) {
                var event = {
                    tag: "commented",
                    startDate: self.parseDate(comment.date_created),
                    text: '<a href="" data-id="' + model.get("id") + '" class="showAnchor">' + tag + " by " + comment.created_by_name + '</a>',
                    headline: comment.value,
                    asset: {
                        media: '<a href=\'#Users/' + comment.created_by + '\'><img src=\'' + comment.created_by_picture_url + '\' /></a>',
                        caption: comment.created_by_name
                    }
                };

                events.push(event);
            }

            _.each(comment.notes, function(attachment) {
                var event = {
                    tag: "attached",
                    startDate: self.parseDate(attachment.date_entered),
                    text: '<a href="" data-id="' + model.get("id") + '" class="showAnchor">' + tag + " by " + attachment.created_by_name + '</a>',
                    headline: attachment.filename
                };

                events.push(event);
            });
        });

        // if (model.get("target_name") || self._parseTags(model.get("activity_data").value)) {
        if (model.get("target_name")) {
            var event = {
                startDate: self.parseDate(model.get("date_created")),
                text: '<a href="" data-id="' + model.get("id") + '" class="showAnchor">' + model.get("activity_type") + " by " + model.get("created_by_name") + '</a>',
                headline: model.get("target_name") || self._parseTags(model.get("activity_data").value),
                tag: model.get("activity_type"),
                asset: {
                    media: '<a href=\'#Users/' + model.get("created_by") + '\'><img src=\'' + model.get("created_by_picture_url") + '\' /></a>',
                    caption: model.get("created_by_name")
                }
            };

            events.push(event);
        }

        _.each(model.get('notes'), function(attachment) {
            var event = {
                tag: "attached",
                startDate: self.parseDate(attachment.date_entered),
                text: '<a href="" data-id="' + model.get("id") + '" class="showAnchor">' + tag + " by " + attachment.created_by_name + '</a>',
                headline: attachment.filename
            };

            if (attachment.file_type == "image") {
                event.asset = {
                    media: "<img src='" + attachment.url + "' />",
                    caption: attachment.filename
                };
            }
            events.push(event);
        });

        return events;
    },

    parseDate: function(dateString) {
        var t = dateString.split(/[- :]/);
        return t[1] + '/' + t[2] + '/' + t[0] + ' ' + t[3] + ':' + t[4] + ':' + t[5];
    },

    show: function() {
        this.$("#" + this.id).removeClass("hide").show();

        if (!this.timelineRendered) {
            this._renderTimeline();
        }
    },

    hide: function() {
        this.$("#" + this.id).addClass("hide").hide();
    }
})