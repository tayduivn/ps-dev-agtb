/**
 * @class Base
 * Base Class
 *
 *
 * @constructor
 * Create a new instance of the class
 * @param {Object} options
 */
var Base = function (options) {
    var defaults = {
        id : (options && options.id) || 'base-ui-' + UITools.getIndex()
    };
    $.extend(true, defaults, options);
    /**
     * Unique Identifier
     * @type {String}
     */
    this.id = defaults.id;
};

/**
 * Sets the id property
 * @return {String}
 */
Base.prototype.setId = function (value) {
    this.id = value;
    return this;
};

/**
 * Object Type
 * @type {String}
 * @private
 */
Base.prototype.type = "Core";

/**
 * Object Family
 * @type {String}
 * @private
 */
Base.prototype.family = "Core";

/**
 * Returns the object type
 * @return {String}
 */
Base.prototype.getType = function () {
    return this.type;
};

/**
 * Returns the object family
 * @return {String}
 */
Base.prototype.getFamily = function () {
    return this.family;
};

/**
 * Destroys the fields ob the object
 */
Base.prototype.dispose = function () {
    var key;
    for (key in this) {
        this[key] = null;
    }
};
if (typeof exports !== "undefined") {
        module.exports = Base;
    }