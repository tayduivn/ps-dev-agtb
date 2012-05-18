(function(app) {

    // This view is instantiated by the app module
    // It is referenced in app.config.additionalComponents setting (modify your config.js, see extensions/nomad/sample-config.js)
    app.view.views.AlertView = app.view.View.extend({

        /**
         * Shows an alert.
         * @param options(optional) Alert options.
         * @return {Object} Alert instance.
         */
        show:function (options) {
            this.alertType = options.level || 'general'; //general, success, error, warning
            this.alertMessages = (_.isString(options.messages)) ? [options.messages] : options.messages;
            this.autoClose = !!options.autoClose;

            this.$el.addClass('alert-' + this.alertType);
            this.$el.show();
            this.render();

            if(this.autoClose) {
                setTimeout(_.bind(function () {
                    this.$el.fadeOut();
                }, this), 9000);
            }

            return this;
        },
        close:function(){
            this.$el.hide();
        }


    });

})(SUGAR.App);