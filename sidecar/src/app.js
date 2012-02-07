/**
 SideCar Platform
 *
 */

var SUGAR = SUGAR || {};

/**
 * Constructor class for the main framework app
 *
 * @param opts Configuration options
 *  @property el Root node of where the application will be rendered to
 */
SUGAR.App = (function() {
    var app,
        modules = {};

    function App(opts) {
        var appId = _.uniqueId("SugarApp_"),
            rootEl;

        // Set parameters
        opts = opts || {};

        if (opts.el) {
            rootEl = (_.isString(opts.el)) ? $(opts.el) : opts.el;
        } else {
            throw "SugarApp needs a root node.";
        }

        // Here we initialize all the modules;
        _.each(modules, function(module) {
            if (module.init && _.isFunction(module.init)) {
                module.init.call(this, this);
            }
        }, this);

        return _.extend({
            appId: appId,
            rootEl: rootEl
        }, this);
    }

    return {
        getInstance: function(opts) {
            app = app || _.extend(this, new App(opts));
            return app;
        },

        destroy: function() {
            Backbone.history.stop();
            app = null;
        },

        augment: function(name, obj) {
            modules[name] = obj;
        }
    }
})();