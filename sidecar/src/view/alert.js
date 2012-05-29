/**
 * Alert  
 * @class View.Alert
 * @alias SUGAR.app.view.alert
 *
 * Interface for creating alerts via show and dismiss. Also, this module
 * keeps an internal dictionary of alerts created which can later be accessed
 * by key. This is useful so that client code can dismiss a particular alert.
 *
 * Note that the client application must define an implementation of the Backbone view
 * and add it as an additional component. This module will use that, if found,
 * by calling: 
 * <pre><code>
 * app.additionalComponents.alert.show(options)
 * </code></pre>
 * This implementation will be in charge of rendering the alert to it's UI.
 * It must also provide a close method. See portal src/view/views/alert-view.js for an example.
 */
(function(app) {

    // Dictionary of alerts
    var _alerts = {};

    var _alert = {

        /**
         * Displays an alert message and adds to internal dictionary of alerts.
         * Use supplied key later to dismiss the alert. Caller is responsible for using language translation
         * before calling!
         *
         * @param {Object} options(optional)
         * The options are application specific and are driven by the alert view referenced in {@link Config#additionalComponents} hash.
         * For example, if your custom alert implementation may support the following options:
         * <pre><code>
         * var a2 = SUGAR.App.alert('mykey', {level:"warning", title:'My Title', messages:'hi again', autoclose:false})
         * </code></pre>
         * Alternatively, you can pass an array of strings for the messages property to get separate paragraphs:
         * <pre><code>
         * var a2 = SUGAR.App.alert('mykey', {level:"info", title:'Title', messages:['para 1', 'para 2'], autoclose:true})
         * </code></pre>
         * Note: If the level property is 'process' (a loading indicator), messages is ignored. The 
         * title is the only thing displayed resulting in something like : 'Loading...' 
         * @return {Backbone.View} Alert instance 
         * @method
         */
        show: function(key, options) {
            if (!app.additionalComponents.alert) return null;
            this.dismiss(key);

            // Your AlertView implementation must define this:
            var alertView = app.additionalComponents.alert.show(options);
            if (alertView) {
                _alerts[key] = alertView;
            } else {
                app.logger.error("alert.js: Unable to show alert");
            }
            return alertView; // just for tests ;)
        },

        /**
         * Removes an alert message by key.
         * @param {String} key The key provided when previously calling show.
         * @return {Boolean} Flag indicating if dismiss was successful or not.
         * @method
         */
        dismiss: function(key) {
            if (_alerts[key]) {
                
                // Your AlertView implementation must define this:
                _alerts[key].close();
                delete _alerts[key];
                return true;
            }
            return false;
        },

        /**
         * Removes all alert messages.
         * @method
         */
        dismissAll: function() {
            _.each(_alerts, function(a, index) {
                a.close();
                delete _alerts[index];
            });
        },

        /**
         * @ignore
         */
        _get: function(key) {
            // TODO: We need this method for testing only
            // Is there a better way to do this?
            return _alerts[key];
        }
    };

    app.augment("alert", _alert, false);

})(SUGAR.App);
