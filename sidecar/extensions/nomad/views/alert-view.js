(function(app) {

    // This view is instantiated by the app module
    // It is referenced in app.config.additionalComponents setting (modify your config.js, see extensions/nomad/sample-config.js)
    app.view.views.AlertView = app.view.View.extend({

        /**
         * Shows an alert.
         * @param options(optional) Alert options.
         * @return {Object} Alert instance.
         */
        render:function () {
            app.view.View.prototype.render.call(this);
            this.$el.removeClass("alert");//hot fix styles
        },
        show:function (options) {
            var alert = {
                $el:$('<div>'),
                autoClose:!!options.autoClose,
                messages:(_.isString(options.messages)) ? [options.messages] : options.messages,
                type:options.level || 'general',
                close:function(){
                    this.$el.remove();
                }
            };

            if(alert.autoClose) {
                setTimeout(function () {
                    alert.$el.remove();
                }, 9000);
            }

            var tmpl = app.template.get('panel.alert');

            if (tmpl) {
                alert.$el.append(tmpl(alert)).prependTo(this.$el);
            }

            return alert;
        }
    });

})(SUGAR.App);