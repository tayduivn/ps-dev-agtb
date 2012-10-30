(function(app) {

    var _lastEvent;

    var _analytics = {

        trackEventSplitter: /^(.*):([\-\w]+)\.?(\S+)?$/,
        eventSplitter: /\s{2,}/,
        currentViewId: "",
        subViewId: "",

        init: function() {
            if (app.config.analytics &&
                app.config.analytics.enabled &&
                typeof Analytics != "undefined" && Analytics)
            {
                app.logger.debug("Analytics enabled - " + app.config.analytics.id);
                this.initialize();

                analytics.start(app.config.analytics.id, app.config.analytics.dryRun, app.config.analytics.debug);
            }
            else {
                app.logger.debug("Analytics disabled");
                this.trackPageView = function() {};
                this.trackEvent = function() {};
            }
        },

        // App-specific initialization
        // This should be outside this module once we move it to sidecar
        initialize: function() {
            app.events.on("app:view:change", function(layout, params) {
                var module = params.module || params.parentModule;
                app.analytics.currentViewId = params.layout + (module ? ("/" + module) : "");
                if (params.link) {
                    app.analytics.currentViewId += "/" + params.link;
                }
                if (params.create) {
                    app.analytics.currentViewId += "/create";
                }
                if (params.duplicate) {
                    app.analytics.currentViewId += "/duplicate";
                }

                app.analytics.trackPageView(app.analytics.currentViewId);
            }).on("app:header:changed", function(data) {
                // Track header change to capture subviews that don't trigger app:view:change event
                app.analytics.subViewId = data && data.subview ? ("/" + data.subview) : "";
                if (app.analytics.subViewId !== "") {
                    app.analytics.trackPageView(app.analytics.currentViewId + app.analytics.subViewId);
                }
            });
        },

        trackPageView: function(page) {
            app.logger.debug("GAN-page: " + page);
            analytics.trackPageView(page);
        },

        trackEvent: function(category, action, event, value) {
            if (event && _lastEvent == event) return;
            action = (_.isEmpty(action) && event ? event.currentTarget.id : action) || "[unknown]";
            app.logger.debug("GAN-event: " + category + ":" + action  + "(" + value + ")" + " on " + this.currentViewId + this.subViewId);
            analytics.trackEvent(category, action, this.currentViewId + this.subViewId, value);
            _lastEvent = event;
        },

        detachAnalytics: function($el) {
            var $els = this.getTrackableElements($el);
            $els.unbind('.analytics');
        },

        attachAnalytics: function($el) {
            var self = this;
            var $els = this.getTrackableElements($el);
            $els.unbind('.analytics');
            $els.each(function(i, el) {
                self._attachAnalytics(el);
            });
        },

        getTrackableElements: function($el) {
            var items = this._getTrackableElements($el);
            if ($el.attr('track')) {
                items = items.add($el);
            }
            return items;
        },

        _getTrackableElements: function(scope) {
            return $('[track]', scope);
        },

        _attachAnalytics: function(el) {
            var $el = $(el);
            var track = ($el.attr('track') || "").trim();
            if (track === "") return;

            var match = track.match(this.trackEventSplitter);
            var events = (match[1] || "").trim();
            var action = match[2];
            var css = match[3] || "";

            var ee = events.replace(this.eventSplitter,' ').split(' ');

            ee = _.map(ee, function(e) {
                return e + '.analytics';
            });

            events = ee.join(' ');

            this._attachEvents(events, action, css, $el);
        },

        _attachEvents: function(events, action, css, $el) {
            if (events) {
                $el.off(events);
                $el.on(events, function (e) {
                    app.analytics.trackEvent(e.type, action, e, ($(this).hasClass(css) ? 1 : 0));
                });
            }
        }
    };

    app.augment("analytics", _analytics, false);

})(SUGAR.App);
