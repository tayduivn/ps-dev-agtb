({
    calendarRendered: false,

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
    },

    _addCalendarMonthEvent: function(models) {
        var events = [], counts = {};
        var getDate = function(dateString) {
            var d = app.date.parse(dateString, 'Y-m-d H:i:s');
            d.setHours(0);
            d.setMinutes(0);
            d.setSeconds(0);
            return d.toDateString();
        };

        $.each(models, function(index, model) {
            var dateStr = getDate(model.get('date_created'));
            if (typeof counts[dateStr] != 'undefined') {
                counts[dateStr].count += 1;
            }
            else {
                counts[dateStr] = {};
                counts[dateStr].count = 1;
                counts[dateStr].id = model.get('id');
                counts[dateStr].start = new Date(dateStr);
            }
        });

        $.each(counts, function(dateStr, data) {
            var event = {"allDay": true, "id": data.id};
            event.start = data.start;
            event.title = data.count + ' event(s)';
            events.push(event);
        });

        return events;
    },

    _addCalendarWeekEvent: function(models) {
        var events = [], numEvents = 5, dateFormat = 'Y-m-d H:i:s';

        $.each(models, function(index, model) {
            if (events.length < numEvents) {
                var event = {allDay: false};
                event.id = model.get('id');
                event.start = app.date.parse(model.get("date_created"), dateFormat);
                event.title = model.get("created_by_name") + " " + model.get("activity_type") + "...";
                events.push(event);
            }
            else if (events.length == numEvents) {
                var event = {allDay: true};
                event.id = model.get('id');
                event.start = app.date.parse(model.get("date_created"), dateFormat);
                event.title = (models.length - numEvents) + " more event(s)";
                events.push(event);
                return false;
            }
        });

        return events;
    },

    _addCalendarDayEvent: function(models) {
        var events = [], numEvents = 5;

        $.each(models, function(index, model) {
            var activityType = model.get('activity_type');
            var event = {allDay: false};
            event.id = model.get('id');
            event.start = app.date.parse(model.get("date_created"), 'Y-m-d H:i:s');
            if (events.length < numEvents) {
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
            }
            else if (events.length == numEvents) {
                event.allDay = true;
                event.title = (models.length - numEvents) + " more event(s)";
                events.push(event);
                return false;
            }
        });

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
                var events = [], view = $('.calendar').fullCalendar('getView'), objarrays;
                if (view.name == 'month') {
                    events = self._addCalendarMonthEvent(self.collection.models);
                }
                else if (view.name == 'basicWeek') {
                    events = self._addCalendarWeekEvent(self.collection.models);
                }
                else {
                    events = self._addCalendarDayEvent(self.collection.models);
                }
                callback(events);
            },
            eventClick: function(calEvent, jsEvent, view) {
                $('html, body').animate({ scrollTop: $('#' + calEvent.id).offset().top - 50 }, 'slow');
            }
        };

        if (typeof self.collection.models != 'undefined' && self.collection.models.length) {
            $('.calendar').html('');
            $('.calendar').fullCalendar(calendar);
        }
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