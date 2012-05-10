/**
 * Alert  
 * @class View.Alert
 * @alias SUGAR.app.view.alert
 *
 * Interface for creating alerts via show and dismiss. Also, this module
 * keeps an internal dictionary of alerts created which can later be accessed
 * by key. This is useful so that client code can dismiss a particular alert.
 *
 * Note that the client application must define an AlertView implementation
 * and add as an additional component. This module will use that, if found,
 * by calling: 
 * <code>
 * app.additionalComponents.alert.show(options)
 * </code>
 * This implementation will be in charge of rendering the alert to it's UI.
 * It must also provide a close method. See portal src/view/views/alert-view.js for an example.
 */
(function(app) {

    var _alerts = {}, _alert = null;

    _alert = {

        /**
         * Displays an alert message and adds to internal dictionary of alerts.
         * Use supplied key later to dismiss the alert. Caller is responsible for using language translation
         * before calling!
         *
         * @param {Object} Options.
         * For example, to create an alert view with a message:
         * <code>
         * var a2 = SUGAR.App.alert('mykey', {level:"warning", title:'My Title', messages:'hi again', autoclose:false})
         * </code>
         * Alternatively, you can pass an array of strings for the messages property to get separate paragraphs:
         * <code>
         * var a2 = SUGAR.App.alert('mykey', {level:"info", title:'Title', messages:['para 1', 'para 2'], autoclose:true})
         * </code>
         * Note: If the level property is 'process' (a loading indicator), messages is ignored. The 
         * title is the only thing displayed resulting in something like : 'Loading...' 
         * @return {Backbone.View} Alert instance 
         * @method
         */
        show: function(key, options) {
            var _alertView = null;
            if (!app.additionalComponents.alert) return; 

            // Your AlertView implementation must define this:
            _alertView = app.additionalComponents.alert.show(options);
            if(_alertView) {
                _alerts[key] = _alertView;
            } else {
                app.logger.error("alert.js: Unable to show alert");
            }
            return _alertView; // just for tests ;)
        },

        /**
         * Removes an alert message by key.
         * @param {String} The key provided when previously calling show.
         * @return {Boolean} Indicating if dismiss was successful or not.
         * @method
         */
        dismiss: function(key) {
            if(_alerts[key]) {
                
                // Your AlertView implementation must define this:
                _alerts[key].close();
                delete _alerts[key];
                return true;
            }
            return false;
        },

        /**
         * Removes all alert messages
         * @method
         */
        dismissAll: function() {
            _.each(_alerts, function(a, index) {
                a.close();
                delete _alerts[index];
            });
        },

        /**
         * For tests .. don't use this!
         * TODO: Is there a better way to do this?
         */
        _get: function(key) {
            return _alerts[key];
        }
    };

    app.augment("alert", _alert, false);
})(SUGAR.App);
