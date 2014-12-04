/**
 * @class Layout
 * Handle the layout for panels
 * @extend Base
 *
 * @constructor
 * Creates a new instance of this class
 * @param {Object} options
 */
var Layout = function (options) {
    Base.call(this, options);

    Layout.prototype.initObject.call(this, options);
};

Layout.prototype = new Base();

/**
 * Defines the object's type
 * @type {String}
 */
Layout.prototype.type = 'Layout';

/**
 * Defines the object's family
 * @type {String}
 */
Layout.prototype.family = 'Layout';

/**
 * Initializes the object with default values
 * @param {Object} options
 */
Layout.prototype.initObject = function (options) {

};
