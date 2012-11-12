({
    calendarRendered: false,
    numEvents: 5,

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        if (this.module != "ActivityStream") {
            this.subcontext = this.context.getChildContext({module: "ActivityStream"});
            this.subcontext.prepare();
            this.eventCollection = this.subcontext.get("collection");
        } else {
            this.eventCollection = this.collection;
        }
    },

    _addCalendarMonthEvent: function(models) {
        var events = [],
            counts = {},
            getDate = function(dateString) {
                var d = app.date.parse(dateString, 'Y-m-d H:i:s');
                d.setHours(0);
                d.setMinutes(0);
                d.setSeconds(0);
                return d.toDateString();
            };

        _.each(models, function(model) {
            var dateStr = getDate(model.get('date_created'));
            if (!_.isUndefined(counts[dateStr])) {
                counts[dateStr].count += 1;
            } else {
                counts[dateStr] = {
                    count: 1,
                    id: model.get('id'),
                    start: new Date(dateStr)
                };
            }
        });

        _.each(counts, function(data) {
            var event = {
                "allDay": true,
                "id": data.id,
                start: data.start,
                title: data.count + ' event(s)'
            };

            events.push(event);
        });

        return events;
    },

    _addCalendarWeekEvent: function(models) {
        var events = [],
            dateFormat = 'Y-m-d H:i:s';

        _.each(models, function(model) {
            var event;

            if (events.length < this.numEvents) {
                event = {
                    allDay: false,
                    id: model.get('id'),
                    start: app.date.parse(model.get("date_created"), dateFormat),
                    title: model.get("created_by_name") + " " + model.get("activity_type") + "...",
                };
                events.push(event);
            } else if (events.length == this.numEvents) {
                event = {
                    allDay: true,
                    id: model.get('id'),
                    start: app.date.parse(model.get("date_created"), dateFormat),
                    title: (models.length - this.numEvents) + " more event(s)"
                };

                events.push(event);
                return false;
            }
        }, this);

        return events;
    },

    _addCalendarDayEvent: function(models) {
        var events = [];

        _.each(models, function(model) {
            var activityType = model.get('activity_type'),
                event = {
                    allDay: false,
                    id: model.get('id'),
                    start: app.date.parse(model.get("date_created"), 'Y-m-d H:i:s')
                };

            if (events.length < this.numEvents) {
                event.title = model.get("created_by_name") + " " + model.get("activity_type") + " ";

                switch (activityType) {
                    case "posted":
                        event.title += model.get('activity_data').value;
                        if (model.get('target_name')) {
                            event.title += "on " + model.get('target_name');
                        }
                        break;
                    case "created":
                        event.title += model.get('target_name') || 'a record';
                        break;
                    case "related":
                        event.title += (model.get('activity_data').relate_name || 'a record') + " to " + (model.get('target_name') || 'a record');
                        break;
                    case "updated":
                        $.each(model.get('activity_data'), function(index, value) {
                            if (index !== 0) {
                                event.title += ', ';
                            }
                            event.title += value.field_name;
                        });
                        event.title += model.get('target_name') ? " on " + model.get('target_name') : 'a record';
                        break;
                    default:
                        break;
                }
                events.push(event);
            } else if (events.length == this.numEvents) {
                event.allDay = true;
                event.title = (models.length - this.numEvents) + " more event(s)";
                events.push(event);
                return false;
            }
        }, this);

        return events;
    },

    renderCalendar: function() {
        var self = this;

        // Construct the calendar data.
        var calendar = {
            height: '400',
            header: {
                left: 'prev,next,today',
                center: 'title',
                right: 'month,basicWeek,basicDay'
            },
            editable: false,
            viewDisplay: function(view) {
                $('.calendar').fullCalendar('refetchEvents');
            },
            events: function(start, end, callback) {
                var events = [],
                    view = self.$('.calendar').fullCalendar('getView'),
                    objarrays;

                if (view.name == 'month') {
                    events = self._addCalendarMonthEvent(self.eventCollection.models);
                } else if (view.name == 'basicWeek') {
                    events = self._addCalendarWeekEvent(self.eventCollection.models);
                } else {
                    events = self._addCalendarDayEvent(self.eventCollection.models);
                }

                callback(events);
            },
            eventClick: function(calEvent, jsEvent, view) {
                $('html, body').animate({ scrollTop: $('#' + calEvent.id).offset().top - 50 }, 'slow');
            }
        };

        this.$('.calendar').empty();
        this.$('.calendar').fullCalendar(calendar);
    },

    show: function() {
        this.$(".calendar").removeClass("hide").show();

        if (!this.calendarRendered) {
            this.renderCalendar();
        }
    },

    hide: function() {
        this.$(".calendar").addClass("hide").hide();
    }
})