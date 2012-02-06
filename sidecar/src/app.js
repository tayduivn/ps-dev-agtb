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
SUGAR.App = function(opts) {
    var appId = _.uniqueId("SugarApp_"),
        rootEl;

    // Set parameters
    opts = opts || {};

    if (opts.el) {
        rootEl = (_.isString(opts.el)) ? $(opts.el) : opts.el;
    }

    return {
        appId: appId,
        rootEl: rootEl
    };
};