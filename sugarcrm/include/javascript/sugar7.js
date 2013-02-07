(function(app) {
    app.events.on("app:init", function() {
        var routes;

        routes = [
            {
                name: "index",
                route: ""
            },
            {
                name: "logout",
                route: "logout/?clear=:clear"
            },
            {
                name: "logout",
                route: "logout"
            },
            {
                name: "bwc",
                route: "bwc/*url",
                callback: function(url) {
                    app.logger.debug("BWC: " + url);

                    var frame = $('#bwc-frame');
                    if (frame.length === 1 &&
                        'index.php' + frame.get(0).contentWindow.location.search === url
                        ) {
                        // update hash link only
                        return;
                    }

                    // if only index.php is given, redirect to Home
                    if (url === 'index.php') {
                        app.router.navigate('#Home', {trigger: true});
                        return;
                    }
                    var params = {
                        layout: 'bwc',
                        url: url
                    };
                    var module = /module=([^&]*)/.exec(url);
                    if (!_.isNull(module) && !_.isEmpty(module[1])) {
                        params.module = module[1];
                    }

                    app.controller.loadView(params);
                }
            },
            {
                name: "list",
                route: ":module"
            },
            {
                name: "record",
                route: ":module/create"
            },
            {
                name: "layout",
                route: ":module/layout/:view"
            },
            {
                name: "record",
                route: ":module/:id"
            },
            {
                name: "record",
                route: ":module/:id/:action"
            }
        ];

        app.routing.setRoutes(routes);
        app.utils = _.extend(app.utils, {
                handleTooltip: function(event, viewComponent) {
                    var $el = viewComponent.$(event.target);
                    if( $el[0].offsetWidth < $el[0].scrollWidth ) {
                        $el.tooltip('show');
                    } else {
                        $el.tooltip('destroy');
                    }
                }
        });

        _.extend(app.view.Field.prototype, {
            /**
             * Decorate error gets called when this Field has a validation error.  This function applies custom error
             * styling appropriate for this field.
             * The field is put into 'edit' mode prior to this this being called.
             *
             * Fields should override/implement this when they need to provide custom error styling for different field
             * types (like e-mail, etc).  Make sure to implement clearErrorDecoration too.
             *
             * @param {Object} errors The validation error(s) affecting this field
             */
            decorateError: function(errors) {
                var ftag, self = this;

                // need to add error styling to parent view element
                ftag = this.fieldTag || '';
                self.$('.help-block').html('');
                // Remove previous exclamation then add back.
                self.$('.add-on').remove();
                // Add error styling
                self.$el.addClass('input-append');
                self.$el.addClass(ftag);
                // For each error add to error help block
                _.each(errors, function(errorContext, errorName) {
                    self.$('.help-block').append(app.error.getErrorString(errorName, errorContext));
                });
                $('<span class="add-on"><i class="icon-exclamation-sign"></i></span>').insertBefore(self.$('.help-block'));
            },

            /**
             * Remove error decoration from field if it exists.
             * Fields should override this with the decorateError function as needed.
             */
            clearErrorDecoration: function() {
                var ftag;
                this.$('.help-block').html('');
                // Remove previous exclamation then add back.
                this.$('.add-on').remove();
                this.$el.removeClass('input-append');
                ftag = this.fieldTag || '';
                this.$el.removeClass(ftag);
            }
        });
    });

    /**
     * Performs backward compatibility login.
     *
     * The OAuth token is passed and we do automatic in bwc mode by
     * getting a cookie with the PHPSESSIONID.
     */
    app.bwcLogin = function(redirectUrl) {
        var url = app.api.buildURL('oauth2', 'bwc/login');
        return app.api.call('create', url, {}, {
            success: function() {
                app.router.navigate('#bwc/' + redirectUrl, {trigger: true});
            }
        });
    };

})(SUGAR.App);
