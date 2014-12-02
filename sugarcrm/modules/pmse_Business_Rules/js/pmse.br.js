var UITools = {
    index: 0,
    getIndex: function () {
        this.index = this.index + 1;
        return this.index;
    }
};
var getRelativePosition = function (targetElement, relativeElement) {
    var e = $(targetElement).offset(),
        re = ($(relativeElement).get(0) instanceof Document) ? {top: 0, left: 0} : $(relativeElement).offset();

    return {
        top: e.top - re.top,
        left: e.left - re.left
    };
};

function isHTMLElement (obj) {
    try {
        //Using W3 DOM2 (works for FF, Opera and Chrom)
        return obj instanceof HTMLElement;
    }
    catch(e){
        //Browsers not supporting W3 DOM2 don't have HTMLElement and
        //an exception is thrown and we end up here. Testing some
        //properties that all elements have. (works on IE7)
        return (typeof obj==="object") &&
            (obj.nodeType===1) && (typeof obj.style === "object") &&
            (typeof obj.ownerDocument ==="object");
    }
}

function isInDOM (element) {
    return jQuery(element).parents('body:last').get(0) === document.body;
}

function cloneObject (obj) {
    var newObj = {}, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) {
            newObj[key] = obj[key];
        }
    }
    return newObj;
}


/**
 * @class Style
 * Class that represent the style of a an object, {@link JCoreObject} creates an instance of this class so every
 * class that inherits from {@link JCoreObject} has an instance of this class.
 *
 *      // i.e
 *      // Let's assume that 'shape' is a CustomShape
 *      var style = new Style({
 *          cssClasses: [
 *              'sprite-class', 'marker-class', ...
 *          ],
 *          cssProperties: {
 *              border: 1px solid black,
 *              background-color: grey,
 *              ...
 *          },
 *          belongsTo: shape
 *      })
 *
 * @constructor Creates a new instance of this class
 * @param {Object} options
 * @cfg {Array} [cssClasses=[]] the classes that `this.belongsTo` has
 * @cfg {Object} [cssProperties={}] the css properties that `this.belongsTo` has
 * @cfg {Object} [belongsTo=null] a pointer to the owner of this instance
 */
var Style = function (options) {

    /**
     * JSON Object used to map each of the css properties of the object,
     * this object has the same syntax as the object passed to jQuery.css()
     *      cssProperties: {
     *          background-color: [value],
     *          border: [value],
     *          ...
     *      }
     * @property {Object}
     */
    this.cssProperties = null;

    /**
     * Array of all the classes of this object
     *      cssClasses = [
     *          'class_1',
     *          'class_2',
     *          ...
     *      ]
     * @property {Array}
     */
    this.cssClasses = null;

    /**
     * Pointer to the object to whom this style belongs to
     * @property {Object}
     */
    this.belongsTo = null;


    Style.prototype.initObject.call(this, options);
};


/**
 * The type of this class
 * @property {String}
 */
Style.prototype.type = "Style";

/**
 * Constant for the max z-index
 * @property {number} [MAX_ZINDEX=100]
 */
Style.MAX_ZINDEX = 100;

/**
 * Instance initializer which uses options to extend the config options to
 * initialize the instance
 * @private
 * @param {Object} options
 */
Style.prototype.initObject = function (options) {
    var defaults = {
        cssClasses: [],
        cssProperties: {},
        belongsTo: null
    };
    $.extend(true, defaults, options);
    this.cssClasses = defaults.cssClasses;
    this.cssProperties = defaults.cssProperties;
    this.belongsTo = defaults.belongsTo;
};

/**
 * Applies cssProperties and cssClasses to `this.belongsTo`
 * @chainable
 */
Style.prototype.applyStyle = function () {

    if (!this.belongsTo.html) {
        throw new Error("applyStyle(): can't apply style to an" +
            " object with no html");
    }

    var i,
        class_i;

    // apply the cssProperties
    $(this.belongsTo.html).css(this.cssProperties);

    // apply saved classes
    for (i = 0; i < this.cssClasses.length; i += 1) {
        class_i = this.cssClasses[i];
        if (!$(this.belongsTo.html).hasClass(class_i)) {
            $(this.belongsTo.html).addClass(class_i);
        }
    }
    return this;
};

/**
 * Extends the property `cssProperties` with a new object and also applies those new properties
 * @param {Object} properties
 * @chainable
 */
Style.prototype.addProperties = function (properties) {
    $.extend(true, this.cssProperties, properties);
    $(this.belongsTo.html).css(properties);
    return this;
};

/**
 * Gets a property from `this.cssProperties` using jQuery or `window.getComputedStyle()`
 * @param {String} property
 * @return {String}
 */
Style.prototype.getProperty = function (property) {
    return this.cssProperties[property] ||
        $(this.belongsTo.html).css(property) ||
            window.getComputedStyle(this.belongsTo.html, null)
            .getPropertyValue(property);
};

/**
 * Removes ´properties´ from the ´this.cssProperties´, also disables those properties from
 * the HTMLElement
 * @param {Object} properties
 * @chainable
 */
Style.prototype.removeProperties = function (properties) {
    var property,
        i;
    for (i = 0; i < properties.length; i += 1) {
        property = properties[i];
        if (this.cssProperties.hasOwnProperty(property)) { // JS Code Convention
            $(this.belongsTo.html).css(property, "");   // reset inline style
            delete this.cssProperties[property];
        }
    }
    return this;
};

/**
 * Adds new classes to ´this.cssClasses´ array
 * @param {Array} cssClasses
 * @chainable
 */
Style.prototype.addClasses = function (cssClasses) {
    var i,
        cssClass;
    if (cssClasses && cssClasses instanceof Array) {
        for (i = 0; i < cssClasses.length; i += 1) {
            cssClass = cssClasses[i];
            if (typeof cssClass === "string") {
                if (this.cssClasses.indexOf(cssClass) === -1) {
                    this.cssClasses.push(cssClass);
                    $(this.belongsTo.html).addClass(cssClass);
                }
            } else {
                throw new Error("addClasses(): array element is not of type string");
            }
        }
    } else {
        throw new Error("addClasses(): parameter must be of type Array");
    }
    return this;
};

/**
 * Removes classes from ´this.cssClasses´ array, also removes those classes from
 * the HTMLElement
 * @param {Array} cssClasses
 * @chainable
 */
Style.prototype.removeClasses = function (cssClasses) {

    var i,
        index,
        cssClass;
    if (cssClasses && cssClasses instanceof Array) {
        for (i = 0; i < cssClasses.length; i += 1) {
            cssClass = cssClasses[i];
            if (typeof cssClass === "string") {
                index = this.cssClasses.indexOf(cssClass);
                if (index !== -1) {
                    $(this.belongsTo.html).removeClass(this.cssClasses[index]);
                    this.cssClasses.splice(index, 1);
                }
            } else {
                throw new Error("removeClasses(): array element is not of " +
                    "type string");
            }
        }
    } else {
        throw new Error("removeClasses(): parameter must be of type Array");
    }
    return this;
};

/**
 * Removes all the classes from ´this.cssClasses´ array
 * @param {Array} cssClasses
 * @chainable
 */
Style.prototype.removeAllClasses = function () {
    this.cssClasses = [];
    $(this.belongsTo.html).removeClass();
    return this;
};

/**
 * Checks if the class is a class stored in ´this.cssClasses´
 * @param cssClass
 * @return {boolean}
 */
Style.prototype.containsClass = function (cssClass) {
    return this.cssClasses.indexOf(cssClass) !== -1;
};

/**
 * Returns an array with all the classes of ´this.belongsTo´
 * @return {Array}
 */
Style.prototype.getClasses = function () {
    return this.cssClasses;
};

/**
 * Serializes this instance
 * @return {Object}
 * @return {Array} return.cssClasses
 */
Style.prototype.stringify = function () {
    return {
        cssClasses: this.cssClasses
//        cssProperties: this.cssProperties
    };
};

/**
 * @class ArrayList
 * Construct a List similar to Java's ArrayList that encapsulates methods for
 * making a list that supports operations like get, insert and others.
 *
 *      some examples:
 *      var item,
 *          arrayList = new ArrayList();
 *      arrayList.getSize()                 // 0
 *      arrayList.insert({                  // insert an object
 *          id: 100,
 *          width: 100,
 *          height: 100
 *      });
 *      arrayList.getSize();                // 1
 *      arrayList.asArray();                // [{id : 100, ...}]
 *      item = arrayList.find('id', 100);   // finds the first element with an id that equals 100
 *      arrayList.remove(item);             // remove item from the arrayList
 *      arrayList.getSize();                // 0
 *      arrayList.isEmpty();                // true because the arrayList has no elements
 *
 * @constructor Returns an instance of the class ArrayList
 */
var ArrayList = function () {
    /**
     * The elements of the arrayList
     * @property {Array}
     * @private
     */
    var elements = [],
        /**
         * The size of the array
         * @property {number} [size=0]
         * @private
         */
        size = 0,
        index,
        i;
    return {

        /**
         * The ID of this ArrayList is generated using the function Math.random
         * @property {number} id
         */
        id: Math.random(),
        /**
         * Gets an element in the specified index or undefined if the index
         * is not present in the array
         * @param {number} index
         * @returns {Object / undefined}
         */
        get : function (index) {
            return elements[index];
        },
        /**
         * Inserts an element at the end of the list
         * @param {Object}
         * @chainable
         */
        insert : function (item) {
            elements[size] = item;
            size += 1;
            return this;
        },
        /**
         * Inserts an element in a specific position
         * @param {Object} item
         * @chainable
         */
        insertAt: function(item, index) {
            elements.splice(index, 0, item);
            size = elements.length;
            return this;
        },
        /**
         * Removes an item from the list
         * @param {Object} item
         * @return {boolean}
         */
        remove : function (item) {
            index = this.indexOf(item);
            if (index === -1) {
                return false;
            }
            //swap(elements[index], elements[size-1]);
            size -= 1;
            elements.splice(index, 1);
            return true;
        },
        /**
         * Gets the length of the list
         * @return {number}
         */
        getSize : function () {
            return size;
        },
        /**
         * Returns true if the list is empty
         * @returns {boolean}
         */
        isEmpty : function () {
            return size === 0;
        },
        /**
         * Returns the first occurrence of an element, if the element is not
         * contained in the list then returns -1
         * @param {Object} item
         * @return {number}
         */
        indexOf : function (item) {
            for (i = 0; i < size; i += 1) {
                if (item.id === elements[i].id) {
                    return i;
                }
            }
            return -1;
        },
        /**
         * Returns the the first object of the list that has the
         * specified attribute with the specified value
         * if the object is not found it returns undefined
         * @param {string} attribute
         * @param {string} value
         * @return {Object / undefined}
         */
        find : function (attribute, value) {
            var i,
                current;
            for (i = 0; i < elements.length; i += 1) {
                current = elements[i];
                if (current[attribute] === value) {
                    return current;
                }
            }
            return undefined;
        },

        /**
         * Returns true if the list contains the item and false otherwise
         * @param {Object} item
         * @return {boolean}
         */
        contains : function (item) {
            if (this.indexOf(item) !== -1) {
                return true;
            }
            return false;
        },
        /**
         * Sorts the list using compFunction if possible, if no compFunction
         * is passed as an parameter then it returns false (the list is not sorted)
         * @param {Function} compFunction
         * @return {boolean}
         */
        sort : function (compFunction) {
            var returnValue = false;
            if (compFunction) {
                elements.sort(compFunction);
                returnValue = true;
            }
            return returnValue;
        },
        /**
         * Returns the list as an array
         * @return {Array}
         */
        asArray : function () {
            return elements;
        },
        /**
         * Returns the first element of the list
         * @return {Object}
         */
        getFirst : function () {
            return elements[0];
        },
        /**
         * Returns the last element of the list
         * @return {Object}
         */
        getLast : function () {
            return elements[size - 1];
        },

        /**
         * Returns the last element of the list and deletes it from the list
         * @return {Object}
         */
        popLast : function () {
            var lastElement;
            size -= 1;
            lastElement = elements[size];
            elements.splice(size, 1);
            return lastElement;
        },
        /**
         * Returns an array with the objects that determine the minimum size
         * the container should have
         * The array values are in this order TOP, RIGHT, BOTTOM AND LEFT
         * @return {Array}
         */
        getDimensionLimit : function () {
            var result = [100000, -1, -1, 100000],
                objects = [undefined, undefined, undefined, undefined];
            //number of pixels we want the inner shapes to be
            //apart from the border

            for (i = 0; i < size; i += 1) {
                if (result[0] > elements[i].y) {
                    result[0] = elements[i].y;
                    objects[0] = elements[i];

                }
                if (result[1] < elements[i].x + elements[i].width) {
                    result[1] = elements[i].x + elements[i].width;
                    objects[1] = elements[i];
                }
                if (result[2] < elements[i].y + elements[i].height) {
                    result[2] = elements[i].y + elements[i].height;
                    objects[2] = elements[i];
                }
                if (result[3] > elements[i].x) {
                    result[3] = elements[i].x;
                    objects[3] = elements[i];
                }
            }
            return result;
        },
        /**
         * Clears the content of the arrayList
         * @chainable
         */
        clear : function () {
            if (size !== 0) {
                elements = [];
                size = 0;
            }
            return this;
        },
        /**
         * Returns the canvas of an element if possible
         * @return {Canvas / undefined}
         */
        getCanvas : function () {
            return (this.getSize() > 0) ? this.get(0).getCanvas() : undefined;
        }
    };
};

// Declarations created to instantiate in NodeJS environment
if (typeof exports !== 'undefined') {
    module.exports = ArrayList;
//    var _ = require('../../lib/underscore/underscore.js');
}

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
/**
 * @class Proxy
 * Handles the proxy connections
 * @extend Base
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 */
var Proxy = function (options) {
    Base.call(this, options);
    /**
     * Defines the URL to connect
     * @type {String}
     */
    this.url = null;
    this.callback = null;
    Proxy.prototype.initObject.call(this, options);
};
Proxy.prototype = new Base();

/**
 * Defines the object's type
 * @type {String}
 */
Proxy.prototype.type = 'Proxy';

/**
 * Defines the object's family
 * @type {String}
 */
Proxy.prototype.family = 'Proxy';

/**
 * Initializes the object with default values
 * @param {Object} options
 */
Proxy.prototype.initObject = function (options) {
    var defaults = {
        url: null,
        callback: null
    };
    $.extend(true, defaults, options);
    this.setUrl(defaults.url)
        .setCallback(defaults.callback);
};

/**
 * Sets the URL property
 * @param {String} url
 * @return {*}
 */
Proxy.prototype.setUrl = function (url) {
    this.url = url;
    return this;
};

Proxy.prototype.setCallback = function (callback) {
    this.callback = callback;
    return this;
};

/**
 * Obtains the data
 */
Proxy.prototype.getData = function () {

};

/**
 * Sends the data
 * @param {Object} data
 * @param {Object} [callback]
 */
Proxy.prototype.sendData = function (data, callback) {

};

/**
 * @class Element
 * Base class to handle HTML Divs
 * @extend Base
 *
 *
 * @constructor
 * Create a new instace of the class 'Element'
 * @param {Object} options
 */
var Element = function (options) {
    Base.call(this, options);
    /**
     * Absolute X position of the HTML Element
     * @type {Number}
     */
    this.x = null;
    /**
     * Absolute Y position of the HTML Element
     * @type {Number}
     */
    this.y = null;
    /**
     * Width dimension of the HTML Element
     * @type {Number}
     */
    this.width = null;
    /**
     * Height dimension of the HTML Element
     * @type {Number}
     */
    this.height = null;
    /**
     * Pointer to the HTMLElement object
     * @type {HTMLElement}
     */
    this.html = null;
    /**
     * Intance of the jCore.Style object to handle style tags
     * @type {Object}
     */
    this.style = null;
    /**
     * Defines if the HTML element is visible
     * @type {Boolean}
     */
    this.visible = null;
    /**
     * Defines the value of the zIndex for the HTML Element
     * @type {Number}
     */
    this.zOrder = null;

    Element.prototype.initObject.call(this, options);
};
Element.prototype = new Base();

/**
 * Defines the object type
 * @type {String}
 * @private
 */
Element.prototype.type = "Base";
/**
 * Defines the object family
 * @type {String}
 * @private
 */
Element.prototype.family = "Base";

/**
 * Initialize the object with the default values
 * @param {Object} options
 * @private
 */
Element.prototype.initObject = function (options) {
    var defaults = {
        //id : (options && options.id) || jCore.Utils.generateUniqueId(),
        style : {
            cssProperties: {},
            cssClasses: []
        },
        width : 0,
        height : 0,
        x : 0,
        y : 0,
        zOrder : 1,
        visible : true
    };
    $.extend(true, defaults, options);
    this//.setId(defaults.id)
        .setStyle(new Style({
            belongsTo: this,
            cssProperties: defaults.style.cssProperties,
            cssClasses: defaults.style.cssClasses
        }))
        .setDimension(defaults.width, defaults.height)
        .setPosition(defaults.x, defaults.y)
        .setZOrder(defaults.zOrder)
        .setVisible(defaults.visible);
};

/**
* Sets the id property
* @param {String} newID
* @return {*}
*/
Element.prototype.setId = function (newID) {
    this.id = newID;
    if (this.html) {
        this.html.id = this.id;
    }
    return this;
};
/**
 * Sets the X property
 * @param {Number} x
 * @return {*}
 */
Element.prototype.setX = function (x) {
    if (typeof x === 'number') {
        this.x = x;
        if (this.html) {
            this.style.addProperties({left: this.x});
        }
    } else {
        throw new Error('setX: x param is not a number');
    }
    return this;
};

/**
 * Sets the Y property
 * @param {Number} y
 * @return {*}
 */
Element.prototype.setY = function (y) {
    if (typeof y === 'number') {
        this.y = y;
        if (this.html) {
            this.style.addProperties({top: this.y});
        }
    } else {
        throw new Error('setY: y param is not a number');
    }
    return this;
};

/**
 * Sets the width property
 * @param {Number} w
 * @return {*}
 */
Element.prototype.setWidth = function (w) {
    if (typeof w === 'number') {
        this.width = w;
        if (this.html) {
            this.style.addProperties({width: this.width});
        }
    } else {
        throw new Error('setWidth: w is not a number');
    }
    return this;
};

/**
 * Sets the height property
 * @param {Number} h
 * @return {*}
 */
Element.prototype.setHeight = function (h) {
    if (typeof h === 'number') {
        this.height = h;
        if (this.html) {
            this.style.addProperties({height: this.height});
        }
    } else {
        throw new Error('setHeight: h is not a number');
    }
    return this;
};

/**
 * Sets the position of the HTML Element
 * @param {Number} x
 * @param {Number} y
 * @return {*}
 */
Element.prototype.setPosition = function (x, y) {
    this.setX(x);
    this.setY(y);
    return this;
};

/**
 * Sets the dimension of the HTML Element
 * @param {Number} w
 * @param {Number} h
 * @return {*}
 */
Element.prototype.setDimension = function (w, h) {
    this.setWidth(w);
    this.setHeight(h);
    return this;
};

/**
 * Sets the xOrder property
 * @param {Number} z
 * @return {*}
 */
Element.prototype.setZOrder = function (z) {
    if (typeof z === 'number' && z > 0) {
        this.zOrder = z;
        if (this.html) {
            this.style.addProperties({zIndex: this.zOrder});
        }
    }
    return this;
};

/**
 * Sets the visible property
 * @param {Boolean} value
 * @return {*}
 */
Element.prototype.setVisible = function (value) {
    if (_.isBoolean(value)) {
        this.visible = value;
        if (this.html) {
            if (value) {
                this.style.addProperties({display: "inline"});
            } else {
                this.style.addProperties({display: "none"});
            }
        }
    }
    return this;
};

/**
 * Sets the style object
 * @param {Object} style Instance of jCore.Style
 * @return {*}
 */
Element.prototype.setStyle = function (style) {
    if (style instanceof Style) {
        this.style = style;
    }
    return this;
};

/**
 * Creates a new HTML Element
 * @param {String} type
 * @return {HTMLElement}
 */
Element.prototype.createHTMLElement = function (type) {
    return document.createElement(type);
};

/**
 * Creates the hmtl object
 * @return {HTMLElement}
 */
Element.prototype.createHTML = function () {
    if (!this.html) {
        this.html = this.createHTMLElement('div');
        this.html.id = this.id;

        this.style.applyStyle();

        this.style.addProperties({
            position: "absolute",
            left: this.x,
            top: this.y,
            width: this.width,
            height: this.height,
            zIndex: this.zOrder
        });
    }
    return this.html;
};

/**
 * Defines the functionality to paint the HTML element
 * @abstract
 */
Element.prototype.paint = function () {
};

/**
 * Returns the html pointer
 * @return {HTMLElement}
 */
Element.prototype.getHTML = function () {
    if (!this.html) {
        this.createHTML();
    }
    return this.html;
};

/**
 * Calculates the text width
 * @param {String} text
 * @param {String} [font]
 * @return {*}
 */
Element.prototype.calculateWidth = function (text, font) {
    //TODO Improve the div creation (maybe we can use a singleton for this)
    var f = font || '12px arial',
        $o = $(this.createHTMLElement('div')), w;
        $o.text(text)
            .css({'position': 'absolute', 'float': 'left', 'white-space': 'nowrap', 'visibility': 'hidden', 'font': f})
            .appendTo($('body')),
        w = $o.width();

    $o.remove();

    return w;
};
var SugarProxy = function (options) {
    Proxy.call(this, options);
    this.uid = null;
    this.getMethod = null;
    this.sendMethod = null;
    SugarProxy.prototype.initObject.call(this, options);
};

SugarProxy.prototype = new Proxy();

SugarProxy.prototype.type = 'SugarProxy';

SugarProxy.prototype.initObject = function (options) {
    var defaults = {
        sendMethod: 'PUT',
        getMethod: 'GET',
        createMethod: 'POST',
        uid: null
    };
    $.extend(true, defaults, options);
    this.setUid(defaults.uid)
        .setSendMethod(defaults.sendMethod)
        .setGetMethod(defaults.getMethod)
        .setCreateMethod(defaults.createMethod);
};

SugarProxy.prototype.setUid = function (id) {
    this.uid = id;
    return this;
};


SugarProxy.prototype.setSendMethod = function (method) {
    this.sendMethod = method;
    return this;
};

SugarProxy.prototype.setGetMethod = function (method) {
    this.getMethod = method;
    return this;
};
SugarProxy.prototype.setCreateMethod = function (method) {
    this.createMethod = method;
    return this;
};

SugarProxy.prototype.getData = function (params, callback) {
    var operation, self = this, url;

    operation = this.getOperation(this.getMethod);
    if (operation === 'read' && params) {
        url = App.api.buildURL(this.url, null, null, params);
    } else {
        url = App.api.buildURL(this.url, null, null);
    }
    App.api.call(operation, url, {}, {
        success: function (response) {
            if (callback && callback.success) {
                callback.success.call(self, response);
            }
        },
        error: function (sugarHttpError) {
            if(callback && typeof callback.error === 'function') {
                callback.error.call(self, sugarHttpError);
            }
        }
    });
};

SugarProxy.prototype.sendData = function (data, callback) {

    var operation, self = this, send, url;

    operation = this.getOperation(this.sendMethod);
    url = App.api.buildURL(this.url, null, null);
    attributes = {
        data: data
    };

    App.api.call(operation, url, attributes, {
        success: function (response) {

            if (callback && callback.success) {
                callback.success.call(self, response);
            }
        }
    });
};
SugarProxy.prototype.createData = function (data, callback) {

    var operation, self = this, send, url;

    operation = this.getOperation(this.createMethod);
    url = App.api.buildURL(this.url, null, null);
    attributes = {
        data: data
    };

    App.api.call(operation, url, attributes, {
        success: function (response) {
            if (callback && callback.success) {
                callback.success.call(self, response);
            }
        }
    });
};
SugarProxy.prototype.removeData = function (params, callback) {
    var operation, self = this, url;
    operation = 'remove';
    if (operation === 'remove' && params) {
        url = App.api.buildURL(this.url, null, null, params);
    } else {
        url = App.api.buildURL(this.url, null, null);
    }
    App.api.call('delete', url, {}, {
        success: function (response) {
//            console.log('getData');
//            console.log(response);
            if (callback && callback.success) {
                callback.success.call(self, response);
            }
        }
    });
};

SugarProxy.prototype.getOperation = function (method) {
    var out;
    switch (method) {
        case 'GET':
            out = 'read';
            break;
        case 'POST':
            out = 'create';
            break;
        case 'PUT':
            out = 'update';
            break;
        case 'DELETE':
            out = 'delete';
            break;
    }
    return out;
};

var ExpressionContainer = function (options, parent) {
    Element.call(this, options);
    //this.isCBOpen = null;
    //this.isDDOpen = null;
    this.tooltipHandler = null;
    this.expression = null;
    this.value = null;
    this.parent = null;
    this.onChange = null;
    ExpressionContainer.prototype.init.call(this, options, parent);
};

ExpressionContainer.prototype = new Element();

ExpressionContainer.prototype.type = 'ExpressionContainer';

ExpressionContainer.prototype.family = 'ExpressionContainer';

ExpressionContainer.prototype.init = function (options, parent) {
    var defaults = {
        expression: [],
        onChange: null
    };
    $.extend(true, defaults, options);
    this.setExpressionValue(defaults.expression)
        //.setIsCBOpen(defaults.isCBOpen)
        //.setIsDDOpen(defaults.isDDOpen)
        .setParent(parent)
        .setOnChangeHandler(defaults.onChange);
};

ExpressionContainer.prototype.setOnChangeHandler = function(handler) {
    if (!(handler === null || typeof handler === 'function')) {
        throw new Error("setOnChangeHandler(): The parameter must be a function or null.");
    }
    this.onChange = handler;
    return this;
};

ExpressionContainer.prototype.setExpressionValue = function (value) {
    this.expression = value;
    this.updateExpressionView();
    return this;
};

//ExpressionContainer.prototype.setIsCBOpen = function (value) {
//    this.isCBOpen = value;
//    return this;
//};
//
//ExpressionContainer.prototype.setIsDDOpen = function (value) {
//    this.isDDOpen = value;
//    return this;
//};

ExpressionContainer.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

ExpressionContainer.prototype.clear = function () {
    return this;
};

//ExpressionContainer.prototype.addItem = function (value) {
//    console.log('AddItem method was called.' + this.id, value);
//    this.setExpressionValue(value);
//    return this;
//};

ExpressionContainer.prototype.remove = function () {
    $(this.html).remove();
    delete this.tooltipHandler;
    delete this.expression;
    delete this.value;
    delete this.parent;
};

ExpressionContainer.prototype.getObject = function () {
    return this.expression;
};

ExpressionContainer.prototype.isValid = function () {
    return true;
};

ExpressionContainer.prototype.createHTML = function () {
    var dvContainer,
        span;

    if(this.html) {
        return this.html;
    }

    span = this.createHTMLElement('span');
    dvContainer = this.createHTMLElement("div");
    dvContainer.className = 'expression-container-cell';
    $(dvContainer).attr('data-placement', 'bottom');
    dvContainer.setAttributeNode(document.createAttribute('title'));

    span.appendChild(dvContainer);
    this.html = span;
    this.dvContainer = dvContainer;

    this.updateExpressionView();
    this.attachListeners();

    return this.html;
};

ExpressionContainer.prototype.updateExpressionView = function () {
    var value = this.parseValue(this.expression),
        $container;

    $container = $(this.dvContainer);
    $container.text(value);
    $container.attr('data-original-title', value);

    return this;
};

ExpressionContainer.prototype.parseValue = function (expression) {
    var val = '';
    if (expression) {
        for (i = 0; i < expression.length; i += 1) {
            if (val !== '') {
                val += ' ';
            }
            val += expression[i].expLabel;
        }
    }
    return val;
};

ExpressionContainer.prototype.attachListeners = function () {
    var self = this;

    if(!this.html) {
        return this;
    }

    //Define Tooltip when ellipsis overflow is active
    $(this.dvContainer).on('mouseenter', function () {
        if (this.offsetWidth < this.scrollWidth) {
            this.tooltipHandler = $(this).tooltip({trigger:'manual'});
            this.tooltipHandler.tooltip('show');
        }
    }).on("mouseleave", function() {
        if (this.tooltipHandler) {
            this.tooltipHandler.tooltip('destroy');
            this.tooltipHandler = null;
        }
    });

    //Define click events to handle CriteriaBuilderControl
    $(this.html).on('click', function () {
        self.handleClick(this);
    });

    return this;
};

ExpressionContainer.prototype.handleClick = function (element) {
    var globalParent,
        parentVariable;

    globalParent = this.parent.parentElement.parent;
    parentVariable = this.parent.parentElement;

    if (parentVariable.fieldType || parentVariable.isReturnType) {
        if (parentVariable.fieldType === 'DropDown' || parentVariable.fieldType === 'Checkbox') {
            this.handleDropDownBuilder(globalParent, parentVariable, element);
        } else {
            this.handleCriteriaBuilder(globalParent, parentVariable, element);
        }
    } else {
        App.alert.show('expression-variable-click', {
            level: 'warning',
            messages: 'Please define first the column type.',
            autoClose: true
        });
    }
};

ExpressionContainer.prototype.handleCriteriaBuilder = function (globalParent, parentVariable, element) {
    var self = this,
        value,
        defaults = {
            operators: false,
            evaluations: false,
            variables: false,
            constants: false
        },
        config = {};

    if (globalParent.globalCBControl.isOpen()) {
        globalParent.globalCBControl.close();
        //this.setIsCBOpen(false);
    } else {
        globalParent.globalCBControl.setOwner(element);
        globalParent.globalCBControl.setOnChangeHandler(function (expressionControl, newValue, oldValue) {
            value = JSON.parse(newValue);
            self.setExpressionValue(value);
            if (typeof self.onChange === 'function') {
                self.onChange(newValue, oldValue);
            }
        });
        if (parentVariable.isReturnType) {
            config = {
                constants: true,
                variables: {
                    dataRoot: null,
                    data: parentVariable.fields,
                    dataFormat: "tabular",
                    textField: "label",
                    moduleTextField: "moduleText",
                    moduleValueField: "moduleValue"
                }
            };
        } else {
            switch (parentVariable.fieldType) {
                case 'Date':
                    config = {
                        operators: {
                            arithmetic: ["+","-"]
                        },
                        constants: {
                            date: true,
                            timespan: true
                        },
                        variables: {
                            dataRoot: null,
                            data: parentVariable.fields,
                            dataFormat: "tabular",
                            textField: "label",
                            typeFilter: parentVariable.fieldType,
                            moduleTextField: "moduleText",
                            moduleValueField: "moduleValue"
                        }
                    };
                    break;
                case 'TextArea':
                case 'TextField':
                case 'email':
                case 'Phone':
                case 'URL':
                    $.extend(true, config, {
                        constants: {
                            basic: {
                                string: true
                            }
                        },
                        variables: {
                            dataRoot: null,
                            data: parentVariable.fields,
                            dataFormat: "tabular",
                            textField: "label",
                            typeFilter: parentVariable.fieldType,
                            moduleTextField: "moduleText",
                            moduleValueField: "moduleValue"
                        }
                    });
                    break;
                case 'Integer':
                    $.extend(true, config, {
                        operators: {
                            arithmetic: true,
                            group: true
                        },
                        constants: {
                            basic: {
                                number: true
                            }
                        },
                        variables: {
                            dataRoot: null,
                            data: parentVariable.fields,
                            dataFormat: "tabular",
                            textField: "label",
                            typeFilter: parentVariable.fieldType,
                            moduleTextField: "moduleText",
                            moduleValueField: "moduleValue"
                        }
                    });
                    break;
                default:
                    if (parentVariable.isReturnType) {
                        $.extend(true, config, {
                            constants: {
                                basic: true,
                                date: true
                            }
                        });
                    }
            }    
        }
        
        $.extend(true, defaults, config);
        //globalParent.globalCBControl.clear();
        globalParent.globalCBControl
            .setOperators(defaults.operators)
            .setEvaluations(defaults.evaluations)
            .setVariablePanel(defaults.variables)
            .setConstantPanel(defaults.constants);
        globalParent.globalCBControl.setValue(this.expression);
        globalParent.globalCBControl.open();
        //this.setIsCBOpen(true);
    }
};

ExpressionContainer.prototype.handleDropDownBuilder = function (globalParent, parentVariable, element) {
    var self = this,
        value;
    if (globalParent.globalDDSelector.isOpen()) {
        globalParent.globalDDSelector.close();
        //this.setIsDDOpen(false);
    } else {
        globalParent.globalDDSelector.setOwner(element);
        globalParent.globalDDSelector.setOnItemValueActionHandler(function (dropdownSelector, list, obj) {
            var prevValue = JSON.stringify(self.expression);
            if (Object.keys(obj).length === 0) {
                value = [];
            } else {
                value = [{
                    expType: "CONSTANT",
                    expSubType: "string",
                    expLabel: obj.text,
                    expValue: obj.value
                }];
            }
            self.setExpressionValue(value);
            globalParent.globalDDSelector.close();

            if (typeof self.onChange === 'function') {
                self.onChange(JSON.stringify(self.expression), prevValue);
            }
            //self.setIsDDOpen(false);
        });
        globalParent.globalDDSelector.setValues(parentVariable.combos[parentVariable.module + globalParent.moduleFieldSeparator 
            + parentVariable.field]);
        globalParent.globalDDSelector.setValue(this.expression);
        globalParent.globalDDSelector.open();
        //this.setIsDDOpen(true);
    }
};


//DecisionTable    
    var DecisionTable = function(options) {
        Element.call(this, {id: options.id});
        this.base_module = null;
        this.hitType = null;
        this.dom = null;
        this.name = null;
        this.proxy = null;
        this.conditions = null;
        this.conclusions = null;
        this.decisionRows = null;
        this.rows = null;
        this.width = null;
        this.onAddColumn = null;
        this.onRemoveColumn = null;
        this.onAddRow = null;
        this.onRemoveRow = null;
        this.onChange = null;
        this.onDirty = null;
        this.showDirtyIndicator = null;
        this.isDirty = null;
        this.fields = [];
        this.combos = {};
        this.language = {};
        this.correctlyBuilt = false;
        this.globalCBControl = null;
        this.globalDDSelector = null;
        this.moduleFieldSeparator = "|||";
        DecisionTable.prototype.initObject.call(this, options || {});
    };

    DecisionTable.prototype = new Element();

    DecisionTable.prototype.type = 'DecisionTable';

    DecisionTable.prototype.initObject = function(options) {
        var defaults = {
            name: "",
            proxy: new SugarProxy(),
            restClient: null,
            base_module: "",
            type: 'multiple',
            width: 'auto',
            rows: 0,
            container: null,
            columns: {
                conditions: [],
                conclusions: []
            },
            ruleset: [],
            onAddColumn: null,
            onRemoveColumn: null,
            onChange: null,
            showDirtyIndicator: true,
            language: {
                SINGLE_HIT: 'Single Hit',
                MULTIPLE_HIT: 'Multiple Hit',
                CONDITIONS: 'Conditions',
                CONCLUSIONS: 'Conclusions',
                ADD_ROW: 'Add row',
                REMOVE_ROW: 'Remove row',
                CLICK_TO_EDIT: 'Click to edit',
                ERROR_CONCLUSION_VAR_DUPLICATED: 'conclusion variable is duplicated',
                ERROR_EMPTY_RETURN_VALUE: 'The "Return" conclusion is empty',
                ERROR_EMPTY_ROW: 'No conditions were specified in row with conclusions, It\'s allowed only one row with no conditions (default ruleset)',
                ERROR_NOT_EXISTING_FIELDS: 'This Business Rules Table can\'t be created, some required fields are missing: %s.',
                ERROR_INCORRECT_BUILD: 'This Business Rules Table can\'t is incorrectly built',
                MSG_DELETE_ROW: 'Do you really want to delete this rule set?',
                MIN_ROWS: 'The decision table must have at least 1 row',
                MIN_CONDITIONS_COLS: 'The decision table must have at least 1 condition column',
                MIN_CONCLUSIONS_COLS: 'The decision table must have at least 1 conclusion column',
                //--for DecisionTableVariable--//
                LBL_RETURN: 'Return',
                ERROR_NO_VARIABLE_SELECTED: 'No variable was selected',
                //for DecisionTableValue
                ERROR_INVALID_EXPRESSION: 'Invalid expression',
                LBL_VARIABLES: 'Variables',
                LBL_CONSTANTS: 'Constants',
                LBL_ADD_CONDITION: 'Add condition',
                LBL_ADD_CONCLUSION: 'Add conclusion',
                //for DecisionTableSingleValue
                ERROR_MISSING_EXPRESSION_OR_OPERATOR: 'missing expression or operator'
            }
        }, that = this;

        $.extend(true, defaults, options);

        this.dom = {};
        this.conclusions = [];
        this.conditions = [];
        this.decisionRows = 0;
        this.onAddColumn = defaults.onAddColumn;
        this.onRemoveColumn = defaults.onRemoveColumn;
        this.onChange = defaults.onChange;
        this.rows = parseInt(defaults.rows, 10);
        this.language = defaults.language;

        this.setName(defaults.name)
            .setProxy(defaults.proxy/*, defaults.restClient*/)
            .setBaseModule(defaults.base_module)
            .setHitType(defaults.type)
            .setWidth(defaults.width)
            .setShowDirtyIndicator(defaults.showDirtyIndicator);

        //this.getHTML();
        if(defaults.container) {
            $(defaults.container).append(this.getHTML());

            if(!this.isDOMNodeInsertedSupported) {
                this.updateDimensions();
            }
        }

        this.auxConclutions = defaults.columns.conclusions;
        this.auxConditions = defaults.columns.conditions;
        this.rules = defaults.ruleset;

        this.globalCBControl = new ExpressionControl({
            matchOwnerWidth: false,
            width: 250,
            allowInput: false,
            itemContainerHeight: 70,
            appendTo: jQuery("#businessrulecontainer").get(0)
        });

        this.globalDDSelector = new DropdownSelector({
            matchOwnerWidth: true
        });

        this.getFields();
    };

    DecisionTable.prototype.setShowDirtyIndicator = function(show) {
        this.showDirtyIndicator = !!show;
        return this;
    };

    DecisionTable.prototype.getIsDirty = function() {
        return this.isDirty;
    };

    DecisionTable.prototype.setIsDirty = function(dirty, silence) {
        this.isDirty = dirty;
        if (!silence) {
            if(typeof this.onDirty === 'function') {
                this.onDirty.call(this, dirty);
            }
        }
        return this;
    };

    DecisionTable.prototype.onChangeVariableHandler = function() {
        var that = this;
        return function(newVal, oldVal) {
            var valid, cell = this.getHTML(),
                index = $(cell.parentElement).find(cell.tagName.toLowerCase()).index(cell);
            if(this.variableMode === 'condition') {
                valid = that.validateColumn(index, 0);
            } else {
                valid = that.validateColumn(index, 1);
            }

            that.setIsDirty(true);

            if(typeof that.onChange === 'function') {
                that.onChange.call(that, {
                    object: this,
                    newVal: newVal,
                    oldVal: oldVal
                }, valid);
            }
        };
    };

    DecisionTable.prototype.onChangeValueHandler = function() {
        var that = this;
        return function(valueObject, newVal, oldVal) {
            var row, cell, index, indexColumn, isEvaluationVariable, valid;

            isEvaluationVariable = valueObject instanceof DecisionTableValueEvaluation;
            cell = isEvaluationVariable ? valueObject.getHTML()[0] : valueObject.getHTML();
            row = cell.parentElement;
            indexColumn = $(cell.parentElement).find("td").index(cell) / (isEvaluationVariable ? 2 : 1);
            index = $(row.parentElement).find("tr").index(row);

            /*valid = valueObject.isValid();*/

            //if(valid.valid) {
            valid = that.validateColumn(indexColumn, isEvaluationVariable ? 0 : 1);
            if(valid.valid) {
                valid = that.validateRow(index);
            }
            /* } else {
             valid.location = (isEvaluationVariable ? 'Condition' : 'Conclusion') + " # " + (indexColumn + 1) + " - row # " + (index + 1);
             }*/
            that.setIsDirty(true);
            if(typeof that.onChange === 'function') {
                that.onChange.call(that, {
                    object: valueObject,
                    newVal: newVal,
                    oldVal: oldVal
                }, valid);
            }
        };
    };

    DecisionTable.prototype.removeAllConclusions = function() {
        while(this.conclusions.length) {
            this.conclusions[0].remove();
        }

        return this;
    };

    DecisionTable.prototype.removeAllConditions = function() {
        while(this.conditions.length) {
            this.conditions[0].remove();
        }
        return this;
    };

    DecisionTable.prototype.setConditions = function(conditions) {
        var i;
        this.removeAllConditions();
        for(i = 0; i < conditions.length; i+=1) {
            this.addCondition(conditions[i]);
        }
        return this;
    };

    DecisionTable.prototype.setConclusions = function(conclusions) {
        var i;
        this.removeAllConclusions();
        for(i = 0; i < conclusions.length; i+=1) {
            this.addConclusion(!conclusions[i], this.base_module + this.moduleFieldSeparator + conclusions[i]);
        }
        return this;
    };

    DecisionTable.prototype.setRuleset = function(ruleset) {
        var i, j,
            condition_column_helper = {},
            conclusion_column_helper = {},
            aux,
            conditions, conclusions, errorHTML = "", auxHTML = {}, auxKey;

        //fill the column helper for conditions
        for(i = 0; i < this.conditions.length; i+=1) {
            if(!condition_column_helper[this.conditions[i].select.value]) {
                condition_column_helper[this.conditions[i].select.value] = [i];
            } else {
                condition_column_helper[this.conditions[i].select.value].push(i);
            }
        }

        conclusion_column_helper.result = 0;
        for(i = 1; i < this.conclusions.length; i+=1) {
            conclusion_column_helper[this.conclusions[i].select.value] = i
        }

        for(i = 0; i < ruleset.length; i+=1) {
            conditions = ruleset[i].conditions;
            aux = {};
            for(j = 0; j < conditions.length; j+=1) {
                auxKey = conditions[j].variable_module + this.moduleFieldSeparator + conditions[j].variable_name;
                if(typeof aux[auxKey] === 'undefined') {
                    aux[auxKey] = -1;
                }
                aux[auxKey] +=1;
                if(typeof condition_column_helper[auxKey] !== 'undefined') {
                    this.conditions[condition_column_helper[auxKey][aux[auxKey]]].addValue(conditions[j].value, conditions[j].condition);
                } else {
                    if (!auxHTML[conditions[j].variable_module]) {
                        auxHTML[conditions[j].variable_module] = {};
                    }
                    auxHTML[conditions[j].variable_module][conditions[j].variable_name] = 0;
                }
            }

            conclusions = ruleset[i].conclusions;
            for(j = 0; j < conclusions.length; j+=1) {
                auxKey = (conclusions[j].conclusion_type === "return" ? "result" : this.base_module 
                    + this.moduleFieldSeparator + conclusions[j].conclusion_value); 
                if(typeof conclusion_column_helper[auxKey] !== 'undefined') {
                    this.conclusions[conclusion_column_helper[auxKey]].addValue(conclusions[j].value);
                } else {
                    if (!auxHTML[this.base_module]) {
                        auxHTML[this.base_module] = {};
                    }
                    auxHTML[this.base_module][auxKey] = 0;
                }
            }

            this.addDecisionRow();
        }

        for (i in auxHTML) {
            if (auxHTML.hasOwnProperty(i)) {
                for (j in auxHTML[i]) {
                    errorHTML += ", " + i + "::" + j;
                }
            }
        }
        errorHTML = errorHTML.slice(2);
        if(errorHTML) {
            aux = this.html.parentElement;
            $(this.html).remove();
            auxHTML = this.createHTMLElement('p');
            auxHTML.textContent = this.language.ERROR_NOT_EXISTING_FIELDS.replace(/\%s/, errorHTML);
            this.html = this.createHTMLElement('div');
            this.html.appendChild(auxHTML);
            aux.appendChild(this.html);
            App.alert.show(null, {
                level: "error",
                messages: this.html.textContent
            });
        } else {
            this.correctlyBuilt = true;
        }

        this.updateDimensions();
        return this;
    };

    DecisionTable.prototype.isDOMNodeInsertedSupported = function() {
        var div = this.createHTMLElement('div'), supported = false;
        div.addEventListener('DOMNodeInserted', function() { supported = true; });
        div.appendChild(div.cloneNode());

        return supported;
    };

    DecisionTable.prototype.setRows = function(rows) {
        this.rows = parseInt(rows, 10);
        return this.updateDimensions();
    };

    DecisionTable.prototype.setWidth = function(w) {
        this.width = w;
        return this.updateDimensions();
    };

    DecisionTable.prototype.updateDimensions = function() {
        if(!this.html) {
            return this;
        }
        var w, w_cond, w_conc, index_w;//, header = $(this.dom.hitTypeLabel.parentElement);
        //console.log("Header: ", header);

        //this.dom.nameLabel.style.display = 'none';

        if(this.width !== 'auto') {
            index_w = $(this.dom.indexTableContainer).outerWidth() + 4;
            w = (this.width - index_w) / (this.conditions.length + this.conclusions.length);
            w_cond = $(this.dom.conditionsTable).css("width", "").outerWidth();
            w_conc = $(this.dom.conclusionsTable).css("width", "").outerWidth();
            w = w_cond + w_conc;
            w_cond = Math.floor(w_cond / w * (this.width - index_w));
            w_conc = this.width - index_w - w_cond;
        } else {
            $(this.dom.conditionsHeader.parentElement).css("width", "").find('th').css("width", "");
            $(this.dom.conclusionsTable).css("width", "");
            $(this.dom.conclusionsHeader.parentElement).css("width", "").find('th').css("width", "");
        }

        this.dom.conditionsTableContainer.style.width = this.dom.conditionsHeaderContainer.style.width = this.width !== 'auto' ? w_cond + "px" : "auto";
        this.dom.conclusionsTableContainer.style.width = this.dom.conclusionsHeaderContainer.style.width = this.width !== 'auto' ? w_conc + "px" : "auto";

        if(this.decisionRows && this.rows) {
            w = $(this.dom.conditionsTable).find("tr").outerHeight();
            this.dom.indexTableContainer.style.height = this.dom.conditionsTableContainer.style.height = this.dom.conclusionsTableContainer.style.height = ((w * this.rows) + 10 + this.rows) + "px";
        } else {
            this.dom.indexTableContainer.style.height = this.dom.conditionsTableContainer.style.height = this.dom.conclusionsTableContainer.style.height = "auto";
        }

        w = $(this.dom.conditionsTable).outerWidth();
        if(w < $(this.dom.conditionsTableContainer).width() && this.width !== 'auto') {
            this.dom.conditionsTable.style.width = "100%";
            w = $(this.dom.conditionsTable).outerWidth();
            w = Math.ceil(w/2) * 2;
        }
        $(this.dom.conditionsHeader.parentElement).css("width", w + "px");
        w = Math.floor(w / this.conditions.length);
        $(this.dom.conditionsHeader).find('th').css("width", w + "px");

        w = $(this.dom.conclusionsTable).outerWidth();
        if(w < $(this.dom.conclusionsTableContainer).width() && this.width !== 'auto') {
            this.dom.conclusionsTable.style.width = "100%";
            w = $(this.dom.conclusionsTable).outerWidth();
            w = Math.ceil(w/2) * 2;
        }
        $(this.dom.conclusionsHeader.parentElement).css("width", w + "px");
        w = Math.floor(w / this.conclusions.length);
        $(this.dom.conclusionsHeader).find("th").css("width", w + "px");

        //w_cond = $(this.dom.hitTypeLabel);
        //w_conc = header.find('.decision-table-module');
        //index = $(this.dom.dirtyIndicator);
        //w = header.width();
        //w -= ( w_cond.innerWidth() + parseInt(w_cond.css("margin-left"))
        //+ parseInt(w_cond.css("margin-right")) + w_conc.innerWidth()
        //+ parseInt(w_conc.css("margin-left")) + parseInt(w_conc.css("margin-right"))
        //+ parseInt($(this.dom.nameLabel).css("margin-right")) + parseInt($(this.dom.nameLabel).css("margin-left"))
        //+ index.width() + parseInt(index.css("margin-left")) + parseInt(index.css("margin-right")));
        //this.dom.nameLabel.style.maxWidth = (w - 25) + 'px';
        //this.dom.nameLabel.style.display = '';

        return this;
    };

    DecisionTable.prototype.createRemoveButton = function() {
        //var input = this.createHTMLElement('input');
        var minusNode = this.createHTMLElement('span');
        minusNode.className = 'icon-minus decision-table-remove';
        //minusNode.innerHTML = '&nbsp;';
        //input.tabIndex = 0;
        //input.type = 'text';
        //input.className = 'decision-table-remove';
        //input.readOnly = true;
        //input.value = '-';
        //input.appendChild(minusNode);
        //input.style.width = '15px';

        return minusNode;
    };

    DecisionTable.prototype.addDecisionRow = function () {
        var row = this.createHTMLElement('tr'), i, aux;

        if(!(this.conditions.length && this.conclusions.length)) {
            return this;
        }

        for(i = 0; i < this.conditions.length; i+=1) {
            if(!this.conditions[i].values[this.decisionRows]) {
                this.conditions[i].addValue();
            }
            aux = this.conditions[i].getValueHTML(this.conditions[i].values.length - 1);
            row.appendChild(aux[0]);
            row.appendChild(aux[1]);
        }
        this.dom.conditionsTable.appendChild(row);

        row = row.cloneNode(false);
        for(i = 0; i < this.conclusions.length; i+=1) {
            if(!this.conclusions[i].values[this.decisionRows]) {
                this.conclusions[i].addValue();
            }
            row.appendChild(this.conclusions[i].getValueHTML(this.conclusions[i].values.length - 1));
        }
        this.dom.conclusionsTable.appendChild(row);

        row = row.cloneNode(false);
        aux = this.createRemoveButton();
        this.decisionRows+=1;
        i = this.createHTMLElement("td");
        i.appendChild(aux);
        row.appendChild(i);
        this.dom.indexTable.appendChild(row);

        if(this.decisionRows === 1) {
            this.updateDimensions();
        }

        if(typeof this.onAddRow === 'function') {
            this.onAddRow.call(this);
        }

        return this;
    };

    DecisionTable.prototype.removeRowWithoutConfirmation = function (index) {
        for(i = 0; i < this.conclusions.length; i+=1) {
            this.conclusions[i].removeValue(index);
        }

        for(i = 0; i < this.conditions.length; i+=1) {
            this.conditions[i].removeValue(index);
        }

        $(this.dom.indexTable).find('tr:eq(' + index + ')').remove();
        $(this.dom.conditionsTable).find('tr:eq(' + index + ')').remove();
        $(this.dom.conclusionsTable).find('tr:eq(' + index + ')').remove();

        this.decisionRows --;
        this.setIsDirty(true);

        valid = this.validateColumn();

        if(typeof this.onChange === 'function') {
            this.onChange.call(this, {}, valid);
        }

        if(typeof this.onRemoveRow === 'function') {
            this.onRemoveRow.call(this);
        }

        return this;
    };

    DecisionTable.prototype.removeDecisionRow = function(index) {
        var i,
            ask = false,
            self = this;

        if(this.decisionRows === 1) {
            App.alert.show('mininal-error', {
                level: 'warning',
                messages: this.language.MIN_ROWS,
                autoClose: true
            });
            return this;
        }

        //Check if there are conditions or conditions filled
        for(i = 0; i < this.conditions.length; i+=1) {
            if(this.conditions[i].values[index].filledValue()) {
                ask = true;
                break;
            }
        }
        if (!ask) {
            for(i = 0; i < this.conclusions.length; i+=1) {
                if(this.conclusions[i].values[index].filledValue()) {
                    ask = true;
                    break;
                }
            }
        }
        if (ask) {
            App.alert.show('message-config-delete-row', {
                level: 'confirmation',
                messages: this.language.MSG_DELETE_ROW,
                onConfirm: function() {
                    return self.removeRowWithoutConfirmation(index);
                },
                onCancel: function() {
                    return this;
                }
            });
        } else {
            return this.removeRowWithoutConfirmation(index);
        }
    };

    DecisionTable.prototype.getFields = function(defaultValue) {
        var self = this;
        if(this.fields.length) {
            return this.fields;
        }
        App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        this.proxy.setUrl('pmse_Project/CrmData/oneToOneRelated/' + this.base_module);
        //this.proxy.setUrl('pmse_Project/CrmData/allRelated/' + this.base_module);
        //this.proxy.setUrl('pmse_Project/CrmData/fields/' + this.base_module);
        this.proxy.getData( null, {
            success: function(data) {
                var i, j, fields, combos, module;
                if(data && data.success) {
                    fields = [];
                    combos = {};
                    for(i = 0; i < data.result.length; i += 1) {
                        module = data.result[i];
                        for (j = 0; j < module.fields.length; j += 1) {
                            fields.push({
                                label: module.fields[j].text,
                                value: module.fields[j].value,
                                type: module.fields[j].type,
                                moduleText: module.text,
                                moduleValue: module.value
                            });
                            //Maybe backend shouldn't send the optionItem field if doesn't apply to the field.
                            if (module.fields[j].optionItem !== "none") {
                                combos[module.value + self.moduleFieldSeparator + module.fields[j].value] = module.fields[j].optionItem;
                            } else if (module.fields[j].type === 'Checkbox') {
                                combos[module.value + self.moduleFieldSeparator + module.fields[j].value] = {
                                    checked: translate('LBL_DROPDOW_CHECKED'),
                                    unchecked: translate('LBL_DROPDOWN_UNCHECKED')
                                };
                            }
                        }
                    }

                    self.fields = fields;
                    self.combos = combos;


                    self.setConditions(self.auxConditions);
                    self.setConclusions(self.auxConclutions);
                    self.setRuleset(self.rules);

                    if(!self.conditions.length) {
                        self.addCondition(defaultValue);
                    }

                    if(!self.conclusions.length) {
                        self.addConclusion(true);
                    }

                    if(!self.decisionRows) {
                        self.addDecisionRow();
                    }
                    App.alert.dismiss('upload');
                    self.setIsDirty(false);
                }

            }
        });

        return this;
    };

    DecisionTable.prototype.setName = function(name) {
        this.name = name;
        return this;
    };

    DecisionTable.prototype.setProxy = function(proxy/*, restClient*/) {
        this.proxy = proxy;
        return this;
    };

    DecisionTable.prototype.setBaseModule = function(base_module) {
        this.base_module = base_module;
        return this;
    };

    DecisionTable.prototype.setHitType = function(hitType) {
        this.hitType = hitType;
        return this;
    };

    DecisionTable.prototype.onRemoveVariableHandler = function(array) {
        var that = this, variablesArray = array, valid;
        return function() {
            var x;
            for(var i = 0; i < variablesArray.length; i+=1) {
                if(variablesArray[i] === this) {
                    x = variablesArray[i];
                    variablesArray.splice(i, 1);
                }
            }
            that.updateDimensions();
            valid = that.validateRow();
            if(typeof that.onRemoveColumn === 'function') {
                that.onRemoveColumn.call(this, x);
            }
            that.setIsDirty(true);
            if(typeof that.onChange === 'function') {
                that.onChange.call(that, {}, valid);
            }
        };
    };


    DecisionTable.prototype.addCondition = function(defaultValue) {

        var condition = new DecisionTableVariable({
            parent: this,
            field: defaultValue || null,
            fields: this.fields,
            combos: this.combos,
            language: this.language
        }), i, html;


        condition.onRemove = this.onRemoveVariableHandler(this.conditions);
        condition.onChangeValue = this.onChangeValueHandler();
        condition.onChange = this.onChangeVariableHandler();
        this.conditions.push(condition);
        if(this.html) {
            this.dom.conditionsHeader.appendChild(condition.getHTML());
        }

        this.proxy.uid = this.base_module || "";

        for(i = 0; i < this.decisionRows; i+=1) {
            condition.addValue();
            html = condition.getValueHTML(i);
            $(this.dom.conditionsTable).find("tr:eq(" + i + ")").append(html[0]).append(html[1]);
        }

        this.updateDimensions();
        this.setIsDirty(true);

        if(typeof this.onAddColumn === 'function') {
            this.onAddColumn.call(this, condition);
        }

        return this;
    };

    DecisionTable.prototype.addConclusion = function (returnType, defaultValue) {
        var conclusion = new DecisionTableVariable({
            isReturnType: returnType,
            variableMode: "conclusion",
            fields: this.fields,
            combos: this.combos,
            field: defaultValue,
            parent: this,
            language: this.language
        }), i;

        conclusion.onRemove = this.onRemoveVariableHandler(this.conclusions);
        conclusion.onChangeValue = this.onChangeValueHandler();
        conclusion.onChange = this.onChangeVariableHandler();
        this.conclusions.push(conclusion);
        if(this.html) {
            this.dom.conclusionsHeader.appendChild(conclusion.getHTML());
        }

        for(i = 0; i < this.decisionRows; i+=1) {
            conclusion.addValue();
            this.dom.conclusionsTable.childNodes[i].appendChild(conclusion.getValueHTML(i));
        }

        this.updateDimensions();
        this.setIsDirty(true);
        if(typeof this.onAddColumn === 'function') {
            this.onAddColumn.call(this, conclusion);
        }

        return this;
    };

    DecisionTable.prototype.canBeRemoved = function(obj) {
        var res = false;
        if(obj.parent === this) {
            if(obj.variableMode === 'condition') {
                res = this.conditions.length > 1;
                if(!res) {
                    App.alert.show('mininal-column-error', {
                        level: 'warning',
                        messages: this.language.MIN_CONDITIONS_COLS,
                        autoClose: true
                    });
                }
            } else if (obj.variableMode === 'conclusion') {
                res = this.conclusions.length > 1;
                if(!res) {
                    App.alert.show('mininal-column-error', {
                        level: 'warning',
                        messages: this.language.MIN_CONCLUSIONS_COLS,
                        autoClose: true
                    });
                }
            }
        }
        return res;
    };

    DecisionTable.prototype.createHTML = function() {
        if(this.html) {
            return this.html;
        }

        var table, row, cell, header, body, textContainer, subtable, button, i, span;

        //create the table header
        header = this.createHTMLElement('thead');
        //row = this.createHTMLElement('tr');
        //cell = this.createHTMLElement('th');
        //cell.className = 'decision-table-title';
        //cell.colSpan = 3;
        //textContainer = this.createHTMLElement('div');
        //span = this.createHTMLElement('span');
        //span.appendChild(document.createTextNode((this.hitType === 'single' ? "[" + this.language.SINGLE_HIT + "]" : "[" + this.language.MULTIPLE_HIT + "]")));
        //span.className = 'decision-table-type';
        //span.title = this.language.CLICK_TO_EDIT;
        //span.tabIndex = 0;
        //this.dom.hitTypeLabel = span;
        //textContainer.appendChild(span);
        //span = span.cloneNode(false);
        //span.appendChild(document.createTextNode(this.name));
        //span.className = 'decision-table-name';
        //span.title = this.language.CLICK_TO_EDIT;
        //this.dom.nameLabel = span;
        //textContainer.appendChild(span);
        //span = span.cloneNode(false);
        //span.title = "";
        //$(span).removeAttr("tabIndex");
        //span.textContent = this.isDirty ? '*' : '';
        //textContainer.appendChild(span);
        //this.dom.dirtyIndicator = span;
        //span = span.cloneNode(false);
        //span.className = 'decision-table-module';
        //span.appendChild(document.createTextNode(this.base_module));
        //textContainer.appendChild(span);
        //
        //cell.appendChild(textContainer);
        //row.appendChild(cell);
        //header.appendChild(row);
        //this.dom.title = cell;

        plusNode = this.createHTMLElement('span');
        plusNode.className = 'icon-plus';
        plusNode2 = this.createHTMLElement('span');
        plusNode2.className = 'icon-plus';

        //create the table subheaders
        row = this.createHTMLElement('tr');
        cell = this.createHTMLElement('th');
        row.appendChild(cell);
        cell = this.createHTMLElement('th');
        button = this.createHTMLElement('button');
        button.appendChild(plusNode);
        button.className = 'decision-table-add-button';
        button.title = this.language.LBL_ADD_CONDITION;
        this.dom.addConditionButton = button;
        textContainer = this.createHTMLElement('span');
        textContainer.appendChild(document.createTextNode(this.language.CONDITIONS));
        textContainer.appendChild(button);
        cell.appendChild(textContainer);
        cell.className = 'decision-table-separator-border';
        row.appendChild(cell);
        cell = cell.cloneNode(false);
        button = button.cloneNode(true);
        button.title = this.language.LBL_ADD_CONCLUSION;
        this.dom.addConclusionButton = button;
        textContainer = textContainer.cloneNode(false);
        textContainer.appendChild(document.createTextNode(this.language.CONCLUSIONS));
        textContainer.appendChild(button);
        cell.appendChild(textContainer);
        row.appendChild(cell);
        header.appendChild(row);

        //create the body and the body header
        row = this.createHTMLElement("tr");
        cell = this.createHTMLElement('th');
        textContainer = this.createHTMLElement('button');
        textContainer.appendChild(plusNode2);
        textContainer.title = this.language.ADD_ROW;
        textContainer.className = 'decision-table-add-row';
        cell.appendChild(textContainer);
        row.appendChild(cell);
        cell = this.createHTMLElement('th');
        textContainer = this.createHTMLElement('div');
        textContainer.className = 'decision-table-conditions-header';
        this.dom.conditionsHeaderContainer = textContainer;
        subtable = this.createHTMLElement('table');
        subtable.appendChild(row.cloneNode(false));
        textContainer.appendChild(subtable);
        this.dom.conditionsHeader = subtable.childNodes[0];
        cell.className = 'decision-table-separator-border';
        cell.appendChild(textContainer);
        row.appendChild(cell);
        cell = cell.cloneNode(true);
        this.dom.conclusionsHeaderContainer = cell.childNodes[0];
        this.dom.conclusionsHeaderContainer.className = "decision-table-conclusions-header";
        this.dom.conclusionsHeader = this.dom.conclusionsHeaderContainer.childNodes[0].childNodes[0];
        row.appendChild(cell);
        body = this.createHTMLElement('tbody');
        body.appendChild(row);

        //create the cells in body that will contain the tables for data
        row = this.createHTMLElement('tr');
        cell = this.createHTMLElement('td');
        textContainer = textContainer.cloneNode(false);
        textContainer.className = 'decision-table-container';
        this.dom.indexTableContainer = textContainer;
        subtable = subtable.cloneNode(false);
        subtable.className = 'decision-table-index';
        this.dom.indexTable = subtable;
        textContainer.appendChild(subtable);
        cell.appendChild(textContainer);
        row.appendChild(cell);
        cell = cell.cloneNode(true);
        this.dom.conditionsTable = (this.dom.conditionsTableContainer = cell.childNodes[0]).childNodes[0];
        this.dom.conditionsTable.className = 'decision-table-conditions';
        cell.className = 'decision-table-separator-border';
        row.appendChild(cell);
        cell = cell.cloneNode(true);
        cell.className = "";
        this.dom.conclusionsTable = (this.dom.conclusionsTableContainer = cell.childNodes[0]).childNodes[0];
        //$(this.dom.conclusionsTableContainer).addClass("decision-table-scroll-x");
        this.dom.conclusionsTable.className = 'decision-table-conclusions';
        row.appendChild(cell);
        body.appendChild(row);

        //create the table and append the header and body
        table = this.createHTMLElement('table');
        table.className = "decision-table";
        table.appendChild(header);
        table.appendChild(body);

        this.html = table;

        for(i = 0; i < this.conditions.length; i+=1) {
            this.dom.conditionsHeader.appendChild(this.conditions[i].getHTML());
        }

        for(i = 0; i < this.conclusions.length; i+=1) {
            this.dom.conclusionsHeader.appendChild(this.conclusions[i].getHTML());
        }

        this.setShowDirtyIndicator(this.showDirtyIndicator);

        this.attachListeners();

        return this.html;
    };

    DecisionTable.prototype.attachListeners = function() {
        var that = this;
        $(this.dom.conditionsTableContainer).on('scroll', function(){
            that.globalCBControl.close();
            that.globalDDSelector.close();
            that.dom.conditionsHeaderContainer.scrollLeft = this.scrollLeft;
            that.dom.conclusionsTableContainer.scrollTop = this.scrollTop;
        });

        $(this.dom.conditionsHeaderContainer).on('scroll', function() {
            that.dom.conditionsTableContainer.scrollLeft = this.scrollLeft;
        });

        $(this.dom.conclusionsTableContainer).add(this.dom.conclusionsHeaderContainer).on('scroll', function(){
            that.globalCBControl.close();
            that.globalDDSelector.close();
            that.dom.conclusionsHeaderContainer.scrollLeft = that.dom.conclusionsTableContainer.scrollLeft = this.scrollLeft;
            that.dom.indexTableContainer.scrollTop = that.dom.conditionsTableContainer.scrollTop = this.scrollTop;
        });

        $(this.dom.indexTableContainer).on('scroll', function() {
            that.dom.conditionsTableContainer.scrollTop = that.dom.conclusionsTableContainer.scrollTop = this.scrollTop;
        });

        $(this.dom.addConclusionButton).on('click', function() {
            that.addConclusion();
        });

        $(this.dom.addConditionButton).on('click', function() {
            that.addCondition();
        });

    //    $(this.dom.indexTable).on('click', 'span', function() {
    //        that.removeDecisionRow($(that.dom.indexTable).find("span").index(this));
    //    });
        $(this.dom.indexTable).on('click', 'span.decision-table-remove', function() {
            that.removeDecisionRow($(that.dom.indexTable).find("span.decision-table-remove").index(this));
        });

        $(this.dom.conditionsTable).on('keydown', 'td', function(e) {
            var index, row = this.parentElement;
            if(e.keyCode === 9) {
                index = $(row.parentElement).find("tr").index(row);
                if($(row).find("td:last").get(0) === this && !e.shiftKey) {
                    e.preventDefault();
                    $(that.conclusions[0].getValueHTML(index)).find("span").focus();
                } else if($(row).find("td:first").get(0) === this && e.shiftKey) {
                    e.preventDefault();
                    $(that.dom.indexTable).find("span").eq(index).focus();
                }
            }
        });

        $(this.dom.indexTable).on("keydown", "td", function(e) {
            var index, row = this.parentElement;
            if(e.keyCode === 9) {
                index = $(row.parentElement).find("tr").index(row);
                if(!e.shiftKey) {
                    e.preventDefault();
                    $(that.conditions[0].getValueHTML(index)[0]).find("span").focus();
                } else if(index > 0){
                    e.preventDefault();
                    $(that.conclusions[that.conclusions.length - 1].getValueHTML(index - 1)).find("span").focus();
                }
            }
        });

        $(this.dom.conclusionsTable).on("keydown", "td", function(e) {
            var index, row = this.parentElement;
            if(e.keyCode === 9) {
                index = $(row.parentElement).find("tr").index(row);
                if($(row).find("td:last").get(0) === this && !e.shiftKey && index < that.decisionRows - 1) {
                    e.preventDefault();
                    $(that.dom.indexTable).find("span").eq(index + 1).focus();
                } else if($(row).find("td:first").get(0) === this && e.shiftKey) {
                    e.preventDefault();
                    $(that.conditions[that.conditions.length - 1].getValueHTML(index)[1]).find("span").focus();
                }
            }
        });

        $(this.dom.conditionsTable).on('keydown', 'td', function(e) {
            var index, row = this.parentElement;
            if(e.keyCode === 9) {
                index = $(row.parentElement).find("tr").index(row);
                if($(row).find("td:last").get(0) === this && !e.shiftKey) {
                    e.preventDefault();
                    $(that.conclusions[0].getValueHTML(index)).find("span").focus();
                } else if($(row).find("td:first").get(0) === this && e.shiftKey) {
                    e.preventDefault();
                    $(that.dom.indexTable).find("button").eq(index).focus();
                }
            }
        });

        $(this.dom.indexTable).on('keydown', 'td', function(e) {
            var index, row = this.parentElement;
            if(e.keyCode === 9) {
                index = $(row.parentElement).find("tr").index(row);
                if($(row).find("td:last").get(0) === this && !e.shiftKey) {
                    e.preventDefault();
                    $(that.conditions[0].getValueHTML(index)[0]).find("span").focus();
                } else if($(row).find("td:first").get(0) === this && e.shiftKey && index > 0){
                    e.preventDefault();
                    $(that.conclusions[that.conclusions.length - 1].getValueHTML(index - 1)).find('span').focus();
                }
            }
        });

        $(this.dom.conclusionsTable).on('keydown', 'td', function(e) {
            var index, row = this.parentElement;
            if(e.keyCode === 9) {
                index = $(row.parentElement).find("tr").index(row);
                if($(row).find("td:last").get(0) === this && !e.shiftKey && index < that.decisionRows - 1) {
                    e.preventDefault();
                    $(that.dom.indexTable).find("button").eq(index + 1).focus();
                } else if($(row).find("td:first").get(0) === this && e.shiftKey) {
                    e.preventDefault();
                    $(that.conditions[that.conditions.length - 1].getValueHTML(index)[1]).find("span").focus();
                }
            }
        });

        $(this.html).find('.decision-table-add-row').on('click', function() {
            that.addDecisionRow();
        });

        //$(this.dom.nameLabel).on('focus', function() {
        //    var input = that.createHTMLElement('input');
        //    input.type = 'text';
        //    input.value = that.name;
        //    $(this).empty().append(input);
        //    $(input).select().focus();
        //}).on('blur', 'input', function() {
        //    var name = that.name, value = $.trim(this.value);
        //    if(value) {
        //        that.name = value;
        //    }
        //    if(name != that.name) {
        //        that.setIsDirty(true);
        //    }
        //    $(this.parentElement).text(that.name);
        //});

        //$(this.dom.hitTypeLabel).on('focus', function() {
        //    var select = that.createHTMLElement('select'),
        //        option = that.createHTMLElement('option');
        //
        //    option.label = that.language.MULTIPLE_HIT;
        //    option.value = 'multiple';
        //    option.appendChild(document.createTextNode(option.label));
        //    option.selected = that.hitType !== 'single';
        //    select.appendChild(option);
        //
        //    option = option.cloneNode(false);
        //    option.label = that.language.SINGLE_HIT;
        //    option.value = 'single';
        //    option.appendChild(document.createTextNode(option.label));
        //    option.selected = that.hitType === 'single';
        //    select.appendChild(option);
        //
        //    $(this).empty().append(select);
        //    $(select).focus();
        //}).on('blur', 'select', function() {
        //    var prevValue = that.hitType;
        //    that.hitType = this.value;
        //    $(this.parentElement).text(that.hitType === 'single' ? '[' + that.language.SINGLE_HIT + ']' : '[' + that.language.MULTIPLE_HIT + ']');
        //    if(prevValue !== this.value) {
        //        that.setIsDirty(true);
        //    }
        //});

        $(this.dom.conditionsTable).add(this.dom.conclusionsTable).add(this.dom.indexTable).on("focus", "td", function() {
            var row = this.parentElement, index;
            $(that.html).find("tr.cell-edit").removeClass("cell-edit");
            index = $(row.parentElement).find("tr").index(row);
            $(that.dom.indexTable.childNodes[index]).add(that.dom.conditionsTable.childNodes[index]).add(that.dom.conclusionsTable.childNodes[index]).addClass("cell-edit");
        }).on("blur", "select, input", function(){
            //$(that.html).find("tr.cell-edit").removeClass("cell-edit");
        });

        $(document).bind('DOMNodeInserted', function(e) {
            if(e.target === that.html) {
                that.updateDimensions();
            }
        });

        return this;
    };

    DecisionTable.prototype.validateConclusions = function() {
        var i, obj = {};

        for(i = 0; i < this.conclusions.length; i+=1) {
            if(!this.conclusions[i].isReturnType && this.conclusions[i].field && this.conclusions[i].getFilledValuesNum()) {
                if(!obj[this.conclusions[i].field]) {
                    obj[this.conclusions[i].field] = true;
                } else {
                    $(this.conclusions[i].getHTML()).addClass('error');
                    return {
                        valid: false,
                        location: "Conclusion # " + (i + 1),
                        message: this.language.ERROR_CONCLUSION_VAR_DUPLICATED
                    }
                }
            }
            $(this.conclusions[i].getHTML()).removeClass('error');
        }

        return {valid: true};
    };

    DecisionTable.prototype.validateRow = function(index) {
        var start = 0, limit = this.decisionRows,
            rowHasConclusions, rowHasConditions, i, j, defaultRulesets = 0;

        if(typeof index === 'number') {
            start = index;
            limit = index + 1;
        }

        for(i = start; i < limit; i+=1) {
            rowHasConditions = false;
            rowHasConclusions = false;
            //validate if the row has return value conclusion if there are any condition
            for(j = 0; j < this.conditions.length; j+=1) {
                if(this.conditions[j].values[i].filledValue()) {
                    rowHasConditions = true;
                    break;
                }
            }

            if(rowHasConditions) {
                if(!this.conclusions[0].values[i].filledValue()) {
                    $(this.conclusions[0].values[i].getHTML()).addClass("error");
                    return {
                        valid: false,
                        message: this.language.ERROR_EMPTY_RETURN_VALUE,
                        location: "row # " + (i + 1)
                    };
                } else {
                    rowHasConclusions = true;
                }
            }
            $(this.conclusions[0].values[i].getHTML()).removeClass("error");

            if(!rowHasConclusions) {
                for(j = 0; j < this.conclusions.length; j+=1) {
                    if(this.conclusions[j].values[i].filledValue()) {
                        rowHasConclusions = true;
                        break;
                    }
                }
            }
            if(rowHasConclusions && !rowHasConditions) {
                defaultRulesets += 1;
                if(defaultRulesets > 1) {
                    $(this.dom.conditionsTable).find('tr').eq(i).addClass('error');
                    return {
                        valid: false,
                        message: this.language.ERROR_EMPTY_ROW,
                        location: 'row # ' + (i + 1)
                    };
                }
            }
            $(this.dom.conditionsTable).find('tr').eq(i).removeClass('error');
        }

        return {valid: true};
    };

    DecisionTable.prototype.validateColumn = function(index, type) {
        var valid, i, j, variables = [
            {
                type: "condition",
                collection: this.conditions
            }, {
                type: "conclusion",
                collection: this.conclusions
            }
        ];

        $(this.dom.conditionsTable).find('tr').removeClass('error');

        if(typeof index === 'number' && typeof type === 'number') {
            valid = variables[type].collection[index].isValid();
            if(!valid.valid) {
                return {
                    valid: false,
                    message: valid.message,
                    location: variables[type].type + " # " + (index + 1) + (!isNaN(valid.index) ? " - row " + (valid.index + 1) : "")
                };
            }
        } else {
            for(j = 0; j < variables.length; j+=1) {
                for(i = 0; i < variables[j].collection.length; i+=1) {
                    valid = variables[j].collection[i].isValid();
                    if(!valid.valid) {
                        return {
                            valid: false,
                            message: valid.message,
                            location: variables[j].type + " # " + (i + 1) + (!isNaN(valid.index) ? " - row " + (valid.index + 1) : "")
                        };
                    }
                }
            }
        }

        return {valid: true};
    };

    DecisionTable.prototype.isValid = function() {
        var valid;

        if(!this.correctlyBuilt) {
            return {
                valid: false,
                message: this.language.ERROR_INCORRECT_BUILD
            };
        }

        valid = this.validateColumn();

        if(!valid.valid) {
            return valid;
        }
        valid = this.validateRow();
        if(!valid.valid) {
            return valid;
        }

        return this.validateConclusions();
    };

    DecisionTable.prototype.getJSON = function() {
        var json = {
            id: this.id,
            base_module: this.base_module,
            type: this.hitType,
            name: this.name,
            columns: {
                conditions: [],
                conclusions: []
            },
            ruleset: []
        }, ruleset, conditions, conclusions, i, j, obj, defaultRuleSets = 0, auxKey;

        if(!this.isValid().valid) {
            return null;
        }

        //Add the conditions columns evaluating duplications
        obj = {};
        for(j = 0; j < this.decisionRows; j+=1) {
            for(i = 0; i < this.conditions.length; i+=1) {
                if(this.conditions[i].field && this.conditions[i].values[j].getValue().length) {
                    auxKey = this.conditions[i].module + this.moduleFieldSeparator + this.conditions[i].field;
                    if(!obj[auxKey]) {
                        obj[auxKey] = {
                            max: 0,
                            current: 0
                        };
                    }
                    obj[auxKey].current += 1;
                    if(obj[auxKey].current > obj[auxKey].max) {
                        obj[auxKey].max = obj[auxKey].current;
                    }
                }
            }
            for(i in obj) {
                obj[i].current = 0;
            }
        }
        for(i = 0; i < this.conditions.length; i+=1) {
            auxKey = this.conditions[i].module + this.moduleFieldSeparator + this.conditions[i].field;
            if(obj[auxKey]) {
                for(j = 0; j < obj[auxKey].max; j+=1) {
                    json.columns.conditions.push({
                        module: this.conditions[i].module,
                        field: this.conditions[i].field
                    });
                }
                delete obj[auxKey];
            }
        }


        for(i = 0; i < this.conclusions.length; i+=1) {
            if(this.conclusions[i].isReturnType || (this.conclusions[i].field && this.conclusions[i].getFilledValuesNum())) {
                json.columns.conclusions.push(this.conclusions[i].select ? this.conclusions[i].field : "");
            }
        }

        for(i = 0; i < this.decisionRows; i+=1) {
            ruleset = {
                id: i + 1
            };
            conditions = [];
            conclusions = [];
            for(j = 0; j < this.conditions.length; j+=1) {
                obj = this.conditions[j].getJSON(i);
                if(obj) {
                    conditions.push(obj);
                }
            }
            for(j = 0; j < this.conclusions.length; j+=1) {
                obj = this.conclusions[j].getJSON(i);
                if(obj.value.length) {
                    conclusions.push(obj);
                }
            }
            ruleset.conditions = conditions;
            ruleset.conclusions = conclusions;
            if(!conditions.length) {
                defaultRuleSets += 1;
            }
            if(conditions.length || defaultRuleSets <= 1) {
                json.ruleset.push(ruleset);
            }
        }

        return json;
    };

//DecisionTableVariable
    var DecisionTableVariable = function(options) {
        Element.call(this);

        this.parent = null;

        this.fieldName = null;
        this.field = null;
        this.fieldType = null;
        this.module = null;

        this.values = [];
        this.fields = null;
        this.combos = {};

        this.variableMode = null;
        this.isReturnType = null;
        this.closeButton = null;

        this.select = null;

        this.onRemove = null;
        this.onChange = null;
        this.onChangeValue = null;

        this.language = {};
        DecisionTableVariable.prototype.initObject.call(this, options);
    };

    DecisionTableVariable.prototype = new Element();

    DecisionTableVariable.prototype.initObject = function(options) {
        var defaults = {
            parent: null,

            field: null,

            fields: [],
            combos: {},

            variableMode: "condition",
            isReturnType: false,

            onRemove: null,
            onChange: null,
            onChangeValue: null,

            language: {}
        };

        $.extend(true, defaults, options);

        this.language = defaults.language;
        this.parent = defaults.parent;
        this.variableMode = defaults.variableMode;
        this.isReturnType = defaults.isReturnType;

        this.setFields(defaults.fields)
            .setCombos(defaults.combos)
            .setField(defaults.field);

        if (defaults.values) {
            this.setValues(defaults.values);
        }

        this.onRemove = defaults.onRemove;
        this.onChange = defaults.onChange;
        this.onChangeValue = defaults.onChangeValue;

    };

    DecisionTableVariable.prototype.setField = function (newField) {
        var i,
            currentField,
            field,
            module,
            moduleFieldConcat;

        if (this.isReturnType) {
            return this;
        }

        if (newField) {
            if (typeof newField === 'string') {
                moduleFieldConcat = newField;
                field = newField.split(this.parent.moduleFieldSeparator);
                module = field[0];
                field = field[1];    
            } else {
                module = newField.module;
                field = newField.field;
                moduleFieldConcat = module + this.parent.moduleFieldSeparator + field;
            }
            for (i = 0; i < this.fields.length; i += 1) {
                currentField = this.fields[i];
                if (currentField.value === field && currentField.moduleValue === module) {
                    this.field = field;
                    this.fieldName = currentField.label;
                    this.fieldType = currentField.type;
                    this.module = module;
                    this.select.value = moduleFieldConcat;
                    return this;
                }
            }
        } else {
            this.field = null;
            this.fieldName = null;
            this.fieldType = null;
            this.module = null;
        }
        if (this.select) {
            this.select.selectedIndex = -1;
        }

        return this;
    };

    DecisionTableVariable.prototype.setFields = function(fields) {
        if(fields.push && fields.pop) {
            this.fields = fields;
            if (!this.isReturnType) {
                this.populateSelectElement();
            }
        }
        return this;
    };

    DecisionTableVariable.prototype.setCombos = function (combos) {
        this.combos = combos;
        return this;
    };

    DecisionTableVariable.prototype.populateSelectElement = function() {
        var i,
            currentGroup,
            optgroup,
            option,
            select,
            label;

        if (this.select) {
            $(this.select).empty();
        }

        select = this.createHTMLElement('select');

        if (this.fields.length) {

            //Create first option
            option = this.createHTMLElement('option');
            select.appendChild(option);

            currentGroup = {};

            for(i = 0; i < this.fields.length; i += 1) {
                if (this.fields[i].moduleText !== currentGroup.label) {
                    if (this.variableMode === 'conclusion' && this.fields[i].moduleValue !== this.parent.base_module) {
                        break;
                    }
                    currentGroup = document.createElement("optgroup");
                    currentGroup.label = this.fields[i].moduleText;
                    select.appendChild(currentGroup);
                }
                option = this.createHTMLElement('option');
                label = SUGAR.App.lang.get(this.fields[i].label, this.base_module);
                option.label = label;
                option.value = this.fields[i].moduleValue + this.parent.moduleFieldSeparator + this.fields[i].value;
                option.appendChild(document.createTextNode(label));
                if(this.field === option.value) {
                    option.selected = true;
                }
                currentGroup.appendChild(option);
            }
        }
        this.select = select;

        return this;
    };

    DecisionTableVariable.prototype.setValues = function(values) {
        var i;

        if (typeof values !== "object" || !values.push) {
            return this;
        }

        i = 0;
        if(this.variableMode === 'conclusion') {
            for(i = 0; i < values.length; i += 1) {
                if (typeof values[i] === "string" || typeof values[i] === 'number') {
                    this.values.push(new DecisionTableSingleValue({
                        value: values[i],
                        parent: this,
                        fields: this.fields
                    }));
                }
            }
        } else {
            for(i = 0; i < values.length; i += 1) {
                this.values.push(new DecisionTableValueEvaluation({
                    value: values[i].value,
                    operator: values[i].operator,
                    parent: this,
                    fields: this.fields,
                    language: this.language
                }));
            }
        }
        return this;
    };

    //DecisionTableVariable.prototype.setName = function(name) {
    //    this.name = name;
    //    return this;
    //};



    DecisionTableVariable.prototype.getValueHTML = function(index) {
        if(this.values[index]) {
            return this.values[index].getHTML();
        }

        return null;
    };

    DecisionTableVariable.prototype.createHTML = function() {
        var html = this.createHTMLElement('th'),
            content,
            closeButton;

        if(this.html) {
            return this.html;
        }

        if(this.isReturnType) {
            content = this.createHTMLElement('span');
            content.className = 'decision-table-return';
            content.appendChild(document.createTextNode(
                this.isReturnType ? this.language.LBL_RETURN : (this.fieldName || "")
            ));
        } else {
            content = this.select;
        }

        html.appendChild(content);

        if(!this.isReturnType) {
            closeButton = this.createHTMLElement("button");
            closeButton.appendChild(document.createTextNode(" "));
            closeButton.className = 'decision-table-close-button';
            //TODO Create this label with the text 'Remove Column'
            closeButton.title = this.language.LBL_TITLE_CLOSE_BUTTON;
            this.closeButton = closeButton;
            html.appendChild(this.closeButton);
        }

        this.html = html;

        this.attachListeners();

        return this.html;
    };

    DecisionTableVariable.prototype.removeWithoutConfirmation = function () {
        while(this.values.length) {
            this.values[0].remove();
        }
        this.values = null;
        $(this.html).remove();
        if(typeof this.onRemove === 'function') {
            this.onRemove.call(this);
        }
    };


    DecisionTableVariable.prototype.remove = function() {
        var self = this;
        if(!this.parent.canBeRemoved(this)) {
            return;
        }
        if(this.getFilledValuesNum()) {
            App.alert.show('variable-check', {
                level: 'confirmation',
                //TODO Create a label to handle this message
                messages: "Do you really want to remove this variable?",
                onCancel: function() {
                    return;
                },
                onConfirm: function () {
                    self.removeWithoutConfirmation();
                }
            });
        } else {
            this.removeWithoutConfirmation();
        }
    };

    DecisionTableVariable.prototype.attachListeners = function() {
        var self = this,
            oldField,
            newField;

        if(!this.html) {
            return this;
        }

        $(this.select).on('change', function(){
            oldField = self.module + self.parent.moduleFieldSeparator  + self.field;
            newField = this.value;

            if (self.hasValues()) {
                App.alert.show('select-change-confirm', {
                    level: 'confirmation',
                    messages: 'Values associated to this variable will be removed. Do you want to continue?',
                    autoClose: false,
                    onConfirm: function () {
                        self.setField(newField || null);
                        self.clearAllValues();

                        if (typeof self.onChange === 'function') {
                            self.onChange.call(self, self.field, oldField);
                        }
                    },
                    onCancel: function () {
                        self.select.value  = oldField;
                    }
                });
            } else {
                self.setField(this.value || null);

                if (typeof self.onChange === 'function') {
                    self.onChange.call(self, self.field, oldField);
                }
            }
        });

        $(this.closeButton).on("click", function() {
            self.remove();
        });

        return this;
    };

    DecisionTableVariable.prototype.clearAllValues = function () {
        var i;
        for (i = 0; i < this.values.length; i += 1) {
            this.values[i].clear();
        }
        return this;
    };

    DecisionTableVariable.prototype.hasValues = function () {
        return (this.getFilledValuesNum() !== 0);
    };

    DecisionTableVariable.prototype.getFilledValuesNum = function() {
        var i,
            n = 0;

        for(i = 0; i < this.values.length; i+=1) {
            if(this.values[i].filledValue()) {
                n +=1;
            }
        }
        return n;
    };

    DecisionTableVariable.prototype.onRemoveValueHandler = function() {
        var that = this;
        return function() {
            var i;
            for(i = 0; i < that.values.length; i+=1) {
                if(that.values[i] === this) {
                    that.values.splice(i, 1);
                    return;
                }
            }
        };
    };

    DecisionTableVariable.prototype.onChangeValueHandler = function() {
        var that = this;
        return function(newVal, oldVal) {
            if(typeof that.onChangeValue === 'function') {
                that.onChangeValue.call(that, this, newVal, oldVal);
            }
        };
    };

    DecisionTableVariable.prototype.addValue = function(value, operator) {
        var value;
        if(this.variableMode === 'conclusion') {
            value = new DecisionTableSingleValue({value: value, parent: this, fields: this.fields, language: this.language});
        } else {
            value = new DecisionTableValueEvaluation({value: value, operator: operator, parent: this, fields: this.fields, language: this.language});
        }
        value.onRemove = this.onRemoveValueHandler();
        value.onChange = this.onChangeValueHandler();
        this.values.push(value);

        return this;
    };

    DecisionTableVariable.prototype.getJSON = function(index) {
        var json = {};
        if(typeof index === 'number') {
            if(this.values[index]) {

                json.value = this.values[index].getValue();

                if(this.variableMode === 'conclusion') {
                    json.conclusion_value = (this.isReturnType ? 'result' : this.field);
                    json.conclusion_type = this.isReturnType ? 'return' : 'variable'; //"expression" type also must be set
                } else {
                    json.variable_name = this.field;
                    json.condition = this.values[index].operator;
                    if(!(!json.value || json.condition) || (!json.value && !json.condition) /*|| (json.value.push && !json.value.length)*/)  {
                        return false;
                    }
                }

                if (!this.isReturnType) {
                    json.variable_module = this.module;
                }

                return json;
            }
        } else {
            return false;
        }
    };

    DecisionTableVariable.prototype.removeValue = function(index) {
        if(this.values[index]) {
            $(this.values[index].getHTML()).remove();
            this.values.splice(index, 1);
        }

        return this;
    };

    DecisionTableVariable.prototype.isValid = function() {
        var valid = {
            valid: true
        }, i, values = 0, validation;
        $(this.select).parent().removeClass("error");
        if(this.variableMode === 'conclusion') {
            for(i = 0; i < this.values.length; i+=1) {
                validation = this.values[i].isValid();
                if(!validation.valid) {
                    return validation;
                }
                if(this.values[i].value.length) {
                    values +=1;
                }
            }
        } else {
            for(i = 0; i < this.values.length; i+=1) {
                validation = this.values[i].isValid();
                if(this.values[i].operator) {
                    values +=1;
                }
                if(!validation.valid) {
                    valid.valid = false;
                    valid.message = validation.message;
                    valid.index = i;
                    return valid;
                }
            }
        }

        if(values && (this.select && !this.select.value)) {
            $(this.select.parentElement).addClass("error");
            valid = {
                valid: false,
                message: this.language.ERROR_NO_VARIABLE_SELECTED
            };
        }

        return valid;
    };

//Value Cells for DecisionTable
//DecisionTableValue
    var DecisionTableValue = function(settings) {
        Element.call(this, settings);
        this.value = null;
        this.expression = null;
        this.onRemove = null;
        this.onChange = null;
        this.parent = null;
        this.language = {};
        DecisionTableValue.prototype.initObject.call(this, settings);
    };

    DecisionTableValue.prototype = new Element();

    DecisionTableValue.prototype.initObject = function(settings) {
        var defaults = {
            value: [],
            onRemove: null,
            onChange: null,
            parent: null,
            fields: [],
            language: {}
        };
        $.extend(true, defaults, settings || {});
        this.language = defaults.language;
        this.parentElement = defaults.parent;
        this.expression = new ExpressionContainer({
            variables: defaults.fields,
            onChange: this.onChangeExpressionHandler(),
            language: this.language
        }, this);
        this.setValue(defaults.value);
        this.onRemove = defaults.onRemove;
        this.onChange = defaults.onChange;
    };

    DecisionTableValue.prototype.onChangeExpressionHandler = function() {
        var that = this;
        return function(newVal, oldVal) {
            that.value = this.getObject();
            if(typeof that.onChange === 'function') {
                that.onChange.call(that, newVal, oldVal);
            }
        };
    };

    DecisionTableValue.prototype.updateHTML = function() {};

    DecisionTableValue.prototype.clear = function () {
        this.setValue([]);
        return this;
    };

    DecisionTableValue.prototype.setValue = function(value) {
        var i;

        this.expression.setExpressionValue(value);
        this.value = value;

        return this;
    };

    DecisionTableValue.prototype.createHTML = function() {};

    DecisionTableValue.prototype.onEnterCellHandler = function(controlCreationFunction) {
        var that = this;
        return function() {
            if(typeof controlCreationFunction !== 'function') {
                return;
            }
            var control = controlCreationFunction();
            $(this.parentElement).empty().append(control);
            $(control).select().focus();
        };
    };

    DecisionTableValue.prototype.onLeaveCellHandler = function(member) {
        var that = this;
        return function() {
            var span = document.createElement('span'),
                cell = this.parentElement, oldValue = that[member], changed = false;
            span.tabIndex = 0;
            changed = oldValue !== this.value;
            that[member] = this.value;
            if(that[member]) {
                span.appendChild(document.createTextNode(that[member]));
            } else {
                span.innerHTML = '&nbsp;';
            }
            try {
                $(cell).empty().append(span);
            } catch(e){}
            that.isValid();
            if(changed && typeof that.onChange === 'function') {
                that.onChange.call(that, that[member], oldValue);
            }
        };
    };

    DecisionTableValue.prototype.isValid = function() {
        if(this.expression.isValid()) {
            $(this.html).removeClass('error');
            return {
                valid: true
            };
        } else {
            $(this.html).addClass('error');
            return {
                valid: false,
                message: this.language.ERROR_INVALID_EXPRESSION
            }
        }
    };

    DecisionTableValue.prototype.attachListeners = function() {};

    DecisionTableValue.prototype.remove = function() {
        $(this.html).remove();
        this.expression.remove();
        if(typeof this.onRemove === 'function') {
            this.onRemove.call(this);
        }
    };

    DecisionTableValue.prototype.getValue = function() {
        return this.expression.getObject();
    };

    DecisionTableValue.prototype.filledValue = function() {
        return !!this.value.length;
    };

//DecisionTableSingleValue
    var DecisionTableSingleValue = function(settings) {
        DecisionTableValue.call(this, settings);
    };

    DecisionTableSingleValue.prototype = new DecisionTableValue();

    DecisionTableSingleValue.prototype.createValueControl = function() {
        var that = this;
        return function() {
            var input = document.createElement('input');
            input.type = 'text';
            input.value = that.value || "";
            return input;
        };
    };

    DecisionTableSingleValue.prototype.updateHTML = function() {
        if(this.html) {
            if(this.value) {
                $(this.html).find('span').text(this.value);
            } else {
                $(this.html).find('span').html('&nbsp;');
            }
            $(this.html).find('input').val(this.value);
        }
        return this;
    };

    DecisionTableSingleValue.prototype.createHTML = function() {
        if(this.html) {
            return this.html;
        }

        var cell;

        cell = this.createHTMLElement('td');

        //span.tabIndex = 0; //<----remove
        cell.appendChild(this.expression.getHTML());

        this.html = cell;

        //this.attachListeners();

        return cell;
    };

//DecisionTableValueEvaluation
    var DecisionTableValueEvaluation = function(settings) {
        DecisionTableValue.call(this, settings);
        this.operator = null;
        DecisionTableValueEvaluation.prototype.initObject.call(this, settings);
    };

    DecisionTableValueEvaluation.prototype = new DecisionTableValue();

    DecisionTableValueEvaluation.prototype.OPERATORS = ["==", ">=", "<=", ">", "<", "!="/*, "within", "not within"*/];

    DecisionTableValueEvaluation.prototype.initObject = function(settings) {
        this.setOperator(settings.operator || "");
    };

    DecisionTableValueEvaluation.prototype.clear = function () {
        DecisionTableValue.prototype.clear.call(this);
        this.setOperator("");
        return this;
    };

    DecisionTableValueEvaluation.prototype.setOperator = function(operator) {
        this.operator = operator;
        if (this.html && this.html[0]) {
            jQuery(this.html[0]).find('span').empty().append(operator);
        }
        return this;
    };

    DecisionTableValueEvaluation.prototype.createHTML = function () {
        if(this.html) {
            return this.html;
        }

        var valueCell, operatorCell, span;
        valueCell = DecisionTableSingleValue.prototype.createHTML.call(this);

        operatorCell = this.createHTMLElement("td");
        operatorCell.className = 'decision-table-operator';
        span = this.createHTMLElement("span");
        span.tabIndex = 0;
        if(this.operator) {
            span.appendChild(document.createTextNode(this.operator));
        } else {
            span.innerHTML = '&nbsp';
        }
        operatorCell.appendChild(span);

        this.html = [operatorCell, valueCell];

        this.attachListeners();

        return this.html;
    };

    DecisionTableValueEvaluation.prototype.fillOperators = function(select) {
        var i, option, type = this.parentElement.fieldType.toLowerCase(), enabledOperators;

        switch (type) {
            case 'date':
            case 'datetime':
            case 'decimal':
            case 'currency':
            case 'float':
            case 'integer':
                enabledOperators = this.OPERATORS;
                break;
            default:
                enabledOperators = [this.OPERATORS[0], this.OPERATORS[5]];
        }

        $(select).append('<option></option>');

        for(i = 0; i < enabledOperators.length; i+=1) {
            option = this.createHTMLElement("option");
            option.label = option.value = enabledOperators[i];
            option.appendChild(document.createTextNode(enabledOperators[i]));
            option.selected = enabledOperators[i] === this.operator;
            select.appendChild(option);
        }

        return select;
    };

    DecisionTableValueEvaluation.prototype.createValueControl = function() {
        var that = this;
        return function() {
            var input = document.createElement('input');
            input.type = 'text';
            input.value = that.value || "";
            return input;
        };
    };

    DecisionTableValueEvaluation.prototype.createOperatorControl = function() {
        var that = this;
        return function() {
            var select = document.createElement('select');
            that.fillOperators(select);
            select.value = that.operator;
            return select;
        };
    };

    DecisionTableValueEvaluation.prototype.attachListeners = function() {
        if(!this.html || !this.html.push) {
            return this;
        }

        $(this.html[0]).on('focus', 'span', this.onEnterCellHandler(this.createOperatorControl()))
            .on('blur', 'select', this.onLeaveCellHandler('operator'));

        return this;
    };

    DecisionTableValueEvaluation.prototype.filledValue = function() {
        return !!this.operator && DecisionTableValue.prototype.filledValue.call(this);
    };

    DecisionTableValueEvaluation.prototype.isValid = function() {
        var res = DecisionTableValue.prototype.isValid.call(this);

        if(!res.valid) {
            $(this.html[0]).removeClass('error');
        } else {
            res = {
                valid: (!!this.value.length === !!this.operator)
            };
            if(!res.valid) {
                $(this.html).addClass('error');
                res.message = this.language.ERROR_MISSING_EXPRESSION_OR_OPERATOR;
            } else {
                $(this.html).removeClass('error');
            }
        }

        return res;
    };

    DecisionTableValueEvaluation.prototype.getOperator = function() {
        return this.operator;
    };

//This is an abstract class
var DataItem = function(settings) {
	Element.call(this, settings);
	this._parent = null;
	this._data = {};
	this._text = null;
	this._eventListenersAttached = false;
	this._htmlItemContent = null;
	this.onClick = null;
	this._disabled = false;
	DataItem.prototype.init.call(this, settings);
};

DataItem.prototype = new Element();
DataItem.prototype.constructor = DataItem;
DataItem.prototype.type = "DataItem";

DataItem.prototype.init = function(settings) {
	var defaults = {
		data: {},
		onClick: null,
		text: "[item]",
		parent: null,
		disabled: false
	};

	jQuery.extend(true, defaults, settings);

	this.setFullData(defaults.data)
		.setOnClickHandler(defaults.onClick)
		.setText(defaults.text)
		.setParent(defaults.parent);

	if (defaults.disabled) {
		this.disable();
	} else {
		this.enable();
	}
};

DataItem.prototype.disable = function () {
	if (this.html) {
		this.style.addClasses(["adam-disabled"]);
	}
	this._disabled = true;
	return this;
};

DataItem.prototype.enable = function () {
	if (this.html) {
		this.style.removeClasses(["adam-disabled"]);
	}
	this._disabled = false;
	return this;
};

DataItem.prototype.setParent = function (parent) {
	if(!(parent === null || parent instanceof ItemContainer)) {
		throw new Error("setParent(): The parameter must be an instace of ItemContainer or null.");
	}
	this._parent = parent;
	return this;
};

DataItem.prototype.getParent = function () {
	return this._parent;
};

DataItem.prototype._getFinalText = function () {
	var regExpMatch, parts, current, i, finalText, text = this._text;
	if(typeof text === 'string') {
		if(regExpMatch = text.match(/^\{\{([a-zA-z0-9\.\-]+)\}\}$/)) {
			parts = regExpMatch[1].split(".");
			current = this._data;
			for(i = 0; i < parts.length; i++) {
				current = current[parts[i]];
				if(!current) {
					break;
				}
			}
			finalText = current && typeof current !== 'object' ? current : "";
		} else {
			finalText = text;
		}
	} else {
		finalText = this._text(this, this.getData()) || "";
	}
	return finalText;
};

DataItem.prototype.setText = function (text) {
	if(!(typeof text === 'string' || typeof text === 'function')) {
		throw new Error("setText(): The parameter must be a string or function.");
	}
	this._text = text;
	if(this._htmlTextContainer) {
		this._htmlTextContainer.textContent = this._getFinalText();
	}
	return this;
};

DataItem.prototype.getText = function () {
	return this._htmlTextContainer ? this._htmlTextContainer.textContent : this._getFinalText();
};

DataItem.prototype.setOnRemoveHandler = function(handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnRemoveHandler(): The parameter must be a function or null.");
	}
	this.onRemove = handler;
	return this;
};

DataItem.prototype.setOnClickHandler = function(handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnClickHandler(): The parameter must be a function or null.")
	}
	this.onClick = handler;
	return this;
};

DataItem.prototype.clearData = function (key) {
	if(key === undefined) {
		this._data = {};
	} else {
		delete this._data[key];
	}
	return this;
};

DataItem.prototype.setData = function (key, value) {
	this._data[key] = value;
	return this;
};

DataItem.prototype.setFullData = function (data) {
	var key;
	this.clearData();
	for (key in data) {
		if (data.hasOwnProperty(key)) {
			this.setData(key, data[key]);
		}
	}
	if(this._htmlTextContainer) {
		this.setText(this._text);
	}
	return this;
};

DataItem.prototype._onClick = function() {
	var that = this;
	return function(e) {
		e.preventDefault();
		e.stopPropagation();
		if(typeof that.onClick === 'function' && !that._disabled) {
			that.onClick(that);
		}
	};
};

DataItem.prototype.getData = function() {
	var dataObject = {}, key;
	for(key in this._data) {
		dataObject[key] = this._data[key];
	}
	return dataObject;
};

DataItem.prototype._attachListeners = function() {
	if (this.html && !this._eventListenersAttached) {
		jQuery(this._htmlItemContent).on('click', this._onClick());
		this._eventListenersAttached = true;
	}
	return this;
};

DataItem.prototype.createHTML = function () {
	throw new Error("createHTML(): Calling an abstract method in DataItem.");
};
var SingleItem = function (settings) {
	DataItem.call(this, settings);
	this.onRemove = null;
	this._htmlTextContainer = null;
	this._htmlIconContainer = null;
	this._htmlRemoveButton = null;
	this._htmlItemContent = null;
	SingleItem.prototype.init.call(this, settings);
};

SingleItem.prototype = new DataItem();

SingleItem.prototype.constructor = SingleItem;

SingleItem.prototype.init = function (settings) {
	var defaults = {
		onRemove: null,
		removable: true
	};

	jQuery.extend(true, defaults, settings);

	this.setOnRemoveHandler(defaults.onRemove);
};

SingleItem.prototype.disable = function () {
	if (this.html) {
		this._htmlRemoveButton.style.display = "none";
	}
	return DataItem.prototype.disable.call(this);
};

SingleItem.prototype.enable = function () {
	if (this.html) {
		this._htmlRemoveButton.style.display = "";
	}
	return DataItem.prototype.enable.call(this);
};

SingleItem.prototype.setOnRemoveHandler = function(handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnRemoveHandler(): The parameter must be a function or null.");
	}
	this.onRemove = handler;
	return this;
};

SingleItem.prototype._onRemoveButtonClick = function() {
	var that = this;
	return function(e) {
		e.preventDefault();
		e.stopPropagation();
		if(typeof that.onRemove === 'function') {
			that.onRemove(that);
		}
	};
};

SingleItem.prototype._attachListeners = function() {
	if (this.html && !this._eventListenersAttached) {
		DataItem.prototype._attachListeners.call(this);
		jQuery(this._htmlRemoveButton).on('click', this._onRemoveButtonClick());
		jQuery(this.html).on("focus focusin focusout blur", function (e) {
			e.stopPropagation();
		})
		this._eventListenersAttached = true;
	}
	return this;
};

SingleItem.prototype.createHTML = function () {
	var textContainer, iconContainer, removeButton, itemContent;
	if (this.html) {
		return this.html;
	}
	//create the main html element to content all the other object's components.
	this.html = this.createHTMLElement('li');
	this.html.id = this.id;
	this.html.className = 'adam single-item';
	//create the object's components
	textContainer = this.createHTMLElement('span');
	textContainer.className = 'adam single-item-text';
	iconContainer = this.createHTMLElement('span');
	iconContainer.className = 'adam single-item-icon';
	itemContent = this.createHTMLElement('a');
	itemContent.className = 'adam single-item-content';
	itemContent.href = '#';
	removeButton = this.createHTMLElement('a');
	removeButton.href = "#";
	removeButton.className = 'adam single-item-remove icon-remove-sign';
	//append the components to its respective parent elements;
	itemContent.appendChild(iconContainer);
	itemContent.appendChild(textContainer);
	this.html.appendChild(itemContent);
	this.html.appendChild(removeButton);
	//save the references to the components into object's member variables.
	this._htmlTextContainer = textContainer;
	this._htmlIconContainer = iconContainer;
	this._htmlRemoveButton = removeButton;
	this._htmlItemContent = itemContent;

	//Set properties that need html to b executed completly
	this.setText(this._text);

	if (this._disabled) {
		this.disable();
	} else {
		this.enable();
	}

	return this._attachListeners().html;
};

var ListItem = function(settings) {
	DataItem.call(this, settings);
	ListItem.prototype.init.call(this, settings);
};

ListItem.prototype = new DataItem();
ListItem.prototype.constructor = ListItem;
ListItem.prototype.type = "ListItem";

ListItem.prototype.init = function (settings) {
	var defaults = {
		text: "[listitem]"
	};
	jQuery.extend(true, defaults, settings);
	this.setText(defaults.text);
};

ListItem.prototype.setVisible = function (value) {
    if (_.isBoolean(value)) {
        this.visible = value;
        if (this.html) {
            if (value) {
                this.style.addProperties({display: ""});
            } else {
                this.style.addProperties({display: "none"});
            }
        }
    }
    return this;
};

ListItem.prototype.setText = function (text) {
	var finalText;
	if (!(typeof text === 'string' || typeof text === 'function')) {
		throw new Error("setText(): The parameter must be a string or function.");
	}
	this._text = text;
	if (this._htmlItemContent) {
		finalText = this._getFinalText();
		if(isHTMLElement(finalText)) {
			this._htmlItemContent.appendChild(finalText);
		} else {
			this._htmlItemContent.textContent = finalText;
		}
	}
	return this;
};

ListItem.prototype.createHTML = function () {
	if(!this.html) {
		this.html = this.createHTMLElement('li');
		this.html.className = 'adam list-item';
		this._htmlItemContent = this.html;
		this.setText(this._text);
		this._attachListeners();
		this.setVisible(this.visible);
	}
	return this.html;
};
var CloseListItem = function (options) {
    ListItem.call(this, options);
    CloseListItem.prototype.init.call(this, options);
};

CloseListItem.prototype = new ListItem();

CloseListItem.prototype.init = function (options) {
    this.setText(function () {
        var dv = document.createElement("div"),
            icon = document.createElement("span");
        dv.className = 'close-list-item';
        icon.className = 'icon-remove';
        dv.appendChild(icon);
        return dv;
    });
};

CloseListItem.prototype.createHTML = function () {
    ListItem.prototype.createHTML.call(this);
    $(this.html).css('background-color','#A0A0A0');
    return this.html;
};


/**
 * @class ItemContainer
 * Control that will be used as a container for the SingleItems objects.
 */
var ItemContainer = function (settings) {
	Element.call(this, settings);
	this._items = new ArrayList();
	this._massiveAction = false;
	this.onAddItem = null;
	this.onRemoveItem = null;
	this.onSelect = null;
	this.onInputChar = null;
	this._textInputMode = null;
	this._textInputs = new ArrayList();
	this._inputValidationFunction = null;
	this._selectedIndex = null;
	this.onBeforeAddItemByInput = null;
	this._className = null;
	this.onBlur = null;
	this.onFocus = null;
	this._blurTimer = null;
	this._blurred = true;
	this._blurSemaphore = true;
	this._disabled = false;
	ItemContainer.prototype.init.call(this, settings);
};

ItemContainer.prototype = new  Element();
ItemContainer.prototype.constructor = ItemContainer;

ItemContainer.prototype.textInputMode = {
	'NONE': 0,
	'END': 1,
	'ALL': 2
};

ItemContainer.prototype.init = function (settings) {
	var defaults = {
		items: [],
		onAddItem: null,
		onRemoveItem: null,
		width: 200,
		height: 80,
		textInputMode: this.textInputMode.ALL,
		inputValidationFunction: null,
		onBeforeAddItemByInput: null,
		onSelect: null,
		onInputChar: null,
		onBlur: null,
		onFocus: null,
		className: "",
		disbaled: false
	};

	jQuery.extend(true, defaults, settings);

	if (typeof defaults.textInputMode !== 'number') {
		throw new Error("init(): The textInputMode config option must be a number");
	}
	this._textInputMode = defaults.textInputMode;

	this.setWidth(defaults.width)
		.setHeight(defaults.height)
		.setItems(defaults.items)
		.setOnAddItemHandler(defaults.onAddItem)
		.setInputValidationFunction(defaults.inputValidationFunction)
		.setOnBeforeAddItemByInput(defaults.onBeforeAddItemByInput)
		.setOnRemoveItemHandler(defaults.onRemoveItem)
		.setOnSelectHandler(defaults.onSelect)
		.setOnInputCharHandler(defaults.onInputChar)
		.setOnBlurHandler(defaults.onBlur)
		.setOnFocusHandler(defaults.onFocus)
		.setClassName(defaults.className);

	if (defaults.disabled) {
		this.disable();
	} else {
		this.enable();
	}
};

ItemContainer.prototype.getText = function () {
	var i, items = this._items.asArray(), text = "";
	for (i = 0; i < items.length; i += 1) {
		text += " " + items[i].getText();
	}
	return text.substr(1);
};

ItemContainer.prototype.setWidth = function(w) {
	if (!(typeof w === 'number' ||
		(typeof w === 'string' && (w === "auto" || /^\d+(\.\d+)?(em|px|pt|%)?$/.test(w))))) {
		throw new Error("setWidth(): invalid parameter.");
	}
	this.width = w;
    if (this.html) {
        this.style.addProperties({width: this.width});
    }
    return this;
};

ItemContainer.prototype.disable = function () {
	var i, items;
	if (!this._disabled) {
		items = this._items.asArray();

		for (i = 0; i < items.length; i += 1 ) {
			items[i].disable();
		}
		jQuery(this.html).find('input').attr('disabled', true);
		this._disabled = true;
	}

	return true;
};

ItemContainer.prototype.enable = function () {
	if (this._disabled) {
		items = this._items.asArray();

		for (i = 0; i < items.length; i += 1 ) {
			items[i].enable();
		}
		jQuery(this.html).find('input').attr('disabled', false);
		this._disabled = false;
	}
	return this;
};

ItemContainer.prototype.setClassName = function (className) {
	if(typeof className !== 'string') {
		throw new Error("setClassName(): The parameter must be a string.");
	}
	this.style.addClasses([className]);
	return this;
};

ItemContainer.prototype.setOnFocusHandler = function (handler) {
	if (!(handler === null || typeof handler === "function")) {
		throw new Error("setOnFocusHandler(): The parameter must be a function or null.");
	}
	this.onFocus = handler;
	return this;
};

ItemContainer.prototype.setOnBlurHandler = function (handler) {
	if (!(handler === null || typeof handler === "function")) {
		throw new Error("setOnBlurHandler(): The parameter must be a function or null.");
	}
	this.onBlur = handler;
	return this;
};

ItemContainer.prototype.setOnInputCharHandler = function (handler) {
	if (!(handler === null || typeof handler === "function")) {
		throw new Error("setOnInputCharHandler(): The parameter must be a function or null.");
	}
	this.onInputChar = handler;
	return this;
};

ItemContainer.prototype.setOnSelectHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnSelectHandler(): The parameter must be a function or null.");
	}
	this.onSelect = handler;
	return this;
};

ItemContainer.prototype.setOnBeforeAddItemByInput = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnBeforeAddItemByInput(): The parameter must be a function or null.");
	}
	this.onBeforeAddItemByInput = handler;
	return this;
};

ItemContainer.prototype.setInputValidationFunction = function(fn) {
	if(!(fn === null || typeof fn === 'function')) {
		throw new Error("setInputValidationFunction(): The parameter must be a function or null.");
	}
	this._inputValidationFunction = fn;
	return this;
};

ItemContainer.prototype.setOnAddItemHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error('setOnAddItemHandler(): The parameter must be a function or null.');
	}
	this.onAddItem = handler;
	return this;
};

ItemContainer.prototype.setOnRemoveItemHandler = function (handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error('setOnRemoveItemHandler(): The parameter must be a function or null.');
	}
	this.onRemoveItem = handler;
	return this;
};

ItemContainer.prototype._addInputText = function (reference) {
	var input = this.createHTMLElement("input");
	input.className = 'adam item-container-input';
	input.disabled = this._disabled;
	this._textInputs.insert(input);
	if(this.html) {
		if(typeof reference === 'number') {
			reference = this._items.get(reference);
		}
		if(reference && reference instanceof SingleItem) {
			this.html.insertBefore(input, reference.getHTML());
		} else {
			this.html.appendChild(input);	
		}
	}
	return this;
};

ItemContainer.prototype.clearItems = function () {
	jQuery(this.html).empty();
	this._items.clear();
	this._textInputs.clear();
	if (this._textInputMode !== this.textInputMode.NONE) {
		this._addInputText();
	}
	return this;
};

ItemContainer.prototype.isParentOf = function (item) {
	return this === item.getParent();
};

ItemContainer.prototype._paintItem = function (item, index) {
	var referenceItem;
	if (this.html) {
		if(this.isParentOf(item)) {
			if (typeof index === 'number') {
				if (index === 0) {
					jQuery(this.html).prepend(item.getHTML());
				} else if (index < this._items.getSize() - 1) {
					jQuery(this.html).find('.item-container-input').eq(index).before(item.getHTML());
				} else {
					jQuery(this.html).find('.item-container-input').last().before(item.getHTML());
				}
			} else {
				if (this._textInputMode === this.textInputMode.NONE) {
					this.html.appendChild(item.getHTML());	
				} else {
					jQuery(this.html).find('.item-container-input').last().before(item.getHTML());
				}
			}
			if(this._textInputMode === this.textInputMode.ALL) {
				this._addInputText(index || item);
			}
		}	
	}
	return this;
};

ItemContainer.prototype._paintItems = function () {
	var i, items = this._items.asArray();
	if (this.html) {
		if (this._textInputMode === this.textInputMode.ALL) {
	    	this._addInputText();
	    }
    	for(i = 0; i < items.length; i++) {
			this._paintItem(items[i]);
		}
		if(this._textInputMode === this.textInputMode.END) {
			this._addInputText();
		}
	}
	return this;
};

ItemContainer.prototype._onRemoveItemHandler = function () {
	var that = this;
	return function (item) {
		that.removeItem(item);
		if(typeof that.onRemoveItem === 'function') {
			that.onRemoveItem(that, item);
		}
	};
};

ItemContainer.prototype.addItem = function(item, index, noFocusNext) {
	if (!(item instanceof SingleItem || typeof item === "object")) {
		throw new Error("The paremeter must be an object literal or null.");
	}
	if (!(item instanceof SingleItem)) {
		item = new SingleItem(item);
	}
	item.setParent(this);
	item.setOnRemoveHandler(this._onRemoveItemHandler());
	if (typeof index === 'number' && index >= 0) {
		this._items.insertAt(item, index);	
	} else {
		this._items.insert(item);
	}
	
	if (!this._massiveAction) {
		this._paintItem(item, index);
		if(!noFocusNext && jQuery(item.getHTML()).next().get(0) !== jQuery(':focus').get(0)) {
			jQuery(item.getHTML()).next().focus();
		} else {
			this._selectedIndex += 1;
			if (this.html) {
				this.html.scrollTop += this.html.scrollHeight;	
			}
		}
	}

	if (typeof this.onAddItem === 'function') {
		if(typeof index === 'number' && index >= 0) {
			item = this._items.get(index);
		} else {
			item = this._items.get(this._items.getSize() - 1);
		}
		this.onAddItem(this, item, index);
	}
	return this;
};

ItemContainer.prototype.removeItem = function (item) {
	if(this.isParentOf(item)) {
		this._items.remove(item);
		jQuery(item.getHTML()).prev('.item-container-input').remove().end()
			.next().focus()
			.end().remove(); 
	}
	return this;
};

ItemContainer.prototype.setVisible = function (value) {
    if (_.isBoolean(value)) {
        this.visible = value;
        if (this.html) {
            if (value) {
                this.style.addProperties({display: ""});
            } else {
                this.style.addProperties({display: "none"});
            }
        }
    }
    return this;
};

ItemContainer.prototype.setItems = function (items) {
	var i;
	if(!jQuery.isArray(items)) {
		throw new Error("setItems(): The parameter must be an array.");
	}
	this._massiveAction = true;
	this.clearItems();
	for(i = 0; i < items.length; i++) {
		this.addItem(items[i]);
	}
	this._paintItems();
	this._massiveAction = false;
	return this;
};

ItemContainer.prototype.getItems = function () {
	return this._items.asArray();
};

ItemContainer.prototype._getTextWidth = function (text, target) {
	var w, $label, label = this.createHTMLElement("span"), styles = window.getComputedStyle(target);
	label.style.padding = 0;
	label.style.fontFamily = styles.getPropertyValue("font-family");
	label.style.fontSize = styles.getPropertyValue("font-size");
	label.style.fontWeight = styles.getPropertyValue("font-weight");
	label.style.whiteSpace = 'nowrap';
	label.style.display =  "none";
	label.textContent = text.replace(/\s/g, "_");
	target.parentNode.appendChild(label);
	$label = jQuery(label);
	w = $label.outerWidth();
	$label.remove();
	return w;
};

ItemContainer.prototype._isValidInput = function (input) {
	var isValid = true;
	if(typeof this._inputValidationFunction === 'function') {
		isValid = this._inputValidationFunction(this, input);
	}
	return isValid;
};

ItemContainer.prototype.select = function (index) {
	if(this.html && !this._disabled) {
		if(typeof index === 'number') {
			jQuery(this.html).find('.adam.item-container-input').eq(index).focus();	
		} else {
			jQuery(this.html).find('.adam.item-container-input').last().focus();
		}
		if(typeof this.onSelect === 'function') {
			this.onSelect(this);
		}
	}
	return this;
};

ItemContainer.prototype.getData = function () {
	var data = [], items = this._items.asArray();
	for (i = 0; i < items.length; i += 1) {
		data.push(items[i].getData());
	}
	return data;
};

ItemContainer.prototype.getSelectedIndex = function() {
	return this._selectedIndex;
};

ItemContainer.prototype._onBlur = function () {
	var that = this;
	return function () {
		clearInterval(that._blurTimer);
		if(typeof that.onBlur === 'function') {
			that._blurred = true;
			that.onBlur(that);
		}
	};
};

ItemContainer.prototype._attachListeners = function () {
	var that, _tempValue = "";
	if(this.html) {
		that = this;
		jQuery(this.html).on('mousedown', function() {
			if(!that._blurred) {
				that._blurSemaphore = false;
			}
		}).on('click', function () {
			that._blurSemaphore = true;
			that.select();
		}).on('focusin', function(e) {
			//console.log("focusin");
			clearInterval(that._blurTimer);	
			if(that._blurred && typeof that.onFocus === 'function' && that._blurSemaphore) {
				that._blurred = false;
				that.onFocus(that);
			}
		}).on('focusout', function(e) {
			//console.log("focusout");
			//console.log(that._blurSemaphore);
			if (!that._blurred) {
				that._blurTimer = setInterval(that._onBlur(), 20);	
			}
		}).on('focus', '.adam.item-container-input', function () {
			that._selectedIndex = $(this.parentNode).find('input').index(this);
		}).on('click', '.adam.item-container-input', function (e) {
			e.stopPropagation();
		}).on('focusout', '.adam.item-container-input', function (e) {
			if (!that._blurSemaphore) {
				e.stopPropagation();
				that._blurSemaphore = true;
			}
		}).on('keydown', '.adam.item-container-input', function(e) {
			var width, newValue, newItem, index, returnedValue, keyIdentifier;
			switch (e.keyCode) {
				case 37:
				case 39:
					if(e.shiftKey) {
						index = jQuery(that.html).find('.adam.item-container-input').index(this);
						newItem = jQuery(that.html).find('.adam.item-container-input').eq(index + (e.keyCode === 37 ? -1 : 1));
						if(newItem.length) {
							this.value = "";
							this.style.width = "1px";
							newItem.get(0).focus();
						}
					}
					break;
				case 27:
					this.value = "";
					break;
				case 13:
					e.preventDefault();
					if (that._isValidInput(this.value)) {
						newItem = new SingleItem({
							text: this.value
						});
						index = jQuery(that.html).find('.adam.item-container-input').index(this);
						if(typeof that.onBeforeAddItemByInput === 'function') {
							returnedValue = that.onBeforeAddItemByInput(that, newItem, this.value, index);
							if(returnedValue === false) {
								newItem = null;
							} else if (returnedValue instanceof SingleItem) {
								newItem = returnedValue;
							}
						}
						if(newItem instanceof SingleItem) {
							that.addItem(newItem, index);	
						}
						this.value = "";
						this.style.width = "1px";
						this.select();
					} else {
						this.select();
					}
					break;
				default:
					try {
						keyIdentifier = e.originalEvent.keyIdentifier;
						//There's not a keyIdentifier property in IE, so we use the "char" property
						if (keyIdentifier) {
							keyIdentifier = eval('"\\u' + keyIdentifier.replace(/(U\+)/, "") + '"');
						} else if (typeof e.char === 'string') {
							//ie
							keyIdentifier = e.char;
						} else {
							//firefox
							keyIdentifier = String.fromCharCode(e.which);
						}
					} catch(e) {
						keyIdentifier = "";
					}
					if (this.selectionStart !== this.selectionEnd) {
						newValue = String.fromCharCode(e.keyCode);
					} else {
						newValue = this.value + keyIdentifier;
					}
					width = that._getTextWidth(newValue, this) || 1;
					this.style.width = width + "px";
			}			
		}).on("keyup", '.adam.item-container-input', function (e) {
			var keyIdentifier;
			try {
				keyIdentifier = e.originalEvent.keyIdentifier;
				//There's not a keyIdentifier property in IE, so we use the "char" property
				if (keyIdentifier) {
					keyIdentifier = eval('"\\u' + keyIdentifier.replace(/(U\+)/, "") + '"');
				} else if (typeof e.char === 'string') {
					//ie
					keyIdentifier = e.char;
				} else {
					//firefox
					keyIdentifier = String.fromCharCode(e.which);
				}
			} catch(e) {}
			if((keyIdentifier || _tempValue !== this.value) && typeof that.onInputChar === 'function') {
				that.onInputChar(that, keyIdentifier, this.value, e.keyCode);
			}
			_tempValue = this.value;
		}).on("blur", '.adam.item-container-input', function() {
			var value = this.value;
			if(this.value !== "") {
				this.value = "";
				this.style.width = "1px";
			}
			/*if(this.value !== value && typeof that.onInputChar === 'function') {
				that.onInputChar(that, "", this.value);
			}*/
		});
	}
	return this;
};

ItemContainer.prototype.createHTML = function() {
	if (!this.html) {
		this.html = this.createHTMLElement('ul');
		this.html.className = "adam item-container";
		this.style.applyStyle();
		this.style.addProperties({
            left: this.x,
            top: this.y,
            width: this.width,
            height: this.height,
            zIndex: this.zOrder
        });
        this._paintItems();
        this._attachListeners();
        this.setVisible(this.visible);
	}
	return this.html;
};

var FieldPanelItem = function(settings) {
	Element.call(this, settings);
	this._parent = null;
	this.onValueAction = null;
	FieldPanelItem.prototype.init.call(this, settings);
};

FieldPanelItem.prototype = new Element();
FieldPanelItem.prototype.constructor = FieldPanelItem;

FieldPanelItem.prototype.family = "FieldPanelItem";
FieldPanelItem.prototype.type = "FieldPanelItem";

FieldPanelItem.prototype.init = function (settings) {
	var defaults = {
		parent: null,
		onValueAction: null
	};

	jQuery.extend(true, defaults, settings);

	this.setParent(defaults.parent)
		.setOnValueActionHandler(defaults.onValueAction);
};

FieldPanelItem.prototype.setParent = function (parent) {
	if(!(parent === null || parent instanceof FieldPanel)) {
		throw new Error("setParent(): The parameter must be an instance of FieldPanel or null.");
	}
	this._parent = parent;

	return this;
};

FieldPanelItem.prototype.getParent = function () {
	return this._parent;
};

FieldPanelItem.prototype.setVisible = function (value) {
    this.visible = !!value;
    if (this.html) {
        if (value) {
            this.style.addProperties({display: ""});
        } else {
            this.style.addProperties({display: "none"});
        }
    }
    return this;
};

FieldPanelItem.prototype.setOnValueActionHandler = function (handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnValueActionHandler(): The parameter must be a function or null.");
	}
	this.onValueAction = handler;
	return this;
};

FieldPanelItem.prototype._onValueAction = function (anyArgument) {
	if(typeof this.onValueAction === 'function') {
		this.onValueAction(this, this.getValueObject(anyArgument));
	}
	return this;
};

FieldPanelItem.prototype.getValueObject =  function() {
	throw new Error("getValueObject(): Trying to call an abstract method.");
};
var FieldPanelButton = function (settings) {
	FieldPanelItem.call(this, settings);
	this._text = null;
	this._value = null;
	FieldPanelButton.prototype.init.call(this, settings);
};

FieldPanelButton.prototype = new FieldPanelItem();
FieldPanelButton.prototype.constructor = FieldPanelButton;

FieldPanelButton.prototype.type = "FieldPanelButton";

FieldPanelButton.prototype.init = function (settings) {
	var defaults = {
		text: "[button]",
		value: ""
	};

	jQuery.extend(true, defaults, settings);
	this.setText(defaults.text)
		.setValue(defaults.value);
};

FieldPanelButton.prototype.setValue = function (value) {
	if(typeof value !== 'string') {
		throw new Error("setValue(): The parameter must be a string.");
	}
	this._value = value;
	return this;
};

FieldPanelButton.prototype.setText = function (text) {
	if(typeof text !== 'string') {
		throw new Error("setText(): The parameter must be a string.");
	}
	if(this.html) {
		this.html.textContent = text;
	}
	this._text = text;
	return this;
};

FieldPanelButton.prototype._onClickHandler = function() {
	var that = this;
	return function (e) {
		e.preventDefault();
		that._onValueAction();
	};
};

FieldPanelButton.prototype._attachListeners = function () {
	if(this.html) {
		jQuery(this.html).on("click", this._onClickHandler());
	}
	return this;
};

FieldPanelButton.prototype.createHTML = function () {
	if(!this.html) {
		this.html = this.createHTMLElement("a");
		this.html.href = "#";
		this.html.className = "adam field-panel-button btn btn-mini btn-block";
		this.setText(this._text).setVisible(this.visible);
		this._attachListeners();
	}
	return this.html;
};

FieldPanelButton.prototype.getValueObject = function() {
	return {
		text: this._text,
		value: this._value
	};
};
var FieldPanelButtonGroup = function(settings) {
	FieldPanelItem.call(this, settings)
	this._items = new ArrayList();
	this._label = null;
	this._htmlLabel = null;
	this._htmlItemsContainer = null;
	this._massiveAction = false;
	FieldPanelButtonGroup.prototype.init.call(this, settings);
};

FieldPanelButtonGroup.prototype = new FieldPanelItem();
FieldPanelButtonGroup.prototype.constructor = FieldPanelButtonGroup;
FieldPanelButtonGroup.prototype.type = "FieldPanelButtonGroup";

FieldPanelButtonGroup.prototype.init = function(settings) {
	var defaults = {
		items: [],
		label: ""
	};

	jQuery.extend(true, defaults, settings);

	this.setItems(defaults.items)
		.setLabel(defaults.label);
};

FieldPanelButtonGroup.prototype.setLabel = function (label) {
	if (typeof label !== 'string') {
		throw new Error("setLabel(): The parameter must be a string.");
	}
	this._label = label;
	if(this._htmlLabel) {
		if (this._label) {
			jQuery(this.html).prepend(this._htmlLabel);
			this._htmlLabel.textContent = label;
		} else {
			jQuery(this._htmlLabel).remove();
		}
	}
	return this;
};

FieldPanelButtonGroup.prototype.clearItems = function () {
	this._items.clear();
	if (this._htmlItemsContainer) {
		jQuery(this._htmlItemsContainer).empty();
	}
	return this;
};

FieldPanelButtonGroup.prototype._paintItem = function (newButton) {
	var that = this;
	if (!newButton.html) {
		newButton.html = this.createHTMLElement("button");
		newButton.html.className = 'adam field-panel-button-group-button btn btn-mini';
		newButton.html.appendChild(document.createTextNode(newButton.text));
		jQuery(newButton.html).on("click", function() {
			that._onValueAction(newButton);
		});
	}
	this._htmlItemsContainer.appendChild(newButton.html);
	return this;
};

FieldPanelButtonGroup.prototype.addItem = function (item) {
	var newButton = {
		text: item.text || item.value || "[button]",
		value: item.value || item.text || null,
		html: null
	};
	this._items.insert(newButton);
	if (!this._massiveAction && this._htmlItemsContainer) {
		this._paintItem(newButton);
	}
	return this;
};

FieldPanelButtonGroup.prototype._paintItems = function () {
	var i, items;
	if (this.html) {
		items = this._items.asArray();
		for (i = 0; i < items.length; i += 1) {
			this._paintItem(items[i]);
		}
	}
	return this;
};

FieldPanelButtonGroup.prototype.setItems = function (items) {
	var i;
	if(!jQuery.isArray(items)) {
		throw new Error("setItems(): The parameter must be an array.");
	}
	this._massiveAction = true;
	this.clearItems();
	for (i = 0; i < items.length; i += 1) {
		this.addItem(items[i]);
	}
	this._paintItems();
	this._massiveAction = false;
	return this;
};

FieldPanelButtonGroup.prototype.getItems = function () {
	return this._items.asArray();
};

FieldPanelButtonGroup.prototype.getValueObject = function(item) {
	return {
		text: item.text,
		value: item.value
	};
};

FieldPanelButtonGroup.prototype.createHTML = function () {
	if(!this.html) {
		this.html = this.createHTMLElement("div");
		this.html.className = "adam field-panel-button-group";
		this._htmlLabel = this.createHTMLElement("span");
		this._htmlLabel.className = "adam field-panel-button-group-label";
		this._htmlItemsContainer = this.createHTMLElement("div");
		this._htmlItemsContainer.className = "adam field-panel-button-container btn-group";

		this.html.appendChild(this._htmlLabel);
		this.html.appendChild(this._htmlItemsContainer);
		this.setLabel(this._label)._paintItems().setVisible(this.visible);
	}
	return this.html;
};
//This is an abstract class
var CollapsiblePanel = function (settings) {
	FieldPanelItem.call(this, settings);
	this._items = new ArrayList();
	this._title = "";
	this._massiveAction = false;
	this._htmlHeader = null;
	this._htmlTitle = null;
	this._htmlBody = null;
	this._bodyHeight = null;
	this._htmlCollapsibleIcon = null;
	this._htmlTitleContainer = null;
	this._collapsed = null;
	this.onCollapse = null;
	this.onExpand = null;
	this._attachedListeners = null;
	this._initialized = false;
	this._enabledAnimations = null;
	this._disabled = false;
	this._onEnablementStatusChange = null;
	this._headerVisible = false;
	CollapsiblePanel.prototype.init.call(this, settings);
};

CollapsiblePanel.prototype = new FieldPanelItem();
CollapsiblePanel.prototype.constructor = CollapsiblePanel;
CollapsiblePanel.prototype.type = "CollapsiblePanel";

CollapsiblePanel.prototype.init = function (settings) {
	var defaults = {
		title: "[panel]",
		items: [],
		bodyHeight: "auto",
		collapsed: true,
		width: '100%',
		onCollapse: null,
		onExpand: null,
		enabledAnimations: true,
		disabled: false,
		onEnablementStatusChange: null,
		headerVisible: true
	};

	jQuery.extend(true, defaults, settings);

	if (defaults.enabledAnimations) {
		this.enableAnimations();
	} else {
		this.disableAnimations();
	}

	if (defaults.disabled) {
		this.disable();
	} else {
		this.enable();
	}

	this.setWidth(defaults.width)
		.setTitle(defaults.title)
		.setItems(defaults.items)
		.setBodyHeight(defaults.bodyHeight)
		.setOnCollapseHandler(defaults.onCollapse)
		.setOnExpandHandler(defaults.onExpand)
		.setOnEnablementStatusChangeHandler(defaults.onEnablementStatusChange);

	if (defaults.collapsed) {
		this.collapse();
	} else {
		this.expand();
	}

	if (defaults.headerVisible) {
		this.showHeader();
	} else {
		this.hideHeader();
	}
	this._initialized = true;
};

CollapsiblePanel.prototype.showHeader = function () {
	this._headerVisible = true;
	if (this._htmlHeader) {
		this._htmlHeader.style.display = '';
	}
	return this;
};

CollapsiblePanel.prototype.hideHeader = function () {
	this._headerVisible = false;
	if (this._htmlHeader) {
		this._htmlHeader.style.display = 'none';
	}
	return this;
};

CollapsiblePanel.prototype.setOnEnablementStatusChangeHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnEnablementStatusChangeHandler(): The parameter must be a function or null.");
	}
	this.onEnablementStatusChange = handler;
	return this;
};

CollapsiblePanel.prototype.isDisabled = function () {
	return this._disabled;
};

CollapsiblePanel.prototype.disable = function () {
	if (!this._disabled) {
		this.collapse();
		this.style.addClasses(['collapsible-panel-disabled']);
		this._disabled = true;
		if (typeof this.onEnablementStatusChange === 'function') {
			this.onEnablementStatusChange(this, false);
		}
	}
	return this;
};

CollapsiblePanel.prototype.enable = function () {
	if (this._disabled) {
		this.style.removeClasses(['collapsible-panel-disabled']);
		this._disabled = false;
		if (typeof this.onEnablementStatusChange === 'function') {
			this.onEnablementStatusChange(this, true);
		}
	}
	return this;
};

CollapsiblePanel.prototype.setParent = function (parent) {
	if(!(parent === null || parent instanceof FieldPanel || parent instanceof MultipleCollapsiblePanel)) {
		throw new Error("setParent(): The parameter must be an instance of FieldPanel, MultipleCollapsiblePanel or "
			+ "null.");
	}
	this._parent = parent;

	return this;
};

CollapsiblePanel.prototype.enableAnimations = function	() {
	this._enabledAnimations = true;
	return this;
};

CollapsiblePanel.prototype.disableAnimations = function () {
	this._enabledAnimations = false;
	return this;
};

CollapsiblePanel.prototype.setOnExpandHandler = function(handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnExpandHandler(): The paremeter must be a function or null.");
	}
	this.onExpand = handler;
	return this;
};

CollapsiblePanel.prototype.setOnCollapseHandler = function(handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnCollapseHandler(): The paremeter must be a function or null.");
	}
	this.onCollapse = handler;
	return this;
};

CollapsiblePanel.prototype.setWidth = function(w) {
	if(typeof w === 'number') {
		Element.prototype.setWidth(w);
	} else {
		if(/^\d+(.\d+)?(px|%)?$/.test(w)) {
			this.width = w;
			if (this.html) {
	            this.style.addProperties({width: w});
	        }
		} else {
			throw new Error("setWidth(): The parameter must be a number or a valid number/unit formatted string.");
		}
	}
	return this;
};

CollapsiblePanel.prototype.isCollapsed = function () {
	return this._collapsed;
};

CollapsiblePanel.prototype.collapse = function (noAnimation) {
	this._collapsed = true;
	if(this._htmlBody) {
		jQuery(this._htmlCollapsibleIcon).removeClass('icon-double-angle-down').addClass('icon-double-angle-right');
		if(isInDOM(this.html)) {
			if (!this._enabledAnimations || noAnimation) {
				jQuery(this._htmlBody).stop(true, true).hide();
			} else {
				jQuery(this._htmlBody).stop(true, true).slideUp();
			}
		} else {
			this._htmlBody.style.display = 'none';
		}
		if (this._initialized && typeof this.onCollapse === 'function') {
			this.onCollapse(this);
		}
	}
	return this;
};

CollapsiblePanel.prototype.expand = function (noAnimation) {
	this._collapsed = false;
	if(this._htmlBody) {
		jQuery(this._htmlCollapsibleIcon).removeClass('icon-double-angle-right').addClass('icon-double-angle-down');
		if (!this._enabledAnimations || noAnimation) {
			jQuery(this._htmlBody).stop(true, true).show();
		} else {
			jQuery(this._htmlBody).stop(true, true).slideDown();	
		}
		if (this._initialized && typeof this.onExpand === 'function') {
			this.onExpand(this);
		}
	}	
	return this;
};

CollapsiblePanel.prototype.toggleCollapse = function() {
	if(this._collapsed) {
		this.expand();
	} else {
		this.collapse();
	}
	return this;
};

CollapsiblePanel.prototype.setBodyHeight = function (height) {
	this._bodyHeight = height;
	if(this._htmlBody) {
		this._htmlBody.style.maxHeight = isNaN(height) ? height : height + "px";
	}
	return this;
};

CollapsiblePanel.prototype.setTitle = function (title) {
	if(typeof title !== 'string') {
		throw new Error("setTitle(): The parameter must be a string.");
	}

	this._title = title;
	if(this._htmlTitle) {
		this._htmlTitle.textContent = title;
	}
	return this;
};

CollapsiblePanel.prototype.getTitle = function () {
	return this._title;
};

CollapsiblePanel.prototype._unpaintItem = function (item) {
	if(item.html) {
		//IE compatibilty
		if (item.html.remove) {
			item.html.remove();
		} else {
			item.html.removeNode(true);
		}
	}
	return this;
};

CollapsiblePanel.prototype._unpaintItems = function () {
	var i, items = this._items.asArray();
	if(this.html) {
		for (i = 0; i < items.length; i += 1) {
			this._unpaintItem(items[i]);
		}
	}
	return this;
};

CollapsiblePanel.prototype.removeItem = function (item) {
	var itemToRemove = this.getItem(item);
	if (itemToRemove) {
		this._items.remove(itemToRemove);
		this._unpaintItem(itemToRemove);
	}
	return 
};

CollapsiblePanel.prototype.clearItems = function () {
	var i, items = this._items.asArray();
	this._unpaintItems();
	if(this._massiveAction) {
		this._items.clear();
	}
	return this;
};

CollapsiblePanel.prototype._paintItem = function (item, index) {
	var itemAtIndex;
	if(this.html) {
		if (index) {
			itemAtIndex = this._items.get(index);
		}
		if (itemAtIndex) {
			this._htmlBody.insertBefore(item.getHTML(), itemAtIndex.getHTML());
		} else {
			this._htmlBody.appendChild(item.getHTML());	
		}
	}
	return this;
};

CollapsiblePanel.prototype._paintItems = function () {
	var i, items;
	if(this.html) {
		items = this._items.asArray();
		this._unpaintItems();
		for(i = 0; i < items.length; i += 1) {
			this._paintItem(items[i]);
		}
	}
	return this;
};

CollapsiblePanel.prototype.addItem = function (item, index) {
	if (typeof index === 'number') {
		this._items.insertAt(item, index);	
	} else if (index === null || index === undefined) {
		this._items.insert(item);
	} else {
		throw new Error("addItem(): The second parameter is optional, in case of use it must be a number.");
	}
	if(!this._massiveAction) {
		this._paintItem(item, index);
	}
	return this;
};

CollapsiblePanel.prototype.getItem = function (field) {
	if (typeof field === 'string') {
		return this._items.find("id", field);	
	} else if (typeof field === 'number') {
		return this._items.get(field);
	} else if (typeof field instanceof FormPanelItem && this._items.indexOf(field) >= 0) {
		return field;
	}
	return null;
};

CollapsiblePanel.prototype.setItems = function (items) {
	var i;
	if(!jQuery.isArray(items)) {
		throw new Error("setItems(): The parameter must be an array.")
	}
	this._massiveAction = true;
	this.clearItems();
	for(i = 0; i < items.length; i++) {
		this.addItem(items[i]);
	}
	this._paintItems();
	this._massiveAction = false;
	return this;
};

CollapsiblePanel.prototype.getItems = function () {
	return this._items.asArray();
};

CollapsiblePanel.prototype._createBody = function () {
	throw new Error("_createBody(): This function must be overwritten in subclases, since it's been called from an abstract one.");
};

CollapsiblePanel.prototype._attachListeners = function () {
	var that;
	if(this.html && !this._attachedListeners) {
		that = this;
		jQuery(this._htmlTitleContainer).on('click', function() {
			if (!that._disabled) {
				that.toggleCollapse();
			}
		});
	}

	return this;
};

CollapsiblePanel.prototype.createHTML = function () {
	var htmlHeader, htmlTitle, htmlBody, htmlTitleContainer, collapsibleIcon;
	if(!this.html) {
		this.html = this.createHTMLElement('div');
		this.html.id = this.id;
		this.html.className = "adam collapsible-panel";
		htmlHeader = this.createHTMLElement('div');
		htmlHeader.className = "adam collapsible-panel-header";
		htmlTitleContainer = this.createHTMLElement('h4');
		htmlTitleContainer.className = "adam collapsible-panel-title";
		htmlTitle = this.createHTMLElement('span');
		collapsibleIcon = this.createHTMLElement('i');
		collapsibleIcon.className = 'adam collapsible-panel-icon icon-double-angle-right';

		htmlTitleContainer.appendChild(collapsibleIcon);
		htmlTitleContainer.appendChild(htmlTitle);
		htmlHeader.appendChild(htmlTitleContainer);
		this.html.appendChild(htmlHeader);
		htmlBody = this._createBody();
		htmlBody.className += " adam collapsible-panel-body";
		this._htmlBody = htmlBody;
		this.html.appendChild(htmlBody);

		this._htmlCollapsibleIcon = collapsibleIcon;
		this._htmlTitleContainer = htmlTitleContainer;
		this._htmlHeader = htmlHeader;
		this._htmlTitle = htmlTitle;
		this._htmlBody = htmlBody;

		this.setBodyHeight(this._bodyHeight);
		this.setTitle(this._title);

		this._paintItems();
		this._collapsed ? this.collapse() : this.expand();
		this._attachListeners();
		this.setVisible(this.visible);

		if (this._headerVisible) {
			this.showHeader();
		} else {
			this.hideHeader();
		}

		this.style.applyStyle();

	    this.style.addProperties({
	        width: this.width,
	        height: "auto"
	    });
	}
	
	return this.html;
};
//FormPanel
	var FormPanel = function(settings) {
		CollapsiblePanel.call(this, settings);
		this._htmlSubmit = null;
		this._submitCaption = null;
		this._htmlFooter = null;
		this._dependencyMap = null;
		this._submitVisible = null;
		this.onSubmit =  null;
		FormPanel.prototype.init.call(this, settings);
	};

	FormPanel.prototype = new CollapsiblePanel();
	FormPanel.prototype.constructor = FormPanel;
	FormPanel.prototype.type = "FormPanel";

	FormPanel.prototype.init = function (settings) {
		var defaults = {
			submitCaption: translate("LBL_PMSE_FORMPANEL_SUBMIT"),
			items: [],
			submitVisible: true,
			onSubmit: null
		};

		jQuery.extend(true, defaults, settings);

		this._dependencyMap = {};
		this._submitVisible = !!defaults.submitVisible;

		this.setItems(defaults.items)
			.setSubmitCaption(defaults.submitCaption)
			.setOnSubmitHandler(defaults.onSubmit);
	};

	FormPanel.prototype.setOnSubmitHandler = function (handler) {
		if (!(handler === null || typeof handler === 'function')) {
			throw new Error("setOnSubmitHandler(): The parameter must be a function or null.");
		}
		this.onSubmit = handler;
		return this;
	};

	FormPanel.prototype.setItems = function (items) {
		if(this._dependencyMap) {
			CollapsiblePanel.prototype.setItems.call(this, items);
		}
		return this;
	};

	FormPanel.prototype._createField = function (settings) {
		var defaults = {
			type: 'text'
		}, field;

		jQuery.extend(true, defaults, settings);

		switch (defaults.type) {
			case 'text':
				field = new FormPanelText(defaults);
				break;
			case 'integer':
				defaults.precision = 0;
				defaults.groupingSeparator = "";
				field = new FormPanelNumber(defaults);
				break;
			case 'number':
				defaults.precision = -1;
				defaults.groupingSeparator = "";
				field = new FormPanelNumber(defaults);
				break;
			case 'currency':
				defaults.precision = 2;
				field = new FormPanelNumber(defaults);
				break;
			case 'dropdown':
				field = new FormPanelDropdown(defaults);
				break;
			case 'date':
				field = new FormPanelDate(defaults);
				break;
			case 'datetime':
				field = new FormPanelDatetime(defaults);
				break;
			case 'radio':
				field = new FormPanelRadio(defaults);
				break;
			case 'hidden':
				field = new FormPanelHidden(defaults);
				break;
			case 'checkbox':
				field = new FormPanelCheckbox(defaults);
				break;
			case 'button': 
				field = new FormPanelButton(defaults);
				break;
			default:
				throw new Error("_createField(): Invalid field type.");
		}
		return field;
	};

	FormPanel.prototype.setSubmitCaption = function (caption) {
		if (typeof caption !== 'string') {
			throw new Error("setSubmitCaption(): The parameter must be a string.");
		}
		this._submitCaption = caption;
		if (this._htmlSubmit) {
			this._htmlSubmit.textContent = caption;
		}
		return this;
	};

	FormPanel.prototype.getItem = function (field) {
		if (typeof field === 'string') {
			return this._items.find("_name", field);	
		} else if (typeof field === 'number') {
			return this._items.get(field);
		} else if (field instanceof FormPanelItem && this._items.indexOf(field) >= 0) {
			return field;
		}
		return null;
	};

	FormPanel.prototype._paintItem = function (item, index) {
		var itemAtIndex;
		if(this.html) {
			if (typeof index === 'number') {
				itemAtIndex = this._items.get(index + 1);
			}
			if (itemAtIndex) {
				this._htmlBody.insertBefore(item.getHTML(), itemAtIndex.getHTML());
			} else {
				this._htmlBody.insertBefore(item.getHTML(), this._htmlFooter);
			}
		}
		return this;
	};

	FormPanel.prototype.addItem = function (item, index) {
		var itemToAdd, dependency, dependant, i, dependencyField;
		if(item instanceof FormPanelField) {
			itemToAdd = item;
		} else if (typeof item === 'object') {
			itemToAdd = this._createField(item);
		} else {
			throw new Error('addItem(): the parameter must be an object or an instance of FormPanelField.');
		}
		itemToAdd.setForm(this);
		CollapsiblePanel.prototype.addItem.call(this, itemToAdd, index);
		if (itemToAdd instanceof FormPanelField) {
			dependency = this._dependencyMap[itemToAdd.getName()];
			if (dependency) {
				for (i = 0; i < dependency.length; i += 1) {
					if(dependencyField = this.getItem(dependency[i])) {
						dependencyField.fireDependentFields();
					}	
				}	
			}
			itemToAdd.fireDependentFields();
		}
		return this;
	};

	FormPanel.prototype.replaceItem = function (newItem, itemToBeReplaced) {
		var itemIndex;
		itemToBeReplaced = this.getItem(itemToBeReplaced);
		itemIndex = this._items.indexOf(itemToBeReplaced);
		if (itemIndex >= 0) {
			this.removeItem(itemToBeReplaced);
			this.addItem(newItem, itemIndex);	
		}
		return this;
	};

	FormPanel.prototype.isValid = function () {
		var items = this._items.asArray(), i, valid = true;
		for (i = 0; i < items.length; i += 1) {
			if (items[i] instanceof FormPanelField) {
				valid = valid && items[i].isValid();
				if(!valid) {
					return valid;
				}
			}
		}
		return valid;
	};
  
	FormPanel.prototype._createBody = function () {
		var element = this.createHTMLElement('form');
		element.className = 'form-panel-body';
		return element;
	};	

	FormPanel.prototype.getValueObject = function () {
		var i, fields = this._items.asArray(), valueObject = {
		};

		for(i = 0; i < fields.length; i += 1) {
			if (fields[i] instanceof FormPanelField) {
				valueObject[fields[i].getName()] = fields[i].getValue();
			}
		}
		return valueObject;
	};

	FormPanel.prototype.registerSingleDependency = function (target, dependant) {
		if(!(target instanceof FormPanelField && typeof dependant === 'string' || jQuery.isArray(dependant))) {
			throw new Error("registerSingleDependency(): Incorrect parameters.");
		}
		if (!this._dependencyMap[dependant]) {
			this._dependencyMap[dependant] = [];
		}
		if (this._dependencyMap[dependant].indexOf(dependant) === -1) {
			this._dependencyMap[dependant].push(target.getName());
		}
		return this;
	};

	FormPanel.prototype.registerDependency = function (target, dependantFields) {
		var i;
		if(!(target instanceof FormPanelField && jQuery.isArray(dependantFields))) {
			throw new Error("registerDependency(): Incorrect parameters.");
		}
		for(i = 0; i < dependantFields.length; i += 1) {
			this.registerSingleDependency(target, dependantFields[i]);
		}
		return this;
	};

	FormPanel.prototype.showSubmit = function () {
		this._htmlFooter.style.display = '';
		this._submitVisible = true;
		return this;
	};

	FormPanel.prototype.hideSubmit = function () {
		this._htmlFooter.style.display = 'none';
		this._submitVisible = false;
		return this;
	};

	FormPanel.prototype.reset = function () {
		var items = this._items.asArray(), i;

		for (i = 0; i < items.length; i += 1) {
			if (items[i] instanceof FormPanelField) {
				items[i].reset();
			}
		}

		return this;
	};

	FormPanel.prototype.submit = function () {
		if (this.isValid()) {
			$(this._htmlBody).trigger('submit');
		}
		return this;
	};

	FormPanel.prototype._attachListeners = function () {
		var that;
		if(this.html && !this._attachedListeners) {
			that = this;
			CollapsiblePanel.prototype._attachListeners.call(this);
			jQuery(that._htmlBody).on('submit', function (e) {
				var sendForm = true;
				e.preventDefault();
				if(that.isValid()) {
					if (typeof that.onSubmit === 'function') {
						sendForm = !(that.onSubmit(that) === false);
					}
					if (sendForm) {
						that._onValueAction();
					}
				}
			});
			this._attachedListeners = true;
		}

		return this;
	};

	FormPanel.prototype.createHTML = function () {
		var button, footer;
		if (!this.html) {
			CollapsiblePanel.prototype.createHTML.call(this);
			footer = this.createHTMLElement("div");
			footer.className = "adam form-panel-footer";
			button = this.createHTMLElement("button");
			button.className = 'adam form-panel-submit btn btn-mini';
			footer.appendChild(button);
			this._htmlBody.appendChild(footer);
			this._htmlSubmit = button;
			this._htmlFooter = footer;
			this.setSubmitCaption(this._submitCaption);
			this._attachListeners();

			if (this._submitVisible) {
				this.showSubmit();
			} else {
				this.hideSubmit();
			}
		}
		return this.html;
	};

//FormPanelItem
	var FormPanelItem = function (settings) {
		Element.call(this, settings);
		this._name = null; 
		this._label = null;
		this._disabled = null;
		this._form = null;
		FormPanelItem.prototype.init.call(this, settings);
	};

	FormPanelItem.prototype = new Element();
	FormPanelItem.prototype.constructor = FormPanelItem;
	FormPanelItem.prototype.type = "FormPanelItem";

	FormPanelItem.prototype.init = function (settings) {
		var defaults = {
			name: this.id,
			form: null, 
			label: "[form-item]",
			disabled: false,
			height: "auto"
		};

		jQuery.extend(true, defaults, settings);

		this.setName(defaults.name)
			.setForm(defaults.form)
			.setLabel(defaults.label)
			.setHeight(defaults.height);

		if (defaults.disabled) {
			this.disable();
		} else {
			this.enable();
		}
	};

	FormPanelItem.prototype.setHeight = function (h) {
		if (!(typeof h === 'number' ||
			(typeof h === 'string' && (h === "auto" || /^\d+(\.\d+)?(em|px|pt|%)?$/.test(h))))) {
			throw new Error("setHeight(): invalid parameter.");
		}
		this.height = h;
	    if (this.html) {
	        this.style.addProperties({height: this.height});
	    }
	    return this;
	};

	FormPanelItem.prototype.setName = function (name) {
		if (typeof name !== 'string') {
			throw new Error("setName(): The parameter must be a string.");
		}
		this._name = name;
		return this;
	};

	FormPanelItem.prototype.getName = function () {
		return this._name;
	};

	FormPanelItem.prototype.setWidth = function (width) {
		return FormPanel.prototype.setWidth.call(this, width);
	};

	FormPanelItem.prototype.setForm = function (form) {
		if(!(form === null || form instanceof FormPanel)) {
			throw new Error("setForm(): The parameter must be an instance of FormPanel or null.");
		}
		this._form = form;
		return this;
	};

	FormPanelItem.prototype.getForm = function () {
		return this._form;
	};

	FormPanelItem.prototype.setLabel = function (label) {
		if (typeof label !== 'string') {
			throw  new Error("setLabel(): The parameter must be a string.");
		}
		this._label = label;
		return this;
	};

	FormPanelItem.prototype.getLabel = function () {
		return this._label;
	};

	FormPanelItem.prototype.enable = function () {
		this._disabled = false;
		return this;
	};

	FormPanelItem.prototype.disable = function () {
		this._disabled = true;
		return this;
	};

	FormPanelItem.prototype.isDisabled = function () {
		return this._disabled;
	};

	FormPanelItem.prototype._attachListeners = function () {};

	FormPanelItem.prototype.setVisible = function (value) {
		if (_.isBoolean(value)) {
	        this.visible = value;
	        if (this.html) {
	            if (value) {
	                this.style.removeProperties(["display"]);
	            } else {
	                this.style.addProperties({display: "none"});
	            }
	        }
	    }
	    return this;
	};

	FormPanelItem.prototype._postCreateHTML = function () {
		this._attachListeners();
		this.style.applyStyle();

        this.style.addProperties({
            width: this.width,
            height: this.height
        });

        if (this._disabled) {
			this.disable();
		} else {
			this.enable();
		}

		this.setVisible(this.visible);

		return this;
	};

	FormPanelItem.prototype.createHTML = function () {
		var html;
		if (!this.html) {
			html = this.createHTMLElement("div");
			html.id = this.id;
			html.className = 'adam form-panel-item';
			this.html = html;
			this._postCreateHTML();
		}
		return this.html;
	};

//FormPanelButton
	var FormPanelButton = function (settings) {
		FormPanelItem.call(this, settings);
		this.onClick = null;
		this._htmlButton = null;
		FormPanelButton.prototype.init.call(this, settings);
	};

	FormPanelButton.prototype = new FormPanelItem();
	FormPanelButton.prototype.constructor = FormPanelButton;
	FormPanelButton.prototype.type = "FormPanelButton";

	FormPanelButton.prototype.init = function (settings) {
		var defaults = {
			onClick: null,
			width: "100%"
		};

		jQuery.extend(true, defaults, settings);

		this.setOnClickHandler(defaults.onClick)
			.setWidth(defaults.width);
	};

	FormPanelButton.prototype.setOnClickHandler = function (handler) {
		if (!(handler === null || typeof handler === 'function')) {
			throw new Error("setOnClickHandler(): The parameter must be a function or null.");
		}
		this.onClick = handler;
		return this;
	};

	FormPanelButton.prototype.enable = function() {
		if (this._htmlButton){
			this._htmlButton.disabled = false;
		}
		return FormPanelItem.prototype.enable.call(this);
	};

	FormPanelButton.prototype.disable = function() {
		if (this._htmlButton) {
			this._htmlButton.disabled = true;
		}
		return FormPanelItem.prototype.disable.call(this);
	};

	FormPanelButton.prototype._attachListeners = function () {
		var that = this;
		if (this.html) {
			jQuery(this._htmlButton).on('click', function () {
				if (typeof that.onClick === 'function') {
					that.onClick(that);
				}
			});
		}

		return this;
	};

	FormPanelButton.prototype._postCreateHTML = function() {
		if (this._htmlButton) {
			FormPanelItem.prototype._postCreateHTML.call(this);
		}
		return this;
	};

	FormPanelButton.prototype.createHTML = function () {
		var html, button;
		if (!this.html)	{
			html = FormPanelItem.prototype.createHTML.call(this);
			html.className += " form-panel-button";
			button = this.createHTMLElement("input");
			button.type = 'button';
			button.value = this._label;
			button.className = "btn btn-mini";
			html.appendChild(button);

			this._htmlButton = button;

			this._postCreateHTML();
		}
		return this.html;
	};

//FormPanelField
	var FormPanelField = function (settings) {
		FormPanelItem.call(this, settings);
		/*this._name = null;
		this._label = null;*/
		this._value = null;
		this.onChange = null;
		this._htmlControl = [];
		this._htmlControlContainer = null;
		this._htmlLabelContainer = null;
		this._dependantFields = [];
		this._dependencyHandler = null;
		this.required = null;
		this._disabled = null;
		this._form = null;
		this._initialValue = null;
		FormPanelField.prototype.init.call(this, settings);
	};

	FormPanelField.prototype =  new FormPanelItem();
	FormPanelField.prototype.constructor = FormPanelField;
	FormPanelField.prototype.type = "FormPanelField";

	FormPanelField.prototype.init = function (settings) {
		var defaults = {
			/*form: null,*/
			/*name: this.id,*/
			label: "[field]",
			onChange: null,
			dependantFields: [],
			dependencyHandler: null,
			value: "",
			required: false/*,
			disabled: false*/
		};

		jQuery.extend(true, defaults, settings);

		this.setLabel(defaults.label)
			.setValue(defaults.value)
			.setRequired(defaults.required)
			.setOnChangeHandler(defaults.onChange)
			.setDependantFields(defaults.dependantFields)
			.setDependencyHandler(defaults.dependencyHandler);

		this._initialValue = this._value;
	};

	FormPanelField.prototype.reset = function () {
		this.setValue(this._initialValue);
		return this;
	};

	FormPanelField.prototype.setForm = function (form) {
		FormPanelItem.prototype.setForm.call(this, form);
		if (form) {
			form.registerDependency(this, this._dependantFields);
		}
		return this;
	};

	FormPanelField.prototype.setRequired = function (required) {
		this.required = !!required;
		return this;
	};

	FormPanelField.prototype._evalRequired = function () {
		if(this.required && !this._disabled) {
			return !!this._value;
		}
		return true;
	};

	FormPanelField.prototype._validateField = function () {
		return this;
	};

	FormPanelField.prototype.isValid = function () {
		var isValid = this._evalRequired();

		if (isValid) {
			isValid = this._validateField();
		}

		if(!isValid && this.html) {
			jQuery(this.html).addClass("error");
		} else {
			jQuery(this.html).removeClass("error");
		}
		return isValid;
	};

	FormPanelField.prototype.setDependencyHandler = function (handler) {
		if(!(handler === null || typeof handler === 'function')) {
			throw new Error("setDependencyHandler(): The parameter must be a function or null.");
		}
		this._dependencyHandler = handler;
		return this;
	};

	FormPanelField.prototype._fireDependencyHandler = function (field, value) {
		if (typeof this._dependencyHandler === 'function') {
			this._dependencyHandler(this, field, value);
		}
		return this;
	};

	FormPanelField.prototype.addDependantField = function (field) {
		if (typeof field === 'string') {
			this._dependantFields.push(field);
			if(this._form) {
				this._form.registerSingleDependency(this, field);
			}
		} else {
			throw new Error("addDependantField(): The parameter must be a string (The name of the dependant field).");
		}
		return this;
	};

	FormPanelField.prototype.setDependantFields = function (fields) {
		var i;
		if(!jQuery.isArray(fields)) {
			throw new Error("setDependantFields(): the parameter must be an array.");
		}
		this._dependantFields = [];
		for (i = 0; i < fields.length; i += 1) {
			this.addDependantField(fields[i]);
		}
		return this;
	};

	FormPanelField.prototype.setLabel = function (label) {
		FormPanelItem.prototype.setLabel.call(this, label);
		if(this._htmlLabelContainer) {
			this._htmlLabelContainer.textContent = label;
		}
		return this;
	};

	FormPanelField.prototype.setOnChangeHandler = function (handler) {
		if (!(handler === null || typeof handler === 'function')) {
			throw new Error("setOnChangeHandler(): The parameter must be a function or null.");
		}
		this.onChange = handler;
		return this;
	};

	FormPanelField.prototype._setValueToControl = function(value) {
		if(this._htmlControl[0]) {
			this._htmlControl[0].value = value;
		}
		return this;
	};

	FormPanelField.prototype.setValue = function (value) {
		var preValue = this._value;
		if(typeof value !== 'string') {
			throw new Error("setValue(): The parameter must be a string.");
		}
		this._setValueToControl(value);
		this._value = value;
		if (value !== preValue) {
			this.fireDependentFields();
		}
		return this;
	};

	FormPanelField.prototype.enable = function () {
		var i;
		if (this._htmlControl && this._htmlControl.length) {
			for (i = 0; i < this._htmlControl.length; i += 1) {
				this._htmlControl[i].disabled = false;	
			}
		}
		return FormPanelItem.prototype.enable.call(this);
	};

	FormPanelField.prototype.disable = function () {
		var i;
		if (this._htmlControl && this._htmlControl.length) {
			for (i = 0; i < this._htmlControl.length; i += 1) {
				this._htmlControl[0].disabled = true;
			}
		}
		return FormPanelItem.prototype.disable.call(this);
	};

	FormPanelField.prototype.getValue = function () {
		return this._value;
	};

	FormPanelField.prototype._getValueFromControl = function () {
		var value = "", i;

		for (i = 0; i < this._htmlControl.length; i += 1) {
			value += this._htmlControl[i].value;
		}
		return value;
	};

	FormPanelField.prototype.fireDependentFields = function () {
		var dependantField, value = this._value;
		if(this._form) {
			for(i = 0; i < this._dependantFields.length; i++) {
				dependantField = this._form.getItem(this._dependantFields[i]);
				if (dependantField) {
					dependantField._fireDependencyHandler(this, value);
				}
			}	
		}
		return this;
	};

	FormPanelField.prototype._onChangeHandler = function () {
		var that = this;
		return function () {
			var currValue = that._value, 
				newValue = that._getValueFromControl(),
				valueHasChanged = currValue !== newValue,
				i, dependantField;

			if(valueHasChanged) {
				that._value = newValue;
				if (typeof that.onChange === 'function') {
					that.onChange(that, newValue, currValue);
				}
				that.fireDependentFields();
			}
		}
	};

	FormPanelField.prototype._attachListeners = function () {
		var i, control;
		if (this.html) {
			for (i = 0; i < this._htmlControl.length; i += 1) {
				jQuery(this._htmlControl[i]).on('change', this._onChangeHandler());
			}
		}
		return this;
	};

	FormPanelField.prototype._createControl = function () {
		var control, i;
		if(!this._htmlControl.length) {
			throw new Error("_createControl(): This method shouldn't be called until the field control is created.");
		}
		this._setValueToControl(this._value);
		for (i = 0; i < this._htmlControl.length; i += 1) {
			control = this._htmlControl[i];
			control.className += ' inherit-width adam form-panel-field-control';
			this._htmlControlContainer.appendChild(control);
		}
		this.setValue(this._value);
		return this;
	};

	FormPanelField.prototype._postCreateHTML = function () {
		if (this._htmlControlContainer) {
			FormPanelItem.prototype._postCreateHTML.call(this);
		}

		return this;
	};

	FormPanelField.prototype.createHTML = function () {
		var html, htmlLabelContainer, span, htmlControlContainer;
		if (!this.html) {
			html = FormPanelItem.prototype.createHTML.call(this);
			html.className += ' adam form-panel-field record-cell';
			html.className += '	adam-' + this.type.toLowerCase();
			htmlLabelContainer = this.createHTMLElement("div");
			htmlLabelContainer.className = 'adam form-panel-label record-label';
			span = this.createHTMLElement("span");
			span.className = 'normal index';
			htmlControlContainer =  this.createHTMLElement("span");
			htmlControlContainer.className = "edit";
			span.appendChild(htmlControlContainer);
			html.appendChild(htmlLabelContainer);
			html.appendChild(span);

			this._htmlLabelContainer = htmlLabelContainer;
			this._htmlControlContainer = htmlControlContainer;
			this.html = html;

			this._createControl()
				.setLabel(this._label);

			this._postCreateHTML();
		}
		return this.html;
	};

//HiddenField
	var FormPanelHidden = function (settings) {
		FormPanelField.call(this, settings);
	};

	FormPanelHidden.prototype = new FormPanelField();
	FormPanelHidden.prototype.constructor = FormPanelHidden;
	FormPanelHidden.prototype.type = "FormPanelHidden";

	FormPanelHidden.prototype._createControl = function() {
		if (!this._htmlControl.length) {
			this._htmlControl[0] = this.createHTMLElement("input");
			this._htmlControl[0].name = this._name;
			this._htmlControl[0].type = "hidden";
			FormPanelField.prototype._createControl.call(this);
		}
		return this;
	};

	FormPanelHidden.prototype.createHTML = function () {
		FormPanelField.prototype.createHTML.call(this);
		this.html.style.display = "none";
		return this;
	};

//TextField
	var FormPanelText = function (settings) {
		FormPanelField.call(this, settings);
		this._placeholder = null;
		this.onKeyUp = null;
		this._maxLength = null;
		FormPanelText.prototype.init.call(this, settings);
	};

	FormPanelText.prototype = new FormPanelField();
	FormPanelText.prototype.constructor = FormPanelText;
	FormPanelText.prototype.type = "FormPanelText";

	FormPanelText.prototype.init = function(settings) {
		var defaults = {
			placeholder: "",
			onKeyUp: null,
			maxLength: 0
		};
		jQuery.extend(true, defaults, settings);
		this.setPlaceholder(defaults.placeholder)
			.setOnKeyUpHandler(defaults.onKeyUp)
			.setMaxLength(defaults.maxLength);
	};

	FormPanelText.prototype.setMaxLength = function (maxLength) {
		if (!(typeof maxLength === 'number' && maxLength >= 0)) {
			throw new Error("setMaxLength(): The parameter must be a number major than 0.");
		}
		this._maxLength = maxLength;
		if (this._htmlControl[0]) {
			if (!maxLength) {
				this._htmlControl[0].removeAttribute("maxlength");
			} else {
				this._htmlControl[0].maxLength = maxLength;
			}
		}
		return this;
	};

	FormPanelText.prototype.setOnKeyUpHandler = function (handler) {
		if (!(handler === null || typeof handler === 'function')) {
			throw new Error("setOnKeyUpHandler(): The parameter must be a function or null");
		}
		this.onKeyUp = handler;
		return this;
	};

	FormPanelText.prototype.setPlaceholder = function (placeholder) {
		if(typeof placeholder !== 'string') {
			throw new Error("setPlaceholder(): The parameter must be a string.")
		}
		this._placeholder = placeholder;
		if (this._htmlControl[0]) {
			this._htmlControl[0].placeholder = placeholder;
		}
		return this;
	};

	FormPanelText.prototype._onKeyUp = function () {
		var that = this;

		return function (e) {
			if (typeof that.onKeyUp === 'function') {
				that.onKeyUp(that, that._htmlControl[0].value, e.keyCode);
			}
		};
	};

	FormPanelText.prototype._attachListeners = function() {
		if (this.html) {
			FormPanelField.prototype._attachListeners.call(this);
			jQuery(this._htmlControl[0]).on('keyup', this._onKeyUp());
		}
		return this;
	};

	FormPanelText.prototype._createControl = function () {
		if (!this._htmlControl.length) {
			this._htmlControl[0] = this.createHTMLElement("input");
			this._htmlControl[0].name = this._name;
			this._htmlControl[0].type = "text";
			this.setMaxLength(this._maxLength);
			FormPanelField.prototype._createControl.call(this);
		}
		return this;
	};

	FormPanelText.prototype.createHTML = function () {
		if(!this.html) {
			FormPanelField.prototype.createHTML.call(this);
			this.setPlaceholder(this._placeholder);
		}
		return this.html;
	};
//FormPanelNumber
	var FormPanelNumber = function (settings) {
		FormPanelField.call(this, settings);
		this._decimalSeparator = null;
		this._groupingSeparator = null;
		this._precision = null;
		this._initialized = false;
		FormPanelNumber.prototype.init.call(this, settings);
	};

	FormPanelNumber.prototype = new FormPanelField();
	FormPanelNumber.prototype.constructor = FormPanelNumber;
	FormPanelNumber.prototype.type = "FormPanelNumber";

	FormPanelNumber.prototype.init = function (settings) {
		var defaults = {
			decimalSeparator: ".",
			groupingSeparator: ",",
			precision: -1,
			value: null
		};

		jQuery.extend(true, defaults, settings);

		this.setDecimalSeparator(defaults.decimalSeparator)
			.setGroupingSeparator(defaults.groupingSeparator)
			.setPrecision(defaults.precision)
			.setValue(defaults.value);

		this._initialized = true;
	};

	FormPanelNumber.prototype._setValueToControl = function (value) {
		var integer, decimal, label = "", aux, power, i, decimalSeparator;
		if (this._htmlControl[0]) {
			this._htmlControl[0].value = this._parseToUserString(value);
		}
		return this;
	};

	FormPanelNumber.prototype._getValueFromControl = function () {
		var groupingSeparatorRegExp, numberParts, value = this._htmlControl[0].value, numericValue;
		
		if (this._groupingSeparator) {
			groupingSeparatorRegExp = new RegExp((this._isRegExpSpecialChar(this._groupingSeparator) ? "\\" : "") + this._groupingSeparator, "g");
			value = value.replace(groupingSeparatorRegExp, "");
		}
		if ((numberParts = value.split(this._decimalSeparator)).length > 2) {
			return null;
		}
		numberParts[1] = numberParts[1] || "0";
		if (!/^\-?\d+$/.test(numberParts[0]) || !/^\d+$/.test(numberParts[1])) {
			return null;
		}
		numericValue = parseInt(numberParts[0], 10);
		numericValue += (parseInt(numberParts[1], 10) / Math.pow(10, numberParts[1].length));

		this._htmlControl[0].value = this._parseToUserString(numericValue);

		return numericValue;
	};

	FormPanelNumber.prototype.setValue = function (value) {
		var preValue = this._value;
		if (!this._decimalSeparator) {
			return this;
		}
		if (!(value === null || typeof value === 'number')) {
			throw new Error("setValue(): The parameter must be a number.");
		}
		this._setValueToControl(value);
		this._value = value;
		if (value !== preValue) {
			this.fireDependentFields();
		}
		return this;
	};

	FormPanelNumber.prototype.setDecimalSeparator = function (decimalSeparator) {
		if (!(typeof decimalSeparator === 'string' && decimalSeparator.length === 1)) {
			throw new Error("setDecimalSeparator(): The parameter must be a single character.");
		}
		if (!isNaN(decimalSeparator) || ["+", "-", "/", "*"].indexOf(decimalSeparator) >= 0) {
			throw new Error("setDecimalSeparator(): Invalid parameter.");
		}
		if (decimalSeparator === this._groupingSeparator) {
			throw new Error("setDecimalSeparator(): The decimal separator must be different than the " 
				+ "grouping separator.");
		}
		this._decimalSeparator = decimalSeparator;
		//we make sure that the object has already been initialized
		if (this._initialized) {
			this.setValue(this._value);
		}
		return this;
	};

	FormPanelNumber.prototype.setGroupingSeparator = function (groupingSeparator) {
		if (!(typeof groupingSeparator === 'string' && groupingSeparator.length <= 1)) {
			throw new Error("setGroupingSeparator(): The parameter must be a single character or empty string.");
		}
		if (!(isNaN(groupingSeparator)  
			|| ["+", "-", "/", "*"].indexOf(groupingSeparator) < 0)) {
			throw new Error("setGroupingSeparator(): Invalid parameter.");
		}
		if (groupingSeparator === this._decimalSeparator) {
			throw new Error("setGroupingSeparator(): The grouping separator must be different than the " 
				+ "decimal separator.");
		}
		this._groupingSeparator = groupingSeparator;
		//we make sure that the object has already been initialized
		if (this._initialized) {
			this.setValue(this._value);
		}
		return this;
	};

	FormPanelNumber.prototype.setPrecision = function (precision) {
		if (!(typeof precision === 'number' && precision % 1 === 0)) {
			throw new Error("setPrecision(): The parameter must be an integer.");
		}
		this._precision = precision;
		//we make sure that the object has already been initialized
		if (this._initialized) {
			this.setValue(this._value);
		}
		return this;
	};

	FormPanelNumber.prototype._createControl = function () {
		if (!this._htmlControl.length) {
			this._htmlControl[0] = this.createHTMLElement("input");
			this._htmlControl[0].type = "text";
			FormPanelField.prototype._createControl.call(this);
		}
		return this;
	};

	FormPanelNumber.prototype._isRegExpSpecialChar = function (c) {
		switch (c) {
		    case "\\":
		    case "^":
		    case "$":
		    case "*":
		    case "+":
		    case "?":
		    case ".":
		    case "(":
		    case ")":
		    case "|":
		    case "{":
		    case "}":
		        return true;
		        break;
	    }
	    return false;
	};

	FormPanelNumber.prototype.isValid = function () {
		var isValid = FormPanelField.prototype.isValid.call(this),
			value = this._htmlControl[0].value;

		if (value && this._value === null) {
			isValid = false;
		}

		if(!isValid && this.html) {
			jQuery(this.html).addClass("error");
		} else {
			jQuery(this.html).removeClass("error");
		}
		return isValid;
	};

	FormPanelNumber.prototype._parseToUserString = function (value) {
		var integer, decimal, label = "", aux, power, i, decimalSeparator;
		if (value === null) {
			label = "";
		} else {
			if (this._precision >= 0) {
				power = Math.pow(10, this._precision);
				value = Math.round(value * power) /power;
			}
			decimalSeparator = this._precision === 0 ? "" : this._decimalSeparator;
			integer = aux = Math.floor(value).toString();

			if (this._precision !== 0) {
				decimal = "";
				decimal = value.toString().split(".")[1] || (this._precision < 0 ? "0" : "");
				for (i = decimal.length; i < this._precision; i += 1) {
					decimal += "0";
				}
			} else {
				decimal = "";
			}

			if (this._groupingSeparator) {
				while (aux.length > 3) {
					label = this._groupingSeparator + aux.substr(-3) + label;
					aux = aux.slice(0, -3);
				}
			}
			label = aux + label + decimalSeparator + decimal;	
		}
		return label;
	};

	FormPanelNumber.prototype._onKeyDown = function () {
		var that = this;
		return function (e) {
			if (that._precision === 0 && (e.keyCode < 48 || (e.keyCode > 57 && e.keyCode < 96) || e.keyCode >105) 
				&& e.keyCode !== 37 && e.keyCode !== 39 && e.keyCode !== 8 && e.keyCode !== 46) {
				e.preventDefault();
			}
		};
	};

	FormPanelNumber.prototype._attachListeners = function() {
		if (this.html) {
			jQuery(this._htmlControl[0]).on('keydown', this._onKeyDown());
			FormPanelField.prototype._attachListeners.call(this);
		}
		return this;
	};
//FormPanelDate
	var FormPanelDate = function (settings) {
		FormPanelField.call(this, settings);
		this._dom = {};
		this._dateObject = null;
		this._dateFormat = null;
		FormPanelDate.prototype.init.call(this, settings);
	};

	FormPanelDate.prototype = new FormPanelField();
	FormPanelDate.prototype.constructor = FormPanelDate;
	FormPanelDate.prototype.type = "FormPanelDate";

	FormPanelDate.prototype.init = function (settings) {
		var defaults = {
			dateFormat: "yyyy-mm-dd"
		};

		jQuery.extend(true, defaults, settings);

		this.setDateFormat(defaults.dateFormat);
	};

	FormPanelDate.prototype.open = function () {
		jQuery(this._htmlControl[0]).datepicker('show');
		return this;
	};

	FormPanelDate.prototype.close = function () {
		jQuery(this._htmlControl[0]).datepicker('hide');
		return this;
	};

	FormPanelDate.prototype._validateField = function () {
		var isValid;

		return this._value !== null || this._htmlControl[0].value === "";
	};

	FormPanelDate.prototype._setValueToControl = function (value) {
		return FormPanelField.prototype._setValueToControl.call(this, this._format(value));
	};
	FormPanelDate.prototype._getValueFromControl = function () {
		return this._unformat(this._htmlControl[0].value);
	};
	//Returns a date value in ISO format
	FormPanelDate.prototype._unformat = function (value) {
		//based on unformat function in components_4ffa9804da5d932ba4c9ac5834421ed5.js line 3876
		value = App.date(value, this._dateFormat.toUpperCase(), true);
		return value.isValid() ? value.format() : null;
	};
	//Returns a date in user format.
	FormPanelDate.prototype._format = function (value) {
		//based on format function in components_4ffa9804da5d932ba4c9ac5834421ed5.js line 3844
		if (!value) {
			return value;
		}
		value = App.date(value);
		return value.isValid() ? value.format(this._dateFormat.toUpperCase()) : null;
	};
	FormPanelDate.prototype.getFormattedDate = function () {
		return this._format(this._value);
	};

	FormPanelDate.prototype._attachListeners = function() {
		if (this.html) {
			jQuery(this._htmlControl[0]).on('changeDate change', this._onChangeHandler())
				.on("show", function() {
					$('.datepicker').filter(":visible").css("z-index", 1300)
				});
		}
		return this;
	};

	FormPanelDate.prototype.setDateFormat = function (dateFormat) {
		if (typeof dateFormat !== 'string') {
			throw new Error("setFormat(): The parameter must be a string.");
		}
		this._dateFormat = dateFormat;
		if (this._htmlControl[0]) {
			$(this._htmlControl[0]).datepicker({
				format: this._dateFormat/*,
				id: "xxx"*/
			});
		}
		return this;
	};

	FormPanelDate.prototype._createControl = function () {
		if (!this._htmlControl[0]) {
			this._htmlControl[0] = this.createHTMLElement("input");
			this._htmlControl[0].name = this._name;
			this._htmlControl[0].type = "text";
			this.setDateFormat(this._dateFormat);

			FormPanelField.prototype._createControl.call(this);
		}
		return this;
	};
//FormPanelDatetime
	var FormPanelDatetime = function (settings) {
		FormPanelDate.call(this, settings);
		this._timeFormat = null;
		FormPanelDatetime.prototype.init.call(this, settings);
	};

	FormPanelDatetime.prototype = new FormPanelDate();
	FormPanelDatetime.prototype.constructor = FormPanelDatetime;
	FormPanelDatetime.prototype.type = "FormPanelDatetime";

	FormPanelDatetime.prototype.init = function (settings) {
		var defaults = {
			timeFormat: 'H:i'
		};

		jQuery.extend(true, defaults, settings);

		this.setTimeFormat(defaults.timeFormat);
	};

	FormPanelDatetime.prototype.openTime = function () {
		jQuery(this._htmlControl[1]).timepicker('show');
		return this;
	};

	FormPanelDatetime.prototype.closeTime = function () {
		jQuery(this._htmlControl[1]).timepicker('hide');
		return this;
	};

	FormPanelDatetime.prototype.closeAll = function() {
		return this.close().closeTime();
	};

	FormPanelDatetime.prototype._setValueToControl = function (value) {
		var date, time;
		if (!this._htmlControl.length) {
			return this;
		}
		if (!value) {
			this._htmlControl[0].value = this._htmlControl[1].value = "";
		} else {
			date = value.split("T");
			time = date[1];
			date = date[0];
			if (this._htmlControl[1]) {
				FormPanelDate.prototype._setValueToControl.call(this, date);
				time = time.split(/[\+\-]/);
				time = time[0];
				jQuery(this._htmlControl[1]).timepicker("setTime", time);
			}
		}
		return this;
	};

	FormPanelDatetime.prototype._getValueFromControl = function () {
		var value = "", date, time, isValid = false, aux;

		if (this._htmlControl.length) {
			date = this._htmlControl[0].value;
			time = this._htmlControl[1].value;
			if (date && time) {
				value = SUGAR.App.date(date + " " + time, this._dateFormat.toUpperCase() + " " + SUGAR.App.date.convertFormat(this._timeFormat), true);	
				isValid = value.isValid();
			}
			if (!isValid) {
				if (date && !FormPanelDate.prototype._unformat.call(this, date)) {
					this._htmlControl[0].value = "";
				}
				//if date has changed then it means that the time was wrong
				if (time) {
					jQuery(this._htmlControl[1]).timepicker("setTime", time);
				}
				value = null;
			} else {
				value = value.format();
			}
		}
		return value;
	};

	FormPanelDatetime.prototype.setValue = function (value) {
		var preValue = this._value, splittedDate, aux, aux2, invalidValueMessage ="setValue(): Invalid value.";
		if (!(value === null || typeof value === 'string')) {
			throw new Error("setValue(): The parameter must be a string.");
		}
		if (!(typeof value === 'string' && (value === "" || (App.date(value)).isValid()))) {
			throw new Error(invalidValueMessage);
		}
		this._setValueToControl(value);
		this._value = value;
		if (value !== preValue) {
			this.fireDependentFields();
		}
		return this;
	};

	FormPanelDatetime.prototype.setTimeFormat = function (timeFormat) {
		var timeControl, formattedTime = "", timeParts, aux, dayPeriod;
		switch (timeFormat) {
			case "H:i":
			case "h:ia":
			case "h:iA":
			case "h:i a":
			case "h:i A":
			case "H.i":
			case "h.ia":
			case "h.iA":
			case "h.i a":
			case "h.i A":
				this._timeFormat = timeFormat;
				break;
			default:
				throw new Error("setTimeFormat(): invalid format.");
		}
		if (timeControl = this._htmlControl[1]) {
			if (this._dateObject) {
				timeParts = timeFormat.split("");
				aux = this._dateObject.getHours();
				aux = (timeParts[0] === "h" && aux > 12) ? aux - 12 : aux;
				aux = aux < 10 ? "0" + aux : aux;
				formattedTime += aux + timeParts[1];

				aux = this._dateObject.getMinutes();
				formattedTime += (aux < 10 ? "0" + aux : aux);

				if (timeParts.length > 3) {
					dayPeriod = this._dateObject.getHours() < 12 ? "am" : "pm";
					if (timeParts[3] === "a") {
						formattedTime += dayPeriod;
					} else if (timeParts[3] === "A") {
						formattedTime += dayPeriod.toUpperCase();
					} else {
						formattedTime += " " 
							+ (timeParts[4] === "A" ? dayPeriod.toUpperCase() : (timeParts[4] === "a" ? dayPeriod : ""));
					}
				}
			}
			this._htmlControl[1].value = formattedTime;
			jQuery(this._htmlControl[1]).timepicker({
				timeFormat: timeFormat,
				appendTo: function (a) {
					return a.parent().parent().parent().parent().parent().parent().parent();
				}
			});
		}
		return this;
	};

	FormPanelDatetime.prototype._attachListeners = function	() {
		if (this.html) {
			FormPanelDate.prototype._attachListeners.call(this);
			jQuery(this._htmlControl[1]).on('change', this._onChangeHandler());
		}
		return this;
	};

	FormPanelDatetime.prototype._createControl = function () {
		if (!this._htmlControl.length) {
			this._htmlControl[1] = this.createHTMLElement("input");
			this._htmlControl[1].type = "text";
			this.setTimeFormat(this._timeFormat);
			FormPanelDate.prototype._createControl.call(this);
		}
		return this;
	};
//FormPanelDropdown
	var FormPanelDropdown = function (settings) {
		FormPanelField.call(this, settings);
		this._options = null;
		this._proxy = null;
		this._dataURL = null;
		this._dataRoot = null;
		this._massiveAction = false;
		this._labelField = null;
		this._valueField = null;
		FormPanelDropdown.prototype.init.call(this, settings);
	};

	FormPanelDropdown.prototype = new FormPanelField();
	FormPanelDropdown.prototype.constructor = FormPanelDropdown;
	FormPanelDropdown.prototype.type = "FormPanelDropdown";

	FormPanelDropdown.prototype.init = function (settings) {
		var defaults = {
			options: [],
			value: "",
			dataURL: null,
			dataRoot: null,
			labelField: "label",
			valueField: "value"
		};

		jQuery.extend(true, defaults, settings);

		this._proxy = new SugarProxy();

		FormPanelField.prototype.setValue.call(this, defaults.value);
		this._options = new ArrayList();

		this.setDataURL(defaults.dataURL)
			.setDataRoot(defaults.dataRoot)
			.setLabelField(defaults.labelField)
			.setValueField(defaults.valueField);

		if (typeof defaults._dataURL === 'string') {
			this.load();
		} else {
			this.setOptions(defaults.options);	
		}
	};

	FormPanelDropdown.prototype.setLabelField = function (field) {
		if (typeof field !== 'string') {
			throw new Error('setLabelField(): The parameter must be a string.');
		}
		this._labelField = field;
		return this;
	};

	FormPanelDropdown.prototype.getLabelField = function (field) {
		return this._labelField;
	};

	FormPanelDropdown.prototype.setValueField = function (field) {
		if (!(typeof field === 'string' || typeof field === "function")) {
			throw new Error('setValueField(): The parameter must be a string.');
		}
		this._valueField = field;
		return this;
	};

	FormPanelDropdown.prototype.getValueField = function () {
		return this._valueField;
	};

	FormPanelDropdown.prototype._showLoadingMessage = function() {
		var option;
		if (this._htmlControl.length) {
			option = this.createHTMLElement('option');
			option.value = "";
			option.label = option.textContent = 'loading...';
			option.className = 'adam form-apnel-dropdown-loading';
			option.selected = true;
			this.disable();
			this._htmlControl[0].appendChild(option);
		}
		return this;
	};

	FormPanelDropdown.prototype._removeLoadingMessage = function () {
		jQuery(this._htmlControl[0]).find('adam form-apnel-dropdown-loading').remove();
		this.enable();
		return this;
	};

	FormPanelDropdown.prototype._onLoadDataSuccess = function () {
		var that = this;
		return function (data) {
			var items = that._dataRoot ? data[that._dataRoot] : data;
			that._removeLoadingMessage();
			that.setOptions(items);
		};
	};

	FormPanelDropdown.prototype.load = function () {
		if (typeof this._dataURL !== 'string') {
			throw new Error("load(): The dataURL wasn't set properly.");
		}
		this._proxy.url = this._dataURL;
		this.clearOptions();
		this._showLoadingMessage();
		this._proxy.getData(null, {
			success: this._onLoadDataSuccess()
		});
		return this;
	};

	FormPanelDropdown.prototype.setDataRoot = function (root) {
		if (!(root === null || typeof root === 'string')) {
			throw new Error("setDataRoot(): The parameter must be a string or null.");
		}
		this._dataRoot = root;
		return this;
	};

	FormPanelDropdown.prototype.setDataURL = function (url) {
		if (!(url === null || typeof url === 'string')) {
			throw new Error("setDataURL(): The parameter must be a string or null.");
		}
		this._dataURL = url;
		return this;
	};

	FormPanelDropdown.prototype.existsValueInOptions = function (value) {
		var i, options = this._options.asArray();

		if (typeof this._valueField === "string") {
			return !!this._options.find(this._valueField, value);
		} else {
			for (i = 0; i < options[i]; i += 1) {
				if (value === this._valueField(options[i])) {
					return true;
				}
			}
		}
		return false;
	};

	FormPanelDropdown.prototype._getFirstAvailableOption = function () {
		var items, i;
		if(this._options) {
			items = this._options.asArray();
			return items[0] || null;
		}
		return null;
	};

	FormPanelDropdown.prototype.getSelectedText = function () {
		return jQuery(this.html).find("option:selected").text();
	};

	FormPanelDropdown.prototype.getSelectedData = function () {
		return jQuery(this.html).find("option:selected").data("data");
	};

	FormPanelDropdown.prototype.setValue = function (value) {
		var firstOption;
		if(this._options) {
			if(this.existsValueInOptions(value)) {
				FormPanelField.prototype.setValue.call(this, value);
			} else {
				firstOption = this._getFirstAvailableOption();
				if (firstOption) {
					firstOption = typeof this._valueField === "function" ? this._valueField(this, firstOption) : firstOption[this._valueField];
				} else {
					firstOption = "";
				}
				FormPanelField.prototype.setValue.call(this, firstOption || "");
			}
		}
		return this;
	};

	FormPanelDropdown.prototype.clearOptions = function () {
		jQuery(this._htmlControl[0]).empty();
		this._options.clear();
		this._value = "";
		return this;
	};

	FormPanelDropdown.prototype._paintOption = function (item) {
		var option = this.createHTMLElement('option');
		option.label = option.textContent = item[this._labelField];
		option.value = typeof this._valueField === 'function' ? this._valueField(this, item) : item[this._valueField];
		jQuery(option).data("data", item);
		this._htmlControl[0].appendChild(option);
		return this;
	};

	FormPanelDropdown.prototype.addOption = function (option) {
		var newOption;
		if(typeof option === 'object') {
			newOption = cloneObject(option);
			this._options.insert(newOption);
			if(this.html && !this._massiveAction) {
				this._paintOption(newOption);
			}
		}
		return this;
	};

	FormPanelDropdown.prototype._paintOptions = function () {
		var i, options = this._options.asArray();
		if(this.html) {
			jQuery(this._htmlControl[0]).empty();
			for (i = 0; i < options.length; i += 1) {
				this._paintOption(options[i]);
			}	
		}
		return this;
	};

	FormPanelDropdown.prototype.setOptions = function (options) {
		var i, value;
		if(!jQuery.isArray(options)) {
			throw new Error("setOptions(): The parameter must be an array.");
		}
		value = this._value;
		this._massiveAction = true;
		this.clearOptions();
		for(i = 0; i < options.length; i += 1) {
			this.addOption(options[i]);
		}
		this._paintOptions();
		this._massiveAction = false;
		this.setValue(value);
		return this.html;
	};

	FormPanelDropdown.prototype.reset = function () {};

	FormPanelDropdown.prototype._createControl = function () {
		if(!this._htmlControl[0]) {
			this._htmlControl[0] = this.createHTMLElement("select");
			this._htmlControl[0].name = this._name;
			FormPanelField.prototype._createControl.call(this);
		}
		return this;
	};

	FormPanelDropdown.prototype.createHTML = function () {
		if (!this.html) {
			FormPanelField.prototype.createHTML.call(this);
			this._paintOptions();
			this._setValueToControl(this._value);
			this._value = this._getValueFromControl();
		}
		return this;
	};

//FormPanelRadio
	var FormPanelRadio = function (settings) {
		FormPanelField.call(this, settings);
		this._options = [];
		FormPanelRadio.prototype.init.call(this, settings);
	};

	FormPanelRadio.prototype = new FormPanelField();
	FormPanelRadio.prototype.constructor = FormPanelRadio;
	FormPanelRadio.prototype.type = "FormPanelRadio";

	FormPanelRadio.prototype.init = function(settings) {
		var defaults = {
			options: []
		};

		jQuery.extend(true, defaults, settings);

		this.setOptions(defaults.options)
			.setValue(defaults.value !== undefined ? defaults.value : this._value);
	};

	FormPanelRadio.prototype._setValueToControl = function (value) {
		var i, control;
		for (i = 0; i < this._htmlControl.length; i += 1) {
			control = jQuery(this._htmlControl[i]).find("input").get(0);
			if (control.value === value) {
				control.checked = true;
			} else {
				control.checked = false;
			}
		}
		return this;
	};

	FormPanelRadio.prototype.setValue = function (value) {
		FormPanelField.prototype.setValue.call(this, value);
		if (this._htmlControl.length) {
			this._value = this._getValueFromControl();
		}
		return this;
	};

	FormPanelRadio.prototype.setOptions = function (options) {
		var i;
		if (!jQuery.isArray(options)) {
			throw new Error("setOptions(): The parameter must be an array.");
		}
		for (i = 0; i < options.length; i += 1) {
			if (options[i].selected === true) {
				this._value = options[i].value;
			}
		}
		this._options = options;
		return this;
	};

	FormPanelRadio.prototype._getValueFromControl = function() {
		var $items, i, value = "";

		if (this._htmlControl.length) {
			$items = jQuery(this._htmlControl[0]);

			for (i = 1; i < this._htmlControl.length; i += 1) {
				$items.add(this._htmlControl[i]);
			}

			$items = $items.find(":checked");

			if ($items.length) {
				value = $items.val();
			}
		}	

		return value;
	};

	FormPanelRadio.prototype._createControl = function () {
		var i, option, label;
		if (!this._htmlControl.length) {
			for (i = 0; i < this._options.length; i += 1) {
				label = this.createHTMLElement('label');
				option = this.createHTMLElement('input');
				option.type = "radio";
				option.name = this._name;
				option.value = this._options[i].value;
				option.className = "adam formpanel-radio";
				option.checked= !!this._options[i].selected;
				label.appendChild(option);
				label.appendChild(document.createTextNode(this._options[i].label));
				this._htmlControl.push(label);
			}
			FormPanelField.prototype._createControl.call(this);
		}
		return this;
	};

//FormPanelCheckbox
	var FormPanelCheckbox = function (settings) {
		FormPanelField.call(this, settings);
	};

	FormPanelCheckbox.prototype = new FormPanelField();
	FormPanelCheckbox.prototype.constructor = FormPanelCheckbox;
	FormPanelCheckbox.prototype.type = 'FormPanelCheckbox';

	FormPanelCheckbox.prototype._setValueToControl = function (value) {
		if (this._htmlControl[0]) {
			this._htmlControl[0].checked = !!value;
		}
		return this;
	};

	FormPanelCheckbox.prototype._getValueFromControl = function () {
		return this._htmlControl[0].checked;
	};

	FormPanelCheckbox.prototype.setValue = function (value) {
		var preValue = this._value;
		this._setValueToControl(!!value);
		this._value = !!value;
		if (value !== preValue) {
			this.fireDependentFields();
		}
		return this;
	};

	FormPanelCheckbox.prototype._createControl = function () {
		if (!this._htmlControl.length) {
			this._htmlControl[0] =  this.createHTMLElement("input");
			this._htmlControl[0].type = "checkbox";
			this._htmlControl[0].name = this._name;
			FormPanelField.prototype._createControl.call(this);
		}

		return this;
	};
var ListPanel = function(settings) {
	CollapsiblePanel.call(this, settings);
	this._itemsContent = null;
	this._data = null;
	this._proxy = null;
	this._dataURL = null;
	this._autoload = null;
	this._dataRoot = null;
	this._htmlMessage = null;
	this._showingLoadingMessage = null;
	this._filter = [];
	this._filterMode = null; 
	this.onItemClick = null;
	this.onLoad = null;
	ListPanel.prototype.init.call(this, settings);
};

ListPanel.prototype = new CollapsiblePanel();
ListPanel.prototype.constructor = ListPanel;
ListPanel.prototype.type = "ListPanel";

ListPanel.prototype.init = function (settings) {
	var defaults = {
		items: [],
		itemsContent: "[list item]",
		data: null,
		onItemClick: null,
		dataURL: null,
		autoload: false,
		dataRoot: null,
		onLoad: null,
		filter: [],
		filterMode: 'inclusive', 
		fieldToFilter: null
	};

	jQuery.extend(true, defaults, settings);

	this._proxy = new SugarProxy();
	this._autoload = defaults.autoload;

	this.setFilterMode(defaults.filterMode)
		.setItemsContent(defaults.itemsContent)
		.setOnItemClickHandler(defaults.onItemClick)
		.setDataURL(defaults.dataURL)
		.setDataRoot(defaults.dataRoot)
		.setOnLoadHandler(defaults.onLoad);

	if(typeof this._dataURL === 'string' && this._autoload) {
		this.load();
	} else {
		if(jQuery.isArray(defaults.data)) {
			this.setDataItems(defaults.data, defaults.fieldToFilter, defaults.filter);
		} else {
			this.setItems(defaults.items);
		}
	}
};

ListPanel.prototype.setFilterMode = function (filterMode) { 	 	
	if (filterMode !== 'inclusive' && filterMode !== 'exclusive') { 	 	
		throw new Error('setFilterMode(): The value for the parameter should be \"inclusive\" or \"exclusive\"'); 	 	
	} 	 	
	this._filterMode = filterMode; 	 	
	return this; 	 	
}; 	 	
 	 	
ListPanel.prototype.getFilterMode = function (filterMode) { 	 	
	return this._filterMode; 	 	
};

ListPanel.prototype.setOnLoadHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("onLoadHandler(): The parameter must be a function or null.");
	}
	this.onLoad = handler;
	return this;
};

ListPanel.prototype._checkItemsNum = function () {
	if(this._items.getSize() === 0) {
		this.showMessage("[0 items]");
	} else {
		this.removeMessage();
	}
	return this;
};

ListPanel.prototype.setDataRoot = function (dataRoot) {
	if (!(dataRoot === null || typeof dataRoot === 'string')) {
		throw new Error("setDataRoot(): The parameter must be a string or null.");
	}
	this._dataRoot = dataRoot;
	return this;
};

ListPanel.prototype._createMessageBox = function () {
	var element;
	if (!this._htmlMessage) {
		element = this.createHTMLElement("div");
		element.className = "adam list-panel-message";
		this._htmlMessage = element;
	} else {
		element = this._htmlMessage;
	}
	return element;
};

ListPanel.prototype.showMessage = function (message) {
	var element = this._createMessageBox();
	this._showingLoadingMessage = false;
	jQuery(element).empty();
	if (isHTMLElement(message)) {
		element.appendChild(message);
	} else if (typeof message === 'string') {
		element.textContent = message;	
	}
	$(this._htmlBody).prepend(this._htmlMessage);
	return this;
};

ListPanel.prototype.removeMessage = function () {
	jQuery(this._htmlMessage).remove();
	this._showingLoadingMessage = false;
	return this;
};

ListPanel.prototype._onLoadDataError = function () {
	var that = this;
	return function (httpError) {
		var i = that.createHTMLElement("strong");
		i.appendChild(document.createTextNode("An error occurred, please try again."));
		that.showMessage(i);
	};
};

ListPanel.prototype._onLoadDataSuccess = function() {
	var that = this;
	return function (data) {
		var items = that._dataRoot ? data[that._dataRoot] : data;
		that.removeMessage()
			.setDataItems(items)
			._checkItemsNum();
		if (typeof that.onLoad === 'function') {
			that.onLoad(that, data);
		}
	};
};

ListPanel.prototype._showLoadingMessage = function() {
	var element, icon;
	if (this._showingLoadingMessage) {
		return this;
	}
	element = this.createHTMLElement("span");
	icon = this.createHTMLElement("i");
	icon.className = "adam list-panel-spinner icon-spinner icon-spin";
	element.appendChild(icon);
	element.appendChild(document.createTextNode("loading..."));
	
	this.showMessage(element);
	this._showingLoadingMessage = true;
	return this;
};

ListPanel.prototype.load = function() {
	if(typeof this._dataURL !== 'string') {
		throw new Error("load(): The url wasn't set properly.");
	}
	this._proxy.url = this._dataURL;
	this.clearItems();
	this._showLoadingMessage();
	this._proxy.getData(null, {
		success: this._onLoadDataSuccess(),
		error : this._onLoadDataError()
	});
	return this;
};

ListPanel.prototype.setDataURL = function (dataURL) {
	if(!(dataURL === null || typeof dataURL === 'string')) {
		throw new Error("setDataURL(): The parameter must be a string or null.");
	}
	this._dataURL = dataURL;
	return this;
};

ListPanel.prototype.setOnItemClickHandler = function (handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnItemClickHandler(): The parameter must be a function or null.");
	}
	this.onItemClick = handler;
	return this;
};

ListPanel.prototype._onItemClickHandler = function () {
	var that = this;
	return function (item) {
		if(typeof that.onItemClick === 'function') {
			that.onItemClick(that, item);
		}
		that._onValueAction(item);
	};
};

ListPanel.prototype.addDataItem = function (data) {
	var newItem;
	if(typeof data !== 'object') {
		throw new Error("addDataItem(): The parameter must be an object.");
	}
	newItem = {
		data: data
	};
	this.addItem(newItem);
	return this;
};

ListPanel.prototype._filterData = function (data, fieldToFilter, filter) {
	var filteredData = [], i, validationFunction = false, that = this;;

	if (jQuery.isArray(filter) && filter.length) {
		validationFunction = function (data) {
			var i = 0;
			if (that._filterMode === 'inclusive') {
				return filter.indexOf(data[fieldToFilter]) >= 0;
			} else { 	 	
				return filter.indexOf(data[fieldToFilter]) === -1; 	 	
			}
		};
	} else if (typeof filter === 'string') {
		validationFunction = function (data) {
			return filter.toLowerCase() === data[fieldToFilter].toLowerCase();
		};
	} else if (typeof filter === 'function') {
		validationFunction = filter;
	}

	if (typeof fieldToFilter === 'string' && validationFunction) {
		for (i = 0; i < data.length; i += 1) {
			if (validationFunction(data[i])) {
				filteredData.push(data[i]);
			}
		}
		return filteredData;
	}

	return data;
};

ListPanel.prototype.getFilter = function () {
	return this._filter.slice(0);
};

ListPanel.prototype.setDataItems = function (data, fieldToFilter, filter) {
	var i;
	if(jQuery.isArray(data)) {
		this._massiveAction = true;
		data = this._filterData(data, fieldToFilter, filter);
		this.clearItems();
		for (i = 0; i < data.length; i += 1) {
			this.addDataItem(data[i]);
		}
		this._filter = filter || [];
		this._paintItems();
		this._massiveAction = false;
	}
	return this;
};

ListPanel.prototype.setItemsContent = function (itemsContent) {
	if (!(typeof itemsContent === 'string' || typeof itemsContent === 'function')) {
		throw new Error("setItemsContent(): The parameter must be a string or a function.");
	}
	this._itemsContent = itemsContent;
	return this;
};

ListPanel.prototype.setItems = function(items) {
	if(this._itemsContent) {
		CollapsiblePanel.prototype.setItems.call(this, items);
	}
	return this;
};

ListPanel.prototype.addItem = function (item) {
	var newItem;
	if(item instanceof ListItem) {
		newItem = item;
	} else {
		if (!item.text) {
			item.text = this._itemsContent;
		}
		newItem = new ListItem(item);
	}
	newItem.setOnClickHandler(this._onItemClickHandler());
	CollapsiblePanel.prototype.addItem.call(this, newItem);
	return this;
};

ListPanel.prototype._createBody = function () {
	var element = this.createHTMLElement('ul');
	element.className = 'list-panel';
	return element;
};

ListPanel.prototype.getValueObject = function (item) {
	return item.getData();
};

/*ListPanel.prototype.createHTML = function () {
	if(!this.html) {
		this.html = this.createHTMLElement("div");
		this.html.className = "adam list-panel";
		CollapsiblePanel.prototype.createHTML.call(this);
	}
	return this.html;
};*/
var MultipleCollapsiblePanel = function (settings) {
	CollapsiblePanel.call(this, jQuery.extend(true, {bodyHeight: 100}, settings));
	this._selectedPanel = null;
	this._panelList = null;
	this._htmlContent = null;
	this._htmlContentHeader = null;
	this._htmlContentTitle = null;
	this._lastSelectedPanel = null;
	this._selectedPanel = null;
	this._fastAccessObject = {};
	MultipleCollapsiblePanel.prototype.init.call(this, settings);
};

MultipleCollapsiblePanel.prototype = new CollapsiblePanel();
MultipleCollapsiblePanel.prototype.constructor = MultipleCollapsiblePanel;
MultipleCollapsiblePanel.prototype.type = "MultipleCollapsiblePanel";

MultipleCollapsiblePanel.prototype.init = function () {
	this._panelList = new ListPanel({
		itemsContent: this._panelListItemContent(),
		onItemClick: this._onPanelListItemClick(),
		collapsed: false
	});
};

MultipleCollapsiblePanel.prototype.getItem = function (item) {
	var searchedItem = null;

	if (typeof item === 'string') {
		searchedItem = this._items.find('id', item);
	} else if (typeof item === 'number') {
		searchedItem = this._items.get(item);
	} else if (item instanceof CollapsiblePanel && this.isParentOf(item)) {
		searchedItem = item;
	}

	return searchedItem;
};

MultipleCollapsiblePanel.prototype.disableItem = function(item) {
	var itemToChange = this.getItem(item);

	if (itemToChange) {
		itemToChange.disable();
	}
	return this;
};

MultipleCollapsiblePanel.prototype.enableItem = function(item) {
	var itemToChange = this.getItem(item);

	if (itemToChange) {
		itemToChange.enable();
	}
	return this;
};

MultipleCollapsiblePanel.prototype._onItemEnablementStatusChange = function () {
	var that = this;
	return function (item, active) {
		var accessObject = that._fastAccessObject[item.id],
			listItem = accessObject.listItem;
		listItem.setVisible(active);
		if (active) {
			accessObject.panel.expand();
		} else {
			if (!that.isCollapsed() && that._selectedPanel === item) {
				that.displayMenu(true);
			}	
		}
	};
};

MultipleCollapsiblePanel.prototype._panelListItemContent = function () {
	var that = this;
	return function (listItem, data) {
		var a = this.createHTMLElement("a"), 
			span = this.createHTMLElement("span"), 
			i = this.createHTMLElement("i");
		a.className = "adam list-item-content";
		i.className = "adam list-item-arrow icon-circle-arrow-right";
		span.textContent = data["text"];
		a.appendChild(span);
		a.appendChild(i);
		return a;
	};
};

MultipleCollapsiblePanel.prototype._onPanelListItemClick = function () {
	var that = this;
	return function (listPanel, item) {
		that.displayPanel(item.getData().id);
	};
};

MultipleCollapsiblePanel.prototype._clearContent = function () {
	var nodes;
	if (this._htmlContent) {
		nodes = this._htmlContent.childNodes; 
		while (nodes.length > 1) {
			if (nodes[0].remove) {
				this._htmlContent.lastChild.remove();
			} else {
				this._htmlContent.lastChild.removeNode(true);
			}
		}
	}

	return true;
};

MultipleCollapsiblePanel.prototype.expand = function (noAnimation) {
	this.displayMenu(true);
	CollapsiblePanel.prototype.expand.call(this, noAnimation);
	return this;
};

/*MultipleCollapsiblePanel.prototype.isParentOf = function (panel) {
	return !!this._items.indexOf(panel);
};*/

MultipleCollapsiblePanel.prototype.displayPanel = function (panel) {
	var panelToDisplay = this._items.find("id", panel), bodyHeight, contentHeaderHeight, w;
	if(this._selectedPanel !== panelToDisplay) {
		this._selectedPanel = panelToDisplay;
		if(this.html) {
			if (this._lastSelectedPanel !== panelToDisplay) {
				this._selectedPanel.getHTML();
				this._clearContent();
				this._htmlContentTitle.textContent = this._selectedPanel.getTitle();
				this._htmlContent.appendChild(this._selectedPanel._htmlBody);
				bodyHeight = jQuery(this._htmlBody).innerHeight();
				contentHeaderHeight = jQuery(this._htmlContentHeader).outerHeight();
				this._selectedPanel._htmlBody.style.height = (bodyHeight - contentHeaderHeight) + "px";
			}

			w = $(this._htmlBody).innerWidth();
			this._htmlContent.style.left = w + "px";
			jQuery(this._panelList._htmlBody).animate({
				left: "-=" + w + "px"
			});
			jQuery(this._htmlContent).animate({
				left: 0
			});
		}
	}
};

MultipleCollapsiblePanel.prototype.displayMenu = function (noAnimation) {
	var w, selectedPanel;
	if (this._selectedPanel) {
		selectedPanel = this._selectedPanel;
		this._lastSelectedPanel = this._selectedPanel;
		this._selectedPanel = null;
		this._panelList._htmlBody.scrollTop = 0;
		w = parseInt(this._panelList._htmlBody.style.left, 10) * -1;//jQuery(this._htmlBody).innerWidth(); //jQuery(this._panelList._htmlBody).outerWidth();
		if (noAnimation) {
			this._panelList._htmlBody.style.left = "0px";
			this._htmlContent.style.left = w + "px";
		} else {
			jQuery(this._panelList._htmlBody).add(this._htmlContent).animate({
				left: "+=" + w + "px"
			});
		}
		if (typeof selectedPanel.onCollapse === 'function') {
			selectedPanel.onCollapse(selectedPanel);
		}
	}
	return this;
};

MultipleCollapsiblePanel.prototype.setBodyHeight = function (height) {
	if (isNaN(height)) {
		throw new Error("setBodyHeight(): The parameter must be a number.");
	}
	this._bodyHeight = height;
	if(this._htmlBody) {
		this._htmlBody.style.maxHeight = this._htmlBody.style.height = height + "px";
	}
	return this;
};

MultipleCollapsiblePanel.prototype.clearItems = function () {
	this.displayMenu();
	if (this._panelList) {
		this._panelList.clearItems();
	}
	this._items.clear();
	return this;
};

MultipleCollapsiblePanel.prototype._paintItems = function () {
	var i, items;
	if (this._panelList) {
		items = this._items.asArray();
		this._panelList.clearItems();
		for (i = 0; i < items.length; i += 1) {
			this._paintItem(items[i]);
		}
	}
	return this;
};

MultipleCollapsiblePanel.prototype._paintItem = function (item) {
	var items;
	if (this._panelList) {
		this._panelList.addItem({
			data: {
				id: item.id,
				text: item.getTitle()
			},
			visible: !item.isDisabled()
		});
		items = this._panelList.getItems();
		this._fastAccessObject[item.id] = {
			listItem: items[items.length - 1],
			panel: item
		};
	}
	return this;
};

MultipleCollapsiblePanel.prototype._createItem = function (item) {
	var newItem;
	//item.onValueAction = this._onSubpanelItemAction();
	switch (item.type) {
		case "form":
			newItem = new FormPanel(item);
			break;
		case "list":
			newItem = new ListPanel(item);
			break;
		default:
			throw new Error("_createItem(): The parameter has an invalid \"type\" property.");
	}
	return newItem;
};

MultipleCollapsiblePanel.prototype.getValueObject = function (args) {
	return args.value;
};

MultipleCollapsiblePanel.prototype._onValueAction = function (anyArgument) {
	if(typeof this.onValueAction === 'function') {
		this.onValueAction(anyArgument.panel, this.getValueObject(anyArgument));
	}
	return this;
};

MultipleCollapsiblePanel.prototype._onSubpanelItemAction = function () {
	var that = this;
	return function (panel, panelValue) {
		that._onValueAction({panel: panel, value: panelValue});
	};
};

MultipleCollapsiblePanel.prototype.addItem = function(item) {
	var itemToAdd;
	if (item instanceof CollapsiblePanel) {
		itemToAdd = item;
	} else if (typeof item === 'object') {
		itemToAdd = this._createItem(item);
	} else {
		throw new Error("addItem(): The parameter must be an instance of CollapsiblePanel or an object.");
	}
	itemToAdd.setParent(this)
		.setOnValueActionHandler(this._onSubpanelItemAction())
		.setOnEnablementStatusChangeHandler(this._onItemEnablementStatusChange())
		.disableAnimations();
	this._items.insert(itemToAdd.expand());

	if (!this._massiveAction) {
		this._paintItem(item);
	}
	return this;
};

MultipleCollapsiblePanel.prototype.removeItem = function (item) {
	var itemToRemove = this.getItem(item);

	if (itemToRemove) {
		this._items.remove(itemToRemove);
		delete this._fastAccessObject[itemToRemove.id];
		if (this.html) {
			if (itemToRemove.html.remove) {
				itemToRemove.html.remove()
			} else {
				itemToRemove.html.removeNode(true);
			}
		}
	}

	return this;
};

MultipleCollapsiblePanel.prototype._attachListeners = function () {
	var that;
	if(this.html && !this._attachedListeners) {
		that = this;
		CollapsiblePanel.prototype._attachListeners.call(this);
		jQuery(this._htmlContentBackButton).on('click', function() {
			that.displayMenu();
		});
	}
	return this;
};

MultipleCollapsiblePanel.prototype._createBody = function () {
	var body, content, contentHeader, contentTitle, backButton;
	if (!this._htmlBody) {
		body = this.createHTMLElement("div");
		//body.className = "adam multiple-panel-body";
		content = this.createHTMLElement("div");
		content.className = "adam multiple-panel-content";
		contentHeader = this.createHTMLElement("header");
		contentHeader.className = "adam multiple-panel-contentheader";
		contentTitle = this.createHTMLElement("span");
		contentTitle.className = "adam multiple-panel-title";
		backButton = this.createHTMLElement("i");
		backButton.className = "adam multiple-panel-back icon-circle-arrow-left";

		this._panelList.getHTML();
		this._panelList._htmlBody.className += " adam-main-list";

		contentHeader.appendChild(contentTitle);
		contentHeader.appendChild(backButton);
		content.appendChild(contentHeader);
		body.appendChild(this._panelList._htmlBody);
		body.appendChild(content);

		this._htmlContent = content;
		this._htmlContentHeader = contentHeader;
		this._htmlContentTitle = contentTitle;
		this._htmlContentBackButton = backButton;
		this._htmlBody = body;
	}
	return this._htmlBody;
};

MultipleCollapsiblePanel.prototype.createHTML = function () {
	if (!this.html) {
		CollapsiblePanel.prototype.createHTML.call(this);
		this.html.className += " multiple-panel";
	}
	return this.html;
};

/*MultipleCollapsiblePanel.prototype._onValueAction = function (anyArgument) {
	if(typeof this.onValueAction === 'function') {
		this.onValueAction(anyArgument.panel, this.getValueObject(anyArgument));
	}
	return this;
};

MultipleCollapsiblePanel.prototype.getValueObject = function (args) {
	return args.value;
};



MultipleCollapsiblePanel.prototype.setOnCloseHandler = function(handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnCloseHandler(): The parameter must be a function or null.");
	}
	this.onClose = handler;
	return this;
};

MultipleCollapsiblePanel.prototype._createItem = function (item) {
	var newItem;
	//item.onValueAction = this._onSubpanelItemAction();
	switch (item.type) {
		case "form":
			newItem = new FormPanel(item);
			break;
		case "list":
			newItem = new ListPanel(item);
			break;
		default:
			throw new Error("_createItem(): The parameter has an invalid \"type\" property.");
	}
	return newItem;
};

MultipleCollapsiblePanel.prototype._paintItems = function () {
	if(this._items.getSize() > 0) {
		this.selectPanel(0);
	}
	return this;
};

MultipleCollapsiblePanel.prototype.addItem = function(item) {
	var itemToAdd;
	if (item instanceof CollapsiblePanel) {
		itemToAdd = item;
	} else if (typeof item === 'object') {
		itemToAdd = this._createItem(item);
	} else {
		throw new Error("addItem(): The parameter must be an instance of CollapsiblePanel or an object.");
	}
	itemToAdd.setOnValueActionHandler(this._onSubpanelItemAction());
	this._items.insert(itemToAdd.expand());
	return this;
};



MultipleCollapsiblePanel.prototype.selectPanel = function (panel) {
	var panelToDisplay;
	if (typeof panel === 'number') {
		panelToDisplay = this._items.get(panel);
	} else if (panel instanceof CollapsiblePanel) {
		if(this.isParentOf(panel)) {
			panelToDisplay = panel;
		} else {
			throw new Error("selectPanel(): The panel to show must belong to the current parent panel.");
		}
	} else {
		throw new Error("selectPanel(): The parameter must be a number or an instance of CollapsiblePanel.");
	}
	if(this._selectedPanel !== panelToDisplay) {
		this._selectedPanel = panelToDisplay;
		if(this.html) {
			this._selectedPanel.getHTML();
			this._clearBody();
			this.setTitle(this._selectedPanel._title);
			this._htmlBody.appendChild(this._selectedPanel._htmlBody);
		}
	}
};

MultipleCollapsiblePanel.prototype._createBody = function () {
	var element = this.createHTMLElement('div');
	element.className = 'adam multiple-panel-body';
	return element;
};

MultipleCollapsiblePanel.prototype._addPanelToList = function (item, index) {
	var li, a;
	if (this._htmlPanelList) {
		li = this.createHTMLElement('li');
		a = this.createHTMLElement('a');
		a.setAttribute("data-panel-index", index !== undefined ? index : this._items.getSize() - 1);
		a.className = "adam collapsible-panel-listitem";
		a.href = "#";
		a.textContent = item._title;
		li.appendChild(a);
		this._htmlPanelList.appendChild(li);
	} 
	return this;
};

MultipleCollapsiblePanel.prototype._updatePanelList = function () {
	var i, items;
	if(this._htmlPanelList) {
		items = this._items.asArray();
		jQuery(this._htmlPanelList).empty();
		for(i = 0; i < items.length; i++) {
			this._addPanelToList(items[i], i);
		}
	}
	return this;
};

MultipleCollapsiblePanel.prototype._onPanelListItemClick = function () {
	var that = this;
	return function (e) {
		var index = parseInt(this.getAttribute("data-panel-index"));
		e.preventDefault();
		that.selectPanel(index);
	};
};

MultipleCollapsiblePanel.prototype._onClose = function () {
	var that = this;
	return function () {
		if(typeof that.onClose === 'function') {
			that.onClose(that);
		}
	};
};

MultipleCollapsiblePanel.prototype._attachListeners = function () {
	var that;
	if(this._htmlPanelList && this.html && !this._attachedListeners) {
		CollapsiblePanel.prototype._attachListeners.call(this);
		jQuery(this._htmlPanelList).on('click', '.collapsible-panel-listitem', this._onPanelsListItemClick());
		jQuery(this._htmlCloseButton).on('click', this._onClose());
		this._attachedListeners = true;
	}
	return this;
};

MultipleCollapsiblePanel.prototype.createHTML = function () {
	var htmlButtonToolbar, listButton, closeButton, panelList, dropdownContainer;
	if(!this.html) {
		CollapsiblePanel.prototype.createHTML.call(this);
		//creates the bootstrap container for button group
		htmlButtonToolbar = this.createHTMLElement('div');
		htmlButtonToolbar.className = 'btn-group pull-right';
		//Create the list for the button
		listButton = this.createHTMLElement('button');
		listButton.className = 'btn btn-mini icon-caret-down dropdown-toggle';
		listButton.id = this.id + "-panels-button";
		listButton.setAttribute("data-toggle", "dropdown");
		//Create the button for closing
		closeButton = this.createHTMLElement('button');
		closeButton.className = "adam collapsible-panel-closebutton btn btn-mini icon-remove";
		//Create the panels list
		panelList = this.createHTMLElement('ul');
		panelList.className = "adam collapsible-panel-panelslist dropdown-menu";
		panelList.setAttribute("role", "menu");
		panelList.setAttribute("aria-labelledby", this.id + "-panels-button");

		htmlButtonToolbar.appendChild(listButton);
		htmlButtonToolbar.appendChild(panelList);
		htmlButtonToolbar.appendChild(closeButton);
		jQuery(this._htmlHeader).prepend(htmlButtonToolbar);

		this._htmlButtonToolbar = htmlButtonToolbar;
		this._htmlCloseButton = closeButton;
		this._htmlPanelListButton = listButton;
		this._htmlPanelList = panelList;
		this._updatePanelList();

		this._attachListeners();
	}
	return this.html;
};

*/
//Singleton
var FieldPanelItemFactory = (function () {
	var products = {
		"button": FieldPanelButton,
		"buttongroup": FieldPanelButtonGroup,
		"list": ListPanel,
		"form": FormPanel,
		"multiple": MultipleCollapsiblePanel,
		"item_container": ItemContainer
	};
	return {
		hasProduct: function (productName) {
			return !!products[productName];
		},
		canProduce: function (productClass) {
			var key;
			for (key in products) {
				if(products.hasOwnProperty(key)) {
					if(products[key] === productClass) {
						return true;
					}
				}
			}
			return false;
		},
		isProduct: function(productObject) {
			var key;
			for (key in products) {
				if(products.hasOwnProperty(key)) {
					if(productObject instanceof products[key]) {
						return true;	
					}
				}
			}
			return false;	
		},
		make: function(settings) {
			var productName = settings.type, Constructor;
			if(!this.hasProduct(productName)) {
				throw new Error("make(): The product \"" + productName + "\" can't be produced by this factory.");
			}
			Constructor = products[productName];
			return new Constructor(settings);
		}
	};
}());
var FieldPanel = function (settings) {
	Element.call(this, settings);
	this._open = null;
	this._massiveAction = false;
	this._onItemValueAction = null;
	this._items = new ArrayList();
	this._open = false;
	this._owner = null;
	this._matchOwnerWidth = true;
	this._appendTo = null;
	this._attachedListeners = false;
	this._className = null;
	this.onOpen = null;
	this.onClose = null;
	FieldPanel.prototype.init.call(this, settings);
};

FieldPanel.prototype = new Element();
FieldPanel.prototype.constructor = FieldPanel;

FieldPanel.prototype.init = function (settings) {
	var defaults = {
		items: [],
		onItemValueAction: null,
		open: false,
		owner: null,
		matchOwnerWidth: true,
		appendTo: document.body,
		className: "",
		onOpen: null,
		onClose: null
	};

	jQuery.extend(true, defaults, settings);
	
	this.setOwner(defaults.owner)
		.setAppendTo(defaults.appendTo)
		.setMatchOwnerWidth(defaults.matchOwnerWidth)
		.setItems(defaults.items)
		.setOnItemValueActionHandler(defaults.onItemValueAction)
		.setClassName(defaults.className);

	if (defaults.open) {
		this.open();
	} else {
		this.close();
	}

	this.setOnOpenHandler(defaults.onOpen)
		.setOnCloseHandler(defaults.onClose);
};

FieldPanel.prototype.setClassName = function (cName) {
	if (typeof cName !== 'string') {
		throw new Error("setClassName(): The parameter must be a string.");
	}

	this._className = cName;

	if (this.html) {
		jQuery(this.html).addClass(cName);
	}

	return this;
};

FieldPanel.prototype.setOnOpenHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("The parameter must be a function or null.");
	}
	this.onOpen = handler;
	return this;
};

FieldPanel.prototype.setOnCloseHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error('The parameter must be a function or null.');
	}
	this.onClose = handler;
	return this;
};

FieldPanel.prototype.setMatchOwnerWidth = function (match) {
	this._matchOwnerWidth = !!match;
	if (this._open) {
		this._append();
	}
	return this;
};

FieldPanel.prototype.setAppendTo = function (appendTo) {
	if (!(isHTMLElement(appendTo) || typeof appendTo === 'function' || appendTo instanceof Base)) {
		throw new Error("setAppendTo(): The parameter must be an HTML element or an instance of Base.");
	}
	this._appendTo = appendTo;
	if (this.isOpen()) {
		this._append();
	}
	return this;
};

FieldPanel.prototype.setWidth = function (w) {
	Element.prototype.setWidth.call(this, w);
	if (this.html && typeof w === "number") {
        this.style.addProperties({"min-width": this.width});
    }
	return this;
};

FieldPanel.prototype.open = function () {
	if (!this._open) {
		//if (this.html) {
			this.getHTML();
			this._append();
			jQuery(this.getHTML()).slideDown();
		//}
		this._open = true;
		if (typeof this.onOpen === 'function') {
			this.onOpen(this);
		}
	}
	return this;
};

FieldPanel.prototype.close = function () {
	if (this._open) {
		if (this.html) {
			this.html.style.display = "none";
		}
		this._open = false;
		if (typeof this.onClose === 'function') {
			this.onClose(this);
		}
	}
	return this;
};

FieldPanel.prototype.isOpen = function () {
	return this._open;
};

FieldPanel.prototype.getOwner = function () {
	return this._owner;
};

FieldPanel.prototype.setOnItemValueActionHandler = function (handler) {
	if(!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnItemValueActionHandler(): the parameter must be a function or null.");
	}
	this.onItemValueAction = handler;
	return this;
};

FieldPanel.prototype._append = function () {
	var position, appendTo = this._appendTo, owner = this._owner, offsetHeight = 1, zIndex = 0, siblings, aux;
	if (owner) {
		if (!isHTMLElement(owner)) {
			owner = owner.html;
		}
		offsetHeight = owner.offsetHeight;
	}
	if (typeof appendTo === 'function') {
		appendTo = appendTo.call(this);
	}
	if (!isHTMLElement(appendTo)) {
		appendTo = appendTo.html;
	}
	siblings = appendTo.children;
	for (i = 0; i < siblings.length; i += 1) {
		aux = jQuery(siblings[i]).zIndex();
		if (aux > zIndex) {
			zIndex = aux;
		} 
	}

	this.setZOrder(zIndex + 1);

	if (!owner || isInDOM(owner)) {
		appendTo.appendChild(this.html);
	}
	if (owner) {
		this.setWidth(this._matchOwnerWidth ? owner.offsetWidth : this.width);
		position = getRelativePosition(owner, appendTo);
	} else {
		this.setWidth(this.width);
		position = {left: 0, top: 0};
	}
	this.setPosition(position.left, position.top + offsetHeight - 1);
	return this;
};

FieldPanel.prototype.setOwner = function (owner) {
	if(!(owner === null || owner instanceof Element || isHTMLElement(owner))) {
		throw new Error("setOwner(): The parameter must be an instance of Element or null.");
	}

	this._owner = owner;
	if (this.isOpen()) {
		this._append();
	}
	return this;
};

FieldPanel.prototype._onItemValueActionHandler = function () {
	var that = this;

	return function (item, valueObject) {
		if(typeof that.onItemValueAction === 'function') {
			that.onItemValueAction(that, item, valueObject);
		}
	};
};

FieldPanel.prototype.addItem = function (item) {
	if(!FieldPanelItemFactory.isProduct(item)) {
		item = FieldPanelItemFactory.make(item);
	}
	if(!FieldPanelItemFactory.isProduct(item)) {
		throw new Error("addItem(): The parameter must be acceptable by this parent.");
	} else {
		item.onValueAction = this._onItemValueActionHandler();
		this._items.insert(item);
	}
	if(!this._massiveAction && this.html) {
		this._paintItem(item);
	}
	return this;
};

FieldPanel.prototype.clearItems = function () {
	this._items.clear();
	jQuery(this.html).empty();
	return this;
};

FieldPanel.prototype._paintItem = function (item) {
	this.html.appendChild(item.getHTML());
	return this;
};

FieldPanel.prototype._paintItems = function () {
	var i, items;
	if(this.html) {
		items = this._items.asArray();
		for (i = 0; i < items.length; i++) {
			this._paintItem(items[i]);
		}
	}
	return this;
};

FieldPanel.prototype.setItems = function (items) {
	var i ;
	this._massiveAction = true;
	this.clearItems();
	for (i = 0; i < items.length; i++) {
		this.addItem(items[i]);
	}
	this._paintItems();
	this._massiveAction = false;
	return this;
};

FieldPanel.prototype.getItems = function () {
	return this._items.asArray();
};

FieldPanel.prototype.hideItem = function (itemIndex) {
	var itemToHide = this._items.get(itemIndex);
	if (itemToHide) {
		itemToHide.hide();
	}
	return this;
};

FieldPanel.prototype.showItem = function (itemIndex) {
	var itemToHide = this._items.get(itemIndex);
	if (itemToHide) {
		itemToHide.show();
	}
	return this;
};

FieldPanel.prototype.attachListeners = function () {
	var that = this;
	if (this.html && !this._attachedListeners) {
		jQuery(document).on("click", function (e) {
			var $selector = $(that.html);
			if (that._owner) {
				$selector = isHTMLElement(that._owner) ? $selector.add(that._owner) : $selector.add(that._owner.html);
			}
			if (!jQuery(e.target).closest($selector).length) {
				that.close();
			}
		});
		this._attachedListeners = true;
	}
	return this;
};

FieldPanel.prototype.createHTML = function () {
	/*if(!this.html) {
		this.html = this.createHTMLElement('div');
		this.html.className = 'adam field-panel';
		this._paintItems();
	}*/

	if(!this.html) {
		Element.prototype.createHTML.call(this);
		this.html.className = 'adam field-panel';
		this._paintItems();

		this.style.addProperties({
			position: "absolute",
			"min-width": this.width,
            height: "auto",
            zIndex: this.zOrder
        });
        this.html.style.display = this._open ? "" : "none";
        this.setClassName(this._className);
        this.attachListeners();
	}
	return this.html;
};

var DropdownSelector = function (options) {
    options.open = false;
    FieldPanel.call(this, options);
    this._owner = null;
    this.onChange = null;
    this.values = null;
    this.value = null;
    this._open = null;
    this._appendTo = null;
    this._matchOwnerWidth = null;
    DropdownSelector.prototype.init.call(this, options);
};

DropdownSelector.prototype = new FieldPanel();

DropdownSelector.prototype.type = 'DropboxSelector';

DropdownSelector.prototype.init = function (options) {
    var defaults = {
        width: 250,
        height: 'auto',
        owner: null,
        onChange: null,
        values: null,
        value: null,
        open: false,
        appendTo: document.body,
        matchOwnerWidth : true
    };
    $.extend(true, defaults, options);
    this.setMatchOwnerWidth(defaults.matchOwnerWidth)
        .setWidth(defaults.width)
        .setHeight(defaults.height)
        .setOwner(defaults.owner)
        .setOnChangeHandler(defaults.onChange)
        .setValues(defaults.values)
        .setValue(defaults.value)
        .setIsOpen(defaults.open)
        .setAppendTo(defaults.appendTo)
        ;
};

DropdownSelector.prototype.setOwner = function (value) {
    this._owner = value;
    return this;
};

DropdownSelector.prototype.setIsOpen = function (value) {
    this._open = value;
    return this;
};

DropdownSelector.prototype.isPanelOpen = function () {
    return this._open;
};

DropdownSelector.prototype.setAppendTo = function (el) {
    this._appendTo = el;
    return this;
};

DropdownSelector.prototype.setWidth = function (w) {
    if (!(typeof w === 'number' ||
        (typeof w === 'string' && (w === "auto" || /^\d+(\.\d+)?(em|px|pt|%)?$/.test(w))))) {
        throw new Error("setWidth(): invalid parameter.");
    }
    this.width = w;
    if (this.html) {
        this.style.addProperties({width: this.width});
    }
    return this;
};

DropdownSelector.prototype.setHeight = function (h) {
    if (!(typeof h === 'number' ||
        (typeof h === 'string' && (h === "auto" || /^\d+(\.\d+)?(em|px|pt|%)?$/.test(h))))) {
        throw new Error("setHeight(): invalid parameter.");
    }
    this.height = h;
    if (this.html) {
        this.style.addProperties({height: this.height});
    }
    return this;
};

DropdownSelector.prototype.setMatchOwnerWidth = function (value) {
    this._matchOwnerWidth = value;
    return this;
};

DropdownSelector.prototype.open = function () {
    this.getHTML();
    if (!this.isPanelOpen()) {
        this._appendPanel();
        $(this.html).slideDown();
        this.setIsOpen(true);
    }
    return this;
};

DropdownSelector.prototype.close = function () {
    if (this.html) {
        this.html.style.display = "none";
    }
    this.setIsOpen(false);
};

DropdownSelector.prototype.setOnChangeHandler = function (handler) {
    if (!(handler === null || typeof handler === 'function')) {
        throw new Error("setOnChangeHandler(): the parameter must be a function or null.");
    }
    this.onChange = handler;
    return this;
};

DropdownSelector.prototype.setValues = function (combo) {
    this.values = combo;
    if (this.html) {
        this.createElements();
    }
    return this;
};

DropdownSelector.prototype.setValue = function (value) {
    this.value = value;
    if (this.html) {
        console.log('Update Value visually');
    }
    return this;
};

DropdownSelector.prototype.createHTML = function () {
    var list;
    FieldPanel.prototype.createHTML.call(this);
    this.style.applyStyle();



    list = new ListPanel({
        bodyHeight: 100, //Change later to 'auto'
        maxBodyHeight: 200,
        collapsed: false,
        headerVisible: false,
        itemsContent: "{{text}}"
    });

    this.addItem(list);
    this.listHtml = list;


    //this.style.addProperties({
    //    width: this.width,
    //    height: this.height,
    //    zIndex: this.zOrder
    //});
    this.style.addProperties({
        position: "absolute",
        height: "auto",
        'min-width' : 0,
        zIndex: this.zOrder
    });
    this.createElements();
    this.attachListeners();
    return this.html;
};

DropdownSelector.prototype.createElements = function () {
    var key;

    this.listHtml.clearItems();
    this.listHtml.addItem(new CloseListItem({
        data: {}
    }));
    if (jQuery.isArray(this.values)) {
        for (key = 0; key < this.values.length; key += 1) {
            this.listHtml.addDataItem({
                value: this.values[key].value,
                text: this.values[key].text
            });
        }
    } else {
        for (key in this.values) {
            this.listHtml.addDataItem({
                value: key,
                text: this.values[key]
            });
        }
    }
    return this;
};

DropdownSelector.prototype.attachListeners = function () {
    var self = this;
    if (this.html) {
        $(document).on("click", function (e) {
            var selector = "#" + self.id;
            if (e.target !== self.html && !$(e.target).parents(selector).length && e.target.parentNode !== self._owner) {
                self.close();
            }
        });
    }
    return this;
};

DropdownSelector.prototype._appendPanel = function () {
    var position, appendPanelTo = this._appendTo, owner = this._owner, offsetHeight = 1, zIndex = 0, siblings, aux;
    if (owner) {
        if (!isHTMLElement(owner)) {
            owner = owner.html;
        }
        offsetHeight = owner.offsetHeight;
    }
    if (typeof appendPanelTo === 'function') {
        appendPanelTo = appendPanelTo.call(this);
    }
    if (!isHTMLElement(appendPanelTo)) {
        appendPanelTo = appendPanelTo.html;
    }
    siblings = appendPanelTo.children;
    for (i = 0; i < siblings.length; i += 1) {
        aux = jQuery(siblings[i]).zIndex();
        if (aux > zIndex) {
            zIndex = aux;
        }
    }

    this.setZOrder(zIndex + 1);

    if (!owner || isInDOM(owner)) {
        appendPanelTo.appendChild(this.html);
    }
    if (owner) {
        this.setWidth(this._matchOwnerWidth ? owner.offsetWidth : this.width);
        position = getRelativePosition(owner, appendPanelTo);
    } else {
        this.setWidth(this.width);
        position = {left: 0, top: 0};
    }
    this.setPosition(position.left, position.top + offsetHeight - 1);
    return this;
};





var ExpressionControl = function(settings) {
	Element.call(this, settings);
	this._panel = null;
	this._operatorSettings = {};
	this._operatorPanel = null;
	this._evaluationSettings = {};
	this._evaluationPanel = null;
	this._evaluationPanels = {};
	this._variableSettings = null;
	this._variablePanel = null;
	this._constantSettings = null;
	this._constantPanel = null;
	this._constantPanels = {};
	this._attachedListeners = false;
	this.onChange = null;
	this._value = null;
	this._panelSemaphore = true; //true for close the panel, false to avoid closing.
	this._itemContainer = null;
	this._externalItemContainer = false;
	//this._owner = null;
	//this._matchOwnerWidth = true;
	this._proxy = null;
	//this._appendTo = null;
	this._expressionVisualizer = null;
	this._dateFormat = null;
	this._decimalSeparator = null;
	this._numberGroupingSeparator = null;
	this._auxSeparator = "|||";
	this.onOpen = null;
	this.onClose = null;
	ExpressionControl.prototype.init.call(this, settings);
};

ExpressionControl.prototype = new Element();
ExpressionControl.prototype.constructor = ExpressionControl;
ExpressionControl.prototype.type = "ExpressionControl";
ExpressionControl.prototype._regex = {
	string: /("(?:[^"\\]|\\.)*")|('(?:[^'\\]|\\.)*')/,
	datetime: /^\d{4}-((0[1-9])|(1[0-2]))-((0[1-9])|([12][0-9])|(3[01]))(\s((0[0-9])|(1[0-2])|(2[0-3])):[0-5][0-9]:[0-5][0-9])?$/,
	unittime: /^\d+[wdhm]$//*,
	number: /^[+-]?\d+(\.\d+)?$/*/
};

ExpressionControl.prototype._typeToControl = {
	"address": "text",
	"checkbox": "checkbox",
	"currency": "currency",
	"date": "date", 
	"datetime": "datetime", //
	"decimal": "number",
	"encrypt": "text",
	"dropdown": "dropdown",
	"float": "number",
	"email": "text",
	"name": "text",
	//"html": "html",
	//"iframe": "iframe",
	//"image": "image" ,
	"integer": "integer",
	"multiselect": "text", //"multiselect",
	//"flex relate": "flexrelate",
	"phone": "text",
	"radio": "radio",
	//"relate": "related",
	"textarea": "text",//"textarea",
	"url": "text",
	"textfield": "text" 
};

ExpressionControl.prototype.OPERATORS  = {
	"arithmetic": [
		{
			text: "+",
			value: "addition"
		},
		{
			text: "-",
			value: "substraction"
		},
		{
			text: "x",
			value: "multiplication"
		},
		{
			text: "/",
			value: "division"
		}
	],
	"logic": [
		{
			text: "AND", 
			value: "AND"
		},
		{
			text: "OR", 
			value:  "OR"
		},
		{
			text: "NOT", 
			value: "NOT"
		}
	],
	"comparison": [
		{
			text: "<", 
			value: "minor_than"
		}, 
		{
			text: "<=", 
			value: "minor_equals_than"
		}, 
		{
			text: "==", 
			value: "equals"
		}, 
		{
			text: ">=", 
			value: "major_equals_than"
		}, 
		{
			text: ">", 
			value: "major_than"
		}, 
		{
			text: "!=", 
			value: "not_equals"
		}
	],
	"group": [
		{
			text: "(",
			value: "("
		}, 
		{
			text: ")",
			value: ")"
		}
	]
};

ExpressionControl.prototype.init = function (settings) {
	var defaults = {
		width: 200,
		itemContainerHeight: 80, //only applicable when it is not external
		height: 'auto',
		operators: true,
		evaluation: false,
		variable: false,
		constant: true,
		onChange: null,
		owner: null,
		itemContainer: null,
		appendTo: document.body,
		matchOwnerWidth: true,
		expressionVisualizer: true,
		dateFormat: "yyyy-mm-dd",
		decimalSeparator: settings.numberGroupingSeparator === "." ? "," : ".",
		numberGroupingSeparator: settings.decimalSeparator === "," ? "." : ",",
		allowInput: true,
		onOpen: null,
		onClose: null,
		className: ""
	};

	jQuery.extend(true, defaults, settings);

	this._proxy = new SugarProxy();
	if (defaults.itemContainer instanceof ItemContainer) {
		this._itemContainer = defaults.itemContainer;
		this._externalItemContainer = true;
	} else {
		this._itemContainer = new ItemContainer({
			textInputMode: defaults.allowInput ? ItemContainer.prototype.textInputMode.ALL 
				: ItemContainer.prototype.textInputMode.NONE,
			width: '100%',
			height: defaults.itemContainerHeight
		});
	}

	this._panel = new FieldPanel({
		id: defaults.id,
		open: false,
		onItemValueAction: this._onPanelValueGeneration(),
		width: this.width,
		className: defaults.className || ""
	});

	this._itemContainer.setOnAddItemHandler(this._onChange())
		.setOnRemoveItemHandler(this._onChange())
		.setInputValidationFunction(this._inputValidationFunction())
		.setOnBeforeAddItemByInput(this._onBeforeAddItemByInput());

	this.setWidth(defaults.width)
		.setHeight(defaults.height)
		.setDateFormat(defaults.dateFormat)
		.setDecimalSeparator(defaults.decimalSeparator)
		.setNumberGroupingSeparator(defaults.numberGroupingSeparator)
		.setOwner(defaults.owner)
		.setAppendTo(defaults.appendTo)
		.setOperators(defaults.operators)
		.setEvaluations(defaults.evaluation)
		.setVariablePanel(defaults.variable)
		.setConstantPanel(defaults.constant)
		.setOnChangeHandler(defaults.onChange)
		.setMatchOwnerWidth(defaults.matchOwnerWidth)
		.setOnOpenHandler(defaults.onOpen)
		.setOnCloseHandler(defaults.onClose);

	if (defaults.expressionVisualizer) {
		this.showExpressionVisualizer();
	} else {
		this.hideExpressionVisualizer();
	}
};

ExpressionControl.prototype.setOnOpenHandler = function (handler) {
	this._panel.setOnOpenHandler(handler);
	return this;
};

ExpressionControl.prototype.setOnCloseHandler = function (handler) {
	this._panel.setOnCloseHandler(handler);
	return this;
};

ExpressionControl.prototype.getText = function () {
	return this._itemContainer.getText();
};

ExpressionControl.prototype.setDecimalSeparator = function (decimalSeparator) {
	if (!(typeof decimalSeparator === 'string' && decimalSeparator && decimalSeparator.length === 1 
		&& !/\d/.test(decimalSeparator) && !/[\+\-\*\/]/.test(decimalSeparator))) {
		throw new Error("setDecimalSeparator(): The parameter must be a single character different than a digit and "
			+ "arithmetic operator.");
	}
	if (decimalSeparator === this._numberGroupingSeparator) {
		throw new Error("setDecimalSeparator(): The decimal separator must be different from the number grouping " 
			+ "separator.");
	}
	this._decimalSeparator = decimalSeparator;
	return this;
};

ExpressionControl.prototype.setNumberGroupingSeparator = function (separator) {
	if (!(separator === null || (typeof separator === 'string' && separator.length <= 1))) {
		throw new Error("setNumberGroupingSeparator(): The parameter is optional should be a single character or "
			+ "null.");
	}
	if (separator === this._decimalSeparator) {
		throw new Error("setNumberGroupingSeparator(): The decimal separatpr must be different from the number grouping " 
			+ "separator.");
	}
	this._numberGroupingSeparator = separator;
	return this;
};

ExpressionControl.prototype.setDateFormat = function(dateFormat) {
	this._dateFormat = dateFormat;
	if (this._constantPanels.date) {
		this._constantPanels.date.getItem("date").setFormat(dateFormat);
	}
	return this;
};

/*ExpressionControl.prototype._parseInputToItem = function (input) {
	var trimmedText = jQuery.trim(input), type;
	if (typeof input !== 'string') {
		throw new Error("_parseInputToItemData(): The parameter must be a string.");
	}

	if (trimmedText === '+' || trimmedText === '-') {
		type = "MATH";
	} else if (this._regex.unittime.test(trimmedText)) {
		type = "UNIT_TIME";
	} else {
		type = "FIXED_DATE";
	}

	return this._createItemData(trimmedText, type);
};*/

ExpressionControl.prototype._onBeforeAddItemByInput = function () {
	var that = this;
	return function (itemContainer, newItem, input, index) {
		var data = that._parseInputToItem(input);
		if (data) {
			newItem.setFullData(data);	
		} else {
			return false;
		}
	};
};

ExpressionControl.prototype.isLeapYear = function (year) {
	if(year % 400 === 0 || year % 4 === 0) {
        return true;
    }
    return false;
};

ExpressionControl.prototype.isValidDateTime = function (date) {
	if (typeof date === 'string') {
		//TODO validation acccording to the set data format
		if (!this._regex.datetime.test(date)) {
			return false;
		}
		date = date.split("-");
		date[0] = parseInt(date[0], 10);
		date[1] = parseInt(date[1], 10);
		date[2] = parseInt(date[2], 10);

		if (date[1] <= 0 || date[2] <= 0 || date[1] > 12 || date[2] > 31) {
			return false;
		}
		if ((date[1] === 4 || date[1] === 6 || date[1] === 9) && date[0] > 30) {
			return false;
		}
		if ((!this.isLeapYear(date[0]) && date[2] > 28) || date[2] > 29) {
			return false;
		}
	} else {
		//TODO validations for other arguments data type
		return false;
	}
	return true;
};

ExpressionControl.prototype._inputValidationFunction = function () {
	var that = this;
	return function (itemContainer, input) {
		var trimmedText = jQuery.trim(input);
		switch (trimmedText) {
			case '+':
			case '-':
			case "NOW":
				return true;
			default:
				return that._regex.unittime.test(trimmedText) || that.isValidDateTime(trimmedText);
		}
	};
};

ExpressionControl.prototype.showExpressionVisualizer = function () {
	if (!this._externalItemContainer) {
		this._itemContainer.setVisible(true);
	}
	return this;
};

ExpressionControl.prototype.hideExpressionVisualizer = function () {
	if (!this._externalItemContainer) {
		this._itemContainer.setVisible(false);
	}
	return this;
};

ExpressionControl.prototype.setMatchOwnerWidth = function (match) {
	this._panel.setMatchOwnerWidth(!!match);
	return this;
};

ExpressionControl.prototype.setAppendTo = function (appendTo) {
	this._panel.setAppendTo(appendTo);
	return this;
};

ExpressionControl.prototype.isOpen = function() {
	return (this._panel && this._panel.isOpen()) || false;
};

ExpressionControl.prototype.getValueObject = function () {
	return this._itemContainer.getData();
};

ExpressionControl.prototype._onChange = function () {
	var that = this;
	return function (itemContainer, item, index) {
		var oldValue = that._value;
		that._value = itemContainer = JSON.stringify(itemContainer.getData());
		if (typeof that.onChange === 'function') {
			that.onChange(that, that._value, oldValue);
		}
	};
};

ExpressionControl.prototype.setOwner = function(owner) {
	this._panel.setOwner(owner);
	return this;
};

ExpressionControl.prototype.getOwner = function () {
	return this._panel.getOwner();
};

ExpressionControl.prototype.getValue = function () {
	return this._value;
};

ExpressionControl.prototype.setValue = function (value) {
	var i;
	if (typeof value === "string") {
		value = JSON.parse(value);
	} else if (!jQuery.isArray(value)) {
		throw new Error("The parameter must be a array formatted string or an object.");
	}

	this._itemContainer.clearItems();
	for (i = 0; i < value.length; i += 1) {
		this._itemContainer.addItem(this._createItem(value[i]));
	}
	return this;
};

ExpressionControl.prototype.setOnChangeHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("setOnChangeHandler(): the parameter must be a function or null.");
	}
	this.onChange = handler;
	return this;
}

ExpressionControl.prototype.setWidth = function (w) {
	if (!(typeof w === 'number' ||
		(typeof w === 'string' && (w === "auto" || /^\d+(\.\d+)?(em|px|pt|%)?$/.test(w))))) {
		throw new Error("setWidth(): invalid parameter.");
	}
	this.width = w;
    if (this.html) {
        this.style.addProperties({width: this.width});
    }
    return this;
};

ExpressionControl.prototype.setHeight = function (h) {
	if (!(typeof h === 'number' ||
		(typeof h === 'string' && (h === "auto" || /^\d+(\.\d+)?(em|px|pt|%)?$/.test(h))))) {
		throw new Error("setHeight(): invalid parameter.");
	}
	this.height = h;
    if (this.html) {
        this.style.addProperties({height: this.height});
    }
    return this;
};

ExpressionControl.prototype._getProperty = function (data, path) {
	var levels, i;
	if (data) {
		levels = path.split(".");
		for (i = 0; i < levels.length; i += 1) {
			data = data[levels[i]];
		}
	}
	return data;
};

ExpressionControl.prototype.setConstantPanel = function(settings) {
	var defaults = true;

	if (settings === false) {
		defaults = false;
	} else if (settings === true) {
		defaults = {
			basic: true,
			date: true,
			timespan: true
		};
	} else {
		defaults = jQuery.extend(true, defaults, settings);
	}

	this._constantSettings = defaults;

	if (this._constantPanel) {
		this._createBasicConstantPanel()
			._createDateConstantPanel()
			._createTimespanPanel();
	}

	return this;
};

ExpressionControl.prototype.setVariablePanel = function (settings) {
	var defaults = {
		dataURL: null,
		dataRoot: null,
		data: [],
		dataFormat: "tabular",
		dataChildRoot: null,
		textField: "text",
		valueField: "value",
		typeField: "type",
		typeFilter: null,
		filterMode: "inclusive", 
		moduleTextField: null,
		moduleValueField: null
	};

	if (settings === false) {
		defaults = false;
	} else {
		jQuery.extend(true, defaults, settings);
		if (defaults.dataURL) {
			if (typeof defaults.dataURL !== "string") {
				throw new Error("setVariablePanel(): The \"dataURL\" property must be a string.");
			}
			if (!(defaults.dataRoot === null || typeof defaults.dataRoot === "string")) {
				throw new Error("setVariablePanel(): The \"dataRoot\" property must be a string or null.");
			}	
			defaults.data = [];
		} else {
			if (!jQuery.isArray(defaults.data)) {
				throw new Error("setVariablePanel(): The \"data\" property must be an array.");
			}
		}
		
		if (defaults.dataFormat !== "tabular" && defaults.dataFormat !== "hierarchical") {
			throw new Error("setVariablePanel(): The \"dataFormat\" property only can have the \"hierarchical\" or " 
				+ "\"tabular\" values.");
		}
		if (typeof defaults.dataChildRoot !== "string" && defaults.dataFormat === "hierarchical") {
			throw new Error("setVariablePanel(): You set the \"dataFormat\" property to \"hierarchical\" so the " 
				+ "\"dataChildRoot\" property must be specified.");
		}
		if (typeof defaults.textField !== "string") {
			throw new Error("setVariablePanel(): The \"textField\" property must be a string.");
		}
		if (typeof defaults.valueField !== "string") {
			throw new Error("setVariablePanel(): The \"valueField\" property must be a string.");
		}
		if (typeof defaults.typeField !== "string") {
			throw new Error("setVariablePanel(): The \"typeField\" property must be a string.");
		}
		if (!(defaults.typeFilter === null || typeof defaults.typeFilter === "string" || jQuery.isArray(defaults.typeFilter))) {
			throw new Error("setVariablePanel(): The \"typeFilter\" property must be a string, array or null.");
		}
		if (typeof defaults.moduleTextField !== "string") {
			throw new Error("setVariablePanel(): The \"moduleTextField\" property must be a string.");
		}
		if (typeof defaults.moduleValueField !== "string") {
			throw new Error("setVariablePanel(): The \"moduleValueField\" property must be a string.");
		}
		if (defaults.filterMode !== 'inclusive' && defaults.filterMode !== 'exclusive') { 	 	
			throw new Error("setVariablePanel(): The \"filterMode\" property must be \"exclusive\" or \"inclusive\""); 	 	
		}
	}

	this._variableSettings = defaults;

	if (this._variablePanel) {
		this._createVariablePanel();
	}

	return this;
};

ExpressionControl.prototype.setModuleEvaluation = function (settings) {
	var defaults = {
		dataURL: null,
		dataRoot: null,
		textField: "text",
		valueField: "value",
		fieldDataURL: null,
		fieldDataRoot: null,
		fieldTextField: "text",
		fieldValueField: "value",
		fieldTypeField: "type"
	}, that = this, moduleField;

	if (settings === false) {
		defaults = false;
	} else {
		jQuery.extend(true, defaults, settings);	
	}

	if (defaults) {
		if (typeof defaults.dataURL !== "string") {
			throw new Error("setModuleEvaluation(): The \"dataURL\" property must be a string.");
		}
		if (!(typeof defaults.dataRoot === "string" || defaults.dataRoot === null)) {
			throw new Error("setModuleEvaluation(): The \"dataRoot\" property must be a string or null.");
		}
		if (typeof defaults.textField !== "string") {
			throw new Error("setModuleEvaluation(): The \"textField\" property must be a string.");
		}
		if (typeof defaults.valueField !== "string") {
			throw new Error("setModuleEvaluation(): The \"valueField\" property must be a string.");
		}
		if (typeof defaults.fieldDataURL !== "string") {
			throw new Error("setModuleEvaluation(): The \"fieldDataURL\" property must be a string.");
		}
		if (!(typeof defaults.fieldDataRoot === "string" || defaults.fieldDataRoot === null)) {
			throw new Error("setModuleEvaluation(): The \"fieldDataRoot\" property must be a string.");
		}
		if (typeof defaults.fieldTextField !== "string") {
			throw new Error("setModuleEvaluation(): The \"fieldTextField\" property must be a string.");
		}
		if (typeof defaults.fieldValueField !== "string") {
			throw new Error("setModuleEvaluation(): The \"fieldValueField\" property must be a string.");
		}
		if (typeof defaults.fieldTypeField !== "string") {
			throw new Error("setModuleEvaluation(): The \"fieldTypeField\" property must be a string.");
		}
	}

	if (!this._evaluationSettings) {
		this._evaluationSettings = {};
	}
	this._evaluationSettings.module = defaults;	
	if (this._evaluationPanel) {
		this._createModulePanel();	
	}
	return this;
};

ExpressionControl.prototype.setFormResponseEvaluation = function (settings) {
	var defaults = {
		dataURL: null,
		dataRoot: null,
		textField: "text",
		valueField: "value"
	};

	if (settings === false) {
		defaults = false;
	} else {
		jQuery.extend(true, defaults, settings);
	}

	if (defaults) {
		if (typeof defaults.dataURL !== "string") {
			throw new Error("setFormResponseEvaluation(): The \"dataURL\" parameter must be a string.");
		}
		if (!(typeof defaults.dataRoot === "string" || defaults.dataRoot === null)) {
			throw new Error("setFormResponseEvaluation(): The \"dataRoot\" parameter must be a string or null.");
		}
		if (typeof defaults.textField !== "string") {
			throw new Error("setFormResponseEvaluation(): The \"textField\" parameter must be a string.");
		}
		if (typeof defaults.valueField !== "string") {
			throw new Error("setFormResponseEvaluation(): The \"valueField\" parameter must be a string.");
		}
	}

	this._evaluationSettings.formResponse = defaults;

	if (this._evaluationPanels.formResponse) {
		this._createFormResponsePanel();
	}

	return this;
};

ExpressionControl.prototype.setBusinessRuleEvaluation = function (settings) {
	var defaults = {
		dataURL: null,
		dataRoot: null,
		textField: "text",
		valueField: "value"
	};

	if (settings === false) {
		defaults = false;
	} else {
		jQuery.extend(true, defaults, settings);

		if (typeof defaults.dataURL !== "string") {
			throw new Error("setBusinessRuleEvaluation(): The parameter must be a string.");
		}
		if (!(typeof defaults.dataRoot === "string" || defaults.dataRoot === null)) {
			throw new Error("setBusinessRuleEvaluation(): The parameter must be a string or null.");
		}
		if (typeof defaults.textField !== "string") {
			throw new Error("setBusinessRuleEvaluation(): The parameter must be a string.");
		}
		if (typeof defaults.valueField !== "string") {
			throw new Error("setBusinessRuleEvaluation(): The parameter must be a string.");
		}
	}
	this._evaluationSettings.businessRule = defaults;
	return this;
};

ExpressionControl.prototype.setUserEvaluation = function (settings) {
	var defaults = {
		defaultUsersDataURL: null,
		defaultUsersDataRoot: null,
		defaultUsersLabelField: "text",
		defaultUsersValueField: "value",
		userRolesDataURL: null,
		userRolesDataRoot: null,
		userRolesLabelField: "text",
		userRolesValueField: "value",
		usersDataURL: null,
		usersDataRoot: null,
		usersLabelField: "text",
		usersValueField: "value"
	};

	if (settings === false) {
		defaults = false;
	} else {
		jQuery.extend(true, defaults, settings);
		if (typeof defaults.defaultUsersDataURL !== "string") {
			throw new Error("setUserEvaluation(): The \"defaultUsersDataURL\" must be a string.");
		}
		if (!(typeof defaults.defaultUsersDataRoot === "string" || defaults.defaultUsersDataRoot === null)) {
			throw new Error("setUserEvaluation(): The \"defaultUsersDataRoot\" must be a string or null.");
		}
		if (typeof defaults.defaultUsersLabelField !== "string") {
			throw new Error("setUserEvaluation(): The \"defaultUsersLabelField\" must be a string.");
		}
		if (typeof defaults.defaultUsersValueField !== "string") {
			throw new Error("setUserEvaluation(): The \"defaultUsersValueField\" must be a string.");
		}
		if (typeof defaults.userRolesDataURL !== "string") {
			throw new Error("setUserEvaluation(): The \"userRolesDataURL\" must be a string.");
		}
		if (!(typeof defaults.userRolesDataRoot === "string" || defaults.userRolesDataRoot === null)) {
			throw new Error("setUserEvaluation(): The \"userRolesDataRoot\" must be a string or null.");
		}
		if (typeof defaults.userRolesLabelField !== "string") {
			throw new Error("setUserEvaluation(): The \"userRolesLabelField\" must be a string.");
		}
		if (typeof defaults.userRolesValueField !== "string") {
			throw new Error("setUserEvaluation(): The \"userRolesValueField\" must be a string.");
		}
		if (typeof defaults.usersDataURL !== "string") {
			throw new Error("setUserEvaluation(): The \"usersDataURL\" must be a string.");
		}
		if (!(typeof defaults.usersDataRoot === "string" || defaults.usersDataRoot === null)) {
			throw new Error("setUserEvaluation(): The \"usersDataRoot\" must be a string or null.");
		}
		if (typeof defaults.usersLabelField !== "string") {
			throw new Error("setUserEvaluation(): The \"usersLabelField\" must be a string.");
		}
		if (typeof defaults.usersValueField !== "string") {
			throw new Error("setUserEvaluation(): The \"usersValueField\" must be a string.");
		}
	}

	this._evaluationSettings.user = defaults;

	if (this._evaluationPanel) {
		this._createUserPanel();	
	}
	return this;
};

ExpressionControl.prototype.setEvaluations = function (evaluations) {
	var panels = ["module", "form", "business_rule", "user"], i, currentEval, _evaluationSettings = {};

	if (evaluations === false) {
		this._evaluationSettings = false;// this._evaluationSettings.form = this._evaluationSettings.business_rule = this._evaluationSettings.user = false;
	} else if (typeof evaluations === 'object') {
		for (i = 0; i < panels.length; i += 1) {
			currentEval = evaluations[panels[i]] || false;
			switch (panels[i]) {
				case "module":
					this.setModuleEvaluation(currentEval);
					break;
				case "form":
					this.setFormResponseEvaluation(currentEval);
					break;
				case "business_rule":
					this.setBusinessRuleEvaluation(currentEval);
					break;
				case "user":
					this.setUserEvaluation(currentEval);
			}
		}
	} 
	return this;
};

ExpressionControl.prototype.setOperators = function (operators) {
	var key, i, usableItems, j;
	if (this._operatorSettings !== operators) {
		this._operatorSettings = {};
		if (typeof operators === 'object') {
			for (key in this.OPERATORS) {
				if (this.OPERATORS.hasOwnProperty(key)) {
					if (typeof operators[key] === "boolean") {
						if (!operators[key]) {
							this._operatorSettings[key] = false;	
						} else {
							this._operatorSettings[key] = this.OPERATORS[key];
						}
					} else if (jQuery.isArray(operators[key])) {
						this._operatorSettings[key] = [];
						for (i = 0; i < operators[key].length; i += 1) {
							for (j = 0; j < this.OPERATORS[key].length; j += 1) {
								if (this.OPERATORS[key][j].text === operators[key][i]) {
									this._operatorSettings[key].push(this.OPERATORS[key][j]);
									break;
								}
							}
						}
					}
				}
			}
		} else if (typeof operators === 'boolean') {
			if (operators) {
				this._operatorSettings = this.OPERATORS;
			} else {
				this._operatorSettings = operators;
			}
		} else {
			throw new Error("setOperators(): The parameter must be an object literal with settings or boolean.");
		}
	}
	if (this._operatorPanel) {
		this._createOperatorPanel();
	}
	return this;
};

ExpressionControl.prototype._getStringOrNumber = function (value) {
	var aux, wildcard = "@" + (Math.random(1) * 10).toString().replace(".", "") + "@", isNum = false;
	value = jQuery.trim(value);
	if (this._decimalSeparator !== ".") {
		isNum = value.indexOf(".") < 0;
		if (isNum) {
			aux = value.replace(this._getDecimalSeparatorRegExp(), ".");	
		}
	}
	if(isNum && !isNaN(aux) && aux !== "") {
		aux = aux.split(".");
		value = parseInt(aux[0]);
		value += aux[1] ? parseInt(aux[1]) / Math.pow(10, aux[1].length) : 0;
	} else if (value.length > 1) {
		if (value[0] === "\"" && value.slice(-1) === "\"") {
			value = value.slice(1, -1);
		} else if (value[0] === "'" && value.slice(-1) === "'") {
			value = value.slice(1, -1);
		}
	}
	return value;
};

ExpressionControl.prototype._createItem = function (data, usableItem) {
	var newItem;

	if(usableItem instanceof SingleItem) {
		newItem = usableItem;
	} else {
		newItem = new SingleItem();
	}
	newItem.setFullData(data);
	newItem.setText("{{expLabel}}");
	return newItem;
};
//THIS METHOD MUST BE REPLACED FOR ANOTHER ONE WITH BETTER PERFORMANCE!!!!
ExpressionControl.prototype._getOperatorType = function(operator) {
	var type, key, i, items;
	for (key in this.OPERATORS) {
		if (this.OPERATORS.hasOwnProperty(key)) {
			items = this.OPERATORS[key];
			for (i = 0; i < items.length; i += 1) {
				if(items[i].text === operator) {
					return key.toUpperCase();
				}
			}
		}
	}

	return null;
};

ExpressionControl.prototype._onPanelValueGeneration = function () {
	var that = this;
	return function (panel, subpanel, data) {
		var itemData = {}, valueType, value, aux, parent = subpanel.getParent() || {}, label, valueField;
		if (parent.id !== 'variables-list') {
			switch (subpanel.id) {
				case "button-panel-operators":
					itemData = {
						expType: that._getOperatorType(data.value),
						expLabel: data.value,
						expValue: data.value
					};
					break;
				case "form-response-evaluation":
					itemData = {
						expType: "CONTROL",
						expLabel: subpanel.getItem("form").getSelectedText() + " "
							+ subpanel.getItem("operator").getSelectedText() + " "
							+ data.status,
						expOperator: data.operator,
						expValue: data.status,
						expField: data.form
					};
					break;
				case "form-module-field-evaluation":
					aux = data.field.split(that._auxSeparator);
					value = that._getStringOrNumber(data.value);
					valueType = typeof data.value === 'string' ? typeof value : typeof data.value;
					label = subpanel.getItem("field").getSelectedText() + " " 
							+ subpanel.getItem("operator").getSelectedText() + " " ;
					valueField = subpanel.getItem("value");
					if (aux[1] === "Date") {
						label += valueField.getFormattedDate();
					} else if (aux[1] === 'Datetime') {
						label += valueField.getFormattedDate() + " " + valueField._htmlControl[1].value;
					} else {
						label += (valueType === "string" ? "\"" + value + "\"" : data.value);
					}
					itemData = {
						expType: "MODULE",
						expSubtype: aux[1],
						expLabel: label,
						expValue: value,
						expOperator: data.operator,
						expModule: data.module,
						expField: aux[0]
					};
					break;
				case 'form-business-rule-evaluation':
					value = that._getStringOrNumber(data.response);
					valueType = typeof value;
					itemData = {
						expType: "BUSINESS_RULES",
						expLabel: subpanel.getItem("rule").getSelectedText() + " "
							+ subpanel.getItem("operator").getSelectedText() + " "
							+ (valueType === "string" ? "\"" + value + "\"" : value),
						expValue: value,
						expOperator: data.operator,
						expField: data.rule
					};
					break;
				case 'form-user-evaluation':
					aux = data.operator.split("|");
					value = data.value || null;
					label = subpanel.getItem("value").getSelectedText();
					switch (aux[0]) {
						case 'USER_ADMIN':
							valueType = aux[1] === 'equals' ? "is admin" : "is not admin";
							break;
						case 'USER_ROLE': 
							valueType = (aux[1] === 'equals' ? "has role" : "has not role") + " " + label;
							break;
						case 'USER_IDENTITY':
							valueType = (aux[1] === 'equals' ? "==" : "!=") + " " + label;
							break;
					}
					label = subpanel.getItem("user").getSelectedText() + " " + valueType;
					itemData = {
						expType: aux[0],
						expLabel: label,
						expValue: value,
						expOperator: aux[1],
						expField: data.user 
					};
					break;
				case 'form-constant-basic':
					if (data.type === 'number') {
						aux = data.value.split(that._getDecimalSeparatorRegExp());
						value = parseInt(aux[0], 10);
						if (aux[1]) {
							aux = parseInt(aux[1], 10) / Math.pow(10, aux[1].length);
						} else {
							aux = 0;
						}
						value += aux * (value >= 0 ? 1 : -1);
						valueType = data.value;
					} else if (data.type === 'boolean') {
						value = data.value.toLowerCase() === "false" || data.value === "0" ? false : !!data.value;
						valueType = value ? "TRUE" : "FALSE";
					} else {
						value = data.value;
						valueType = "\"" +  data.value + "\"";
					}
					itemData = {
						expType: 'CONSTANT',
						expSubtype: data.type,
						expLabel: valueType,
						expValue: value
					};
					break;
				case 'form-constant-date':
					itemData = {
						expType: 'CONSTANT',
						expSubtype: "date",
						expLabel: subpanel.getItem("date").getFormattedDate(),
						expValue: data.date
					};
					break;
				case 'form-constant-timespan':
					itemData = {
						expType: "CONSTANT",
						expSubtype: "timespan",
						expLabel: data.ammount + data.unittime,
						expValue: data.ammount + data.unittime
					};
					break;
				default:
					throw new Error("_onPanelValueGeneration(): Invalid source data.")
			}
		} else {
			itemData = {
				expType: "VARIABLE",
				expSubtype: data.type,
				expLabel: data.text,
				expValue: data.value,
				expModule: data.module
			};
		}

		if (subpanel instanceof FormPanel) {
			subpanel.reset();
		}
		that._itemContainer.addItem(that._createItem(itemData));
	};
};

ExpressionControl.prototype._createOperatorPanel = function () {
	var key;
	if (!this._operatorPanel) {
		this._operatorPanel = new FieldPanelButtonGroup({
			id: "button-panel-operators"
		});
	};
	if (this._operatorSettings) {
		this._operatorPanel.clearItems();
		for (key in this._operatorSettings) {
			if (this._operatorSettings.hasOwnProperty(key)) {
				if (typeof this._operatorSettings[key] === "object") {
					usableItems = this._operatorSettings[key];
					for (i = 0; i < usableItems.length; i += 1) {
						this._operatorPanel.addItem({
							value: usableItems[i].text
						});
					}
				}
			}
		}
		this._operatorPanel.setVisible(!!this._operatorPanel.getItems().length);
	} else {
		this._operatorPanel.setVisible(false);
	}
	return this._operatorPanel;
};

ExpressionControl.prototype.addVariablesList = function (data, cfg) {
	var i, conf, itemsContentHook = function (item, data) {
		var mainLabel = "[item]", span1, span2, wrapperDiv;

		mainLabel = data.text;
		wrapperDiv = this.createHTMLElement('div');
		span1 = this.createHTMLElement("span");
		span1.className = "adam expressionbuilder-variableitem-text";
		span1.textContent = mainLabel;
		span2 = this.createHTMLElement("span");
		span2.className = "adam expressionbuilder-variableitem-datatype";
		span2.textContent = data.type;
		wrapperDiv.appendChild(span1);
		wrapperDiv.appendChild(span2);
		mainLabel = wrapperDiv;
		
		return mainLabel;
	};
	conf = {
		fieldToFilter: "type",
		filter: cfg.typeFilter,
		filterMode: cfg.filterMode,
		title: cfg.moduleText,
		data: [],
		itemsContent: itemsContentHook
	};
	for (i = 0; i < data.length; i += 1) {
		conf.data.push({
			value: data[i][cfg.valueField],
			text: data[i][cfg.textField],
			type: data[i][cfg.typeField],
			module: cfg.moduleValue
		});
	}
	newList = new ListPanel(conf);
	if (newList.getItems().length) {
		this._variablePanel.addItem(newList);
	}
	return this;
};

ExpressionControl.prototype._onLoadVariableDataSuccess = function () {
	var that = this;
	return function (data) {
		var settings = that._variableSettings, cfg, i, j, fields, newList, aux = {}, filterFunction;
		if (settings.dataRoot) {
			data = data[settings.dataRoot];
		}
		if (settings.dataFormat === "hierarchical") {
			for (i = 0; i < data.length; i += 1) {
				that.addVariablesList(
					data[i][settings.dataChildRoot],
					{
						textField: settings.textField,
						valueField: settings.valueField,
						typeField: settings.typeField,
						typeFilter: settings.typeFilter,
						filterMode: settings.filterMode,
						moduleText: data[i][settings.moduleTextField],
						moduleValue: data[i][settings.moduleValueField]
					}
				);
				/*cfg = {
					fieldToFilter: that._variableSettings.typeField,
					filter: that._variableSettings.typeFilter,
					title: data[i][settings.moduleTextField],
					data: [],
					itemsContent: itemsContentHook
				};
				fields = data[i][settings.dataChildRoot];
				for (j = 0; j < fields.length; j += 1) {
					cfg.data.push({
						value: fields[j][settings.valueField],
						text: fields[j][settings.textField],
						type: fields[j][settings.typeField],
						module: data[i][settings.moduleValueField]
					});
				}
				newList = new ListPanel(cfg);
				if (newList.getItems().length) {
					that._variablePanel.addItem(newList);
				}*/
			}
		} else {
			if (typeof settings.typeFilter === 'string') {
				filterFunction = function (value) {
					return settings.typeFilter === value;
				};
			} else if (jQuery.isArray(settings.typeFilter)) {
				filterFunction = function (value) {
					return settings.typeFilter.indexOf(value) >= 0;
				};
			} else {
				filterFunction = function () {
					return true;
				};
			}
			for (i = 0; i < data.length; i += 1) {
				if (filterFunction(data[i][settings.typeField])) {
					if (!aux[data[i][settings.moduleValueField]]) {
						aux[data[i][settings.moduleValueField]] = {
							fields: []
						};	
					}
					aux[data[i][settings.moduleValueField]].fields.push(data[i]);
				}
			}
			j = 0;
			for (i in aux) {
				if (aux.hasOwnProperty(i)) {
					that.addVariablesList(aux[i].fields, {
						textField: settings.textField,
						valueField: settings.valueField,
						typeField: settings.typeField,
						typeFilter: settings.typeFilter,
						filterMode: settings.filterMode,
						moduleText: aux[i].fields[0][settings.moduleTextField],
						moduleValue: aux[i].fields[0][settings.moduleValueField]
					});
				}
			}
		}
	};
};

ExpressionControl.prototype._onLoadVariableDataError = function () {};

ExpressionControl.prototype._createVariablePanel = function () {
	var settings = this._variableSettings, i;
	if (!this._variablePanel) {
		this._variablePanel = new MultipleCollapsiblePanel({
			id: "variables-list",
			title: translate("LBL_PMSE_EXPCONTROL_VARIABLES_PANEL_TITLE"),
			onExpand: this._onExpandPanel()
		});
		this._panel.addItem(this._variablePanel);
	}
	if (settings) {
		this._variablePanel.clearItems();
		if (settings.dataURL) {
			this._proxy.url = settings.dataURL;
			this._proxy.getData(null, {
				success: this._onLoadVariableDataSuccess(),
				error : this._onLoadVariableDataError()
			});	
		} else {
			(this._onLoadVariableDataSuccess())(settings.data);
		}
	}
	this._variablePanel.setVisible(!!settings);
	return this._variablePanel;
};

ExpressionControl.prototype._createModulePanel = function () {
	var moduleField, that = this, settings = this._evaluationSettings.module, currentType;
	if (!this._evaluationPanels.module) {
		this._evaluationPanels.module = new FormPanel({
			id: "form-module-field-evaluation",
			title: translate("LBL_PMSE_EXPCONTROL_MODULE_FIELD_EVALUATION_TITLE"), 
			items: [
				{
					type: "dropdown",
					name: "module",
					label: translate("LBL_PMSE_EXPCONTROL_MODULE_FIELD_EVALUATION_MODULE"),
					width: "100%",
					required: true,
					dependantFields: ['field'],
					required: true
				},
				{
					type: "dropdown",
					name: "field",
					label: translate("LBL_PMSE_EXPCONTROL_MODULE_FIELD_EVALUATION_VARIABLE"),
					width: "40%",
					required: true,
					dependantFields: ['value'],
					dependencyHandler: function (dependantField, field, value) {
						var settings = that._evaluationSettings.module,
							url = settings.fieldDataURL.replace("{{MODULE}}", value);
						if (value) {
							dependantField.setDataURL(url)
								.setDataRoot(settings.fieldDataRoot)
								.setLabelField(settings.fieldTextField)
								.setValueField(function (field, data) {
									return data[settings.fieldValueField] + that._auxSeparator + data[settings.fieldTypeField];
								})
								.load();
						} else {
							dependantField.clearOptions();
						}
						/*field.setDataURL('_xdatax/fields.json')
							.setDataRoot('result')
							.setLabelField('text')
							.load();*/
					}
				},
				{
					type: "dropdown",
					name: "operator",
					label: "",
					width: "20%", 
					labelField: "text",
					valueField: "value",
					required: true,
					options: this.OPERATORS.comparison
				},
				{
					type: "text",
					name: "value",
					label: translate("LBL_PMSE_EXPCONTROL_MODULE_FIELD_EVALUATION_VALUE"),
					width: "40%",
					required: true,
					dependencyHandler: function (dependantField, parentField, value) {
						var type = value.split(that._auxSeparator)[1], 
							form, newField, items = [], itemsObj, keys, operators, newFieldSettings;
						type = type && that._typeToControl[type.toLowerCase()];
						if ((type && type !== currentType) || type === 'dropdown') {
							currentType = type;
							form = dependantField.getForm();

							newFieldSettings = {
								type: type,
								width: dependantField.width,
								label: dependantField.getLabel(),
								name: dependantField.getName()
							};

							if (type === 'dropdown') {
								itemsObj = parentField.getSelectedData()["optionItem"];
								keys = Object.keys(itemsObj);
								keys.forEach(function (item, index, arr) {
									items.push({
										value: item,
										label: itemsObj[item]
									});
								});
								newFieldSettings.options = items;
							}
							
							switch (type) {
								case 'date':
								case 'datetime':
								case 'decimal':
								case 'currency':
								case 'float':
								case 'integer':
									operators = that.OPERATORS.comparison;
									if (type !== 'date' && type !== 'datetime') {
										newFieldSettings.precision = 
											(type === 'integer' ? 0 : (type === 'currency' ? 2 : -1));
										newFieldSettings.groupingSeparator = 
											(type === 'currency' ? that._numberGroupingSeparator : "");
										newFieldSettings.decimalSeparator = that._decimalSeparator;
									}
									break;
								default:
									operators = [that.OPERATORS.comparison[2], that.OPERATORS.comparison[5]];
							}
							form.getItem("operator").setOptions(operators);

							newField = form._createField(newFieldSettings);

							form.replaceItem(newField, dependantField);
							newField.setDependencyHandler(dependantField._dependencyHandler);
						}
						
						/*if (type && constructor = that._typeToControl[type.toLowerCase()]) {
							
							form.replaceItem(form._createField());
						}*/
					}
				}
			],
			onCollapse: function (formPanel) {
				var valueField = formPanel.getItem("value");

				if (valueField instanceof FormPanelDate) {
					valueField.closeAll();
				}
			}
		});
		this._evaluationPanel.addItem(this._evaluationPanels.module);
	}
	if (settings) {
		moduleField = this._evaluationPanels.module.getItem("module");
		moduleField.setDataURL(settings.dataURL)
			.setDataRoot(settings.dataRoot)
			.setLabelField(settings.textField)
			.setValueField(settings.valueField)
			.load();
		this._evaluationPanel.enable();
		this._evaluationPanel.setVisible(true);
	} else {
		this._evaluationPanel.disable();
	}
	return this._evaluationPanels.module;
};

ExpressionControl.prototype._createFormResponsePanel = function () {
	var formField, settings;
	if (!this._evaluationPanels.formResponse) {
		this._evaluationPanels.formResponse = new FormPanel({
			id: "form-response-evaluation",
			title: translate("LBL_PMSE_EXPCONTROL_FORM_RESPONSE_EVALUATION_TITLE"),
			items: [
				{
					type: "dropdown",
					name: "form",
					label: translate("LBL_PMSE_EXPCONTROL_FORM_RESPONSE_EVALUATION_FORM"),
					width: "40%"
				}, {
					type: "dropdown",
					name: "operator",
					label: "",
					width: "20%",
					options: [
						this.OPERATORS.comparison[2],
						this.OPERATORS.comparison[5],
					],
					valueField: "value",
					labelField: "text"
				}, {
					type: "dropdown",
					name: "status",
					label: translate("LBL_PMSE_EXPCONTROL_FORM_RESPONSE_EVALUATION_STATUS"),
					width: "40%",
					options: [
						{
							label: "Approved",
							value: "Approved"
						}, {
							label: "Rejected",
							value: "Rejected"
						}
					]
				}
			]
		});
	}
	settings = this._evaluationSettings.formResponse;
	if (settings) {
		formField = this._evaluationPanels.formResponse.getItem("form");
		this._evaluationPanel.addItem(this._evaluationPanels.formResponse);
		formField.setDataURL(settings.dataURL)
			.setDataRoot(settings.dataRoot)
			.setLabelField(settings.textField)
			.setValueField(settings.valueField)
			.load();	
	}
	
	return this._evaluationPanels.formResponse;
};

ExpressionControl.prototype._createBusinessRulePanel = function () {
	var rulesField, settings = this._evaluationSettings.businessRule;
	if (!this._evaluationPanels.businessRule) {
		this._evaluationPanels.businessRule = new FormPanel({
			id: "form-business-rule-evaluation",
			type: "form",
			title: translate("LBL_PMSE_EXPCONTROL_BUSINESS_RULES_EVALUATION_TITLE"),
			items: [
				{
					type: "dropdown",
					name: "rule",
					label: translate("LBL_PMSE_EXPCONTROL_BUSINESS_RULES_EVALUATION_BR"),
					width: "40%",
					required: true
				}, {
					type: "dropdown",
					label: "",
					name: "operator",
					width: "20%",
					labelField: "text",
					options: [
						this.OPERATORS.comparison[2],
						this.OPERATORS.comparison[5]
					]
				}, {
					type: "text",
					label: translate("LBL_PMSE_EXPCONTROL_BUSINESS_RULES_EVALUATION_RESPONSE"),
					name: "response",
					width: "40%"
				}
			]
		});
	}
	if (settings) {
		rulesField = this._evaluationPanels.businessRule.getItem("rule");
		this._evaluationPanel.addItem(this._evaluationPanels.businessRule);
		rulesField.setDataURL(settings.dataURL)
			.setDataRoot(settings.dataRoot)
			.setLabelField(settings.textField)
			.setValueField(settings.valueField)
			.load();	
	}
	
	return this;
};

ExpressionControl.prototype._createUserPanel = function () {
	var userField, settings = this._evaluationSettings.user;
	if (!this._evaluationPanels.user) {
		this._evaluationPanels.user = new FormPanel({
			id: "form-user-evaluation",
			type: "form",
			title: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TITLE"),
			items: [
				{
					type: "dropdown",
					name: "user",
					label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_USER"),
					width: "35%",
					options: [
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_CURRENT"),
							value: "current_user"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_SUPERVISOR"),
							value: "supervisor"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_OWNER"),
							value: "owner"
						}
					]
				}, {
					type: "dropdown",
					name: "operator",
					label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_OPERATOR"),
					width: "30%",
					dependantFields: ['value'],
					options: [
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_IS_ADMIN"),
							value: "USER_ADMIN|equals"
						},
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_IS_ROLE"),
							value: "USER_ROLE|equals"
						},
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_IS_USER"),
							value: "USER_IDENTITY|equals"
						},
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_IS_NOT_ADMIN"),
							value: "USER_ADMIN|not_equals"
						},
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_IS_NOT_ROLE"),
							value: "USER_ROLE|not_equals"
						},
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_IS_NOT_USER"),
							value: "USER_IDENTITY|not_equals"
						}
					]
				}, {
					type: "dropdown",
					name: "value",
					label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_VALUE"),
					width: "35%",
					required: true,
					dependencyHandler: function(dependantField, field, value) {
						var condition = value.split("|")[0];
						switch (condition) {
							case 'USER_ADMIN':
								dependantField.clearOptions().disable();
								break;
							case 'USER_ROLE':
								dependantField.setDataURL(settings.userRolesDataURL)
									.setDataRoot(settings.userRolesDataRoot)
									.setLabelField(settings.userRolesLabelField)
									.setValueField(settings.userRolesValueField)
									.load();
								break;
							case 'USER_IDENTITY': 
								dependantField.setDataURL(settings.usersDataURL)
									.setDataRoot(settings.usersDataRoot)
									.setLabelField(settings.usersLabelField)
									.setValueField(settings.usersValueField)
									.load();
						}
					}
				}
			]
		});
		this._evaluationPanel.addItem(this._evaluationPanels.user);
	}
	if (settings) {
		userField = this._evaluationPanels.user.getItem("user");
		userField.setDataURL(settings.defaultUsersDataURL)
			.setDataRoot(settings.defaultUsersDataRoot)
			.setLabelField(settings.defaultUsersLabelField)
			.setValueField(settings.defaultUsersValueField)
			.load();
		this._evaluationPanels.user.enable();
	} else {
		this._evaluationPanels.user.disable();
	}
	return this;
};

ExpressionControl.prototype._isRegExpSpecialChar = function (c) {
	switch (c) {
	    case "\\":
	    case "^":
	    case "$":
	    case "*":
	    case "+":
	    case "?":
	    case ".":
	    case "(":
	    case ")":
	    case "|":
	    case "{":
	    case "}":
	        return true;
	        break;
    }
    return false;
};

ExpressionControl.prototype._getDecimalSeparatorRegExp = function () {
	var prefix = "";
	if (this._isRegExpSpecialChar(this._decimalSeparator)) {
	    prefix = "\\";
    }
    return new RegExp(prefix + this._decimalSeparator, "g");
};

ExpressionControl.prototype._getNumberRegExp = function () {
	var prefix = "";
	if (this._isRegExpSpecialChar(this._decimalSeparator)) {
	    prefix = "\\";
    }
    return new RegExp("^-?\\d+(" + (prefix + this._decimalSeparator) + "\\d+)?$");
};

ExpressionControl.prototype._onBasicConstantKeyUp = function () {
	var that = this;
	return function (field, nextValue, keyCode) {
		var form = field.getForm(), 
			numberButton = form.getItem("btn_number"), 
			booleanButton = form.getItem("btn_boolean"),
			nextValue = nextValue.toLowerCase();

		if (that._getNumberRegExp().test(nextValue)) {
			numberButton.enable();
			booleanButton.enable();
		} else {
			numberButton.disable();
			if (nextValue === "true" || nextValue === "false") {
				booleanButton.enable();
			} else {
				booleanButton.disable();
			}
		}
	};
};

ExpressionControl.prototype._createDateConstantPanel = function() {
	var settings = this._constantSettings.date;
	if (!this._constantPanels.date) {
		this._constantPanels.date = new FormPanel({
			id: "form-constant-date",
			title: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_FIXED_DATE"),
			items: [
				{
					type: "date",
					name: "date",
					label: "Date",
					width: "100%",
					format: this._dateFormat,
					required: true
				}
			],
			onCollapse: function (formPanel) {
				formPanel.getItem("date").close();
			}
		});
		this._constantPanel.addItem(this._constantPanels.date);
	}
	if (settings) {
		this._constantPanels.date.enable();
	} else {
		this._constantPanels.date.disable();
	}
	
	return this;
};

ExpressionControl.prototype._createTimespanPanel = function() {
	var settings = this._constantSettings.timespan;
	if (!this._constantPanels.timespan) {
		this._constantPanels.timespan = new FormPanel({
			id: "form-constant-timespan",
			title: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_TITLE"),
			items: [
				{
					type: "text",
					name: "ammount",
					label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_AMMOUNT"),
					filter: "integer",
					width: "40%",
					required: true,
					disabled: true
				}, {
					type: "dropdown",
					label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_UNIT"),
					name: "unittime",
					width: "60%",
					disabled: true,
					options: [
						{
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_YEARS"),
							value: "y"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_MONTHS"),
							value: "m"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_WEEKS"),
							value: "w"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_DAYS"),
							value: "d"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_HOURS"),
							value: "h"
						}, {
							label: translate("LBL_PMSE_EXPCONTROL_USER_EVALUATION_TIMESPAN_MINUTES"),
							value: "min"
						}
					]
				}
			]
		});
		this._constantPanel.addItem(this._constantPanels.timespan);
	}
	if (settings) {
		this._constantPanels.timespan.enable();
	} else {
		this._constantPanels.timespan.disable();
	}
	
	return this;
};

ExpressionControl.prototype._createBasicConstantPanel = function () {
	var settings = this._constantSettings.basic, onClickHandler, basicForm, aux;
	if (!this._constantPanels.basic) {
		onClickHandler = function (clickedButton) {
			var form = clickedButton.getForm(),
				typeField = form.getItem("type");

			typeField.setValue(clickedButton.getLabel().substr(4));
			form.submit();
		};
		this._constantPanels.basic = new FormPanel({
			id: "form-constant-basic",
			title: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_BASIC"),
			submitVisible: false,
			items: [
				{
					name: "value",
					type: "text",
					label: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_BASIC_VALUE"),
					width: "100%",
					onKeyUp: this._onBasicConstantKeyUp()
				}, {
					name: 'type',
					type: 'hidden',
					label: ""
				}, {
					type: "button",
					label: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_BASIC_ADD_STRING"),
					name: "btn_string",
					width: "33%",
					onClick: onClickHandler
				}, {
					type: "button",
					label: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_BASIC_ADD_NUMBER"),
					name: "btn_number",
					width: "33%",
					disabled: true,
					onClick: onClickHandler
				}, {
					type: "button",
					label: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_BASIC_ADD_BOOLEAN"),
					name: "btn_boolean",
					width: "33%",
					onClick: onClickHandler
				}
			],
			onSubmit: function (form) {
				var typeField = form.getItem("type"), enabledButtons = 0, btn, btns, i, aux;

				if(!typeField.getValue()) {
					btns = ["btn_string", "btn_number", "btn_boolean"];
					for (i = 0; i < btns.length; i += 1) {
						aux = form.getItem(btns[i]);
						if (aux.visible && !aux.isDisabled()) {
							btn = aux;
							enabledButtons += 1;
						}
					}
					if (enabledButtons === 1) {
						form.getItem("type").setValue(btn.getvdfvddfv().substr(4));
					} else {
						return false;
					}
				}
			}
		});
		this._constantPanel.addItem(this._constantPanels.basic);
	}
	basicForm = this._constantPanels.basic;

	if (settings) {
		basicForm.getItem("btn_string").setVisible(settings === true || !!settings.string);
		basicForm.getItem("btn_number").setVisible(settings === true || !!settings.number);
		basicForm.getItem("btn_boolean").setVisible(settings === true || !!settings.boolean);
		settings = settings === true || (settings.string || settings.number || settings.boolean);
		if (settings) {
			this._constantPanel.setVisible(true);
			basicForm.enable();	
		} else {
			basicForm.disable();
		}
		
	} else {
		basicForm.disable();
	}

	return this;
};

ExpressionControl.prototype._onExpandPanel = function() {
	var that = this;
	return function(panel) {
		var items = that._panel.getItems(), i;
		for (i = 0; i < items.length; i += 1) {
			if (items[i] instanceof CollapsiblePanel && items[i] !== panel) {
				items[i].collapse();
			}
		}
	};
};

ExpressionControl.prototype._createMainPanel = function () {
	var items = [];
	if (!this._externalItemContainer) {
		items.push(this._itemContainer);
	}
	if (!this._panel.getItems().length) {
		this._createOperatorPanel();
		items.push(this._operatorPanel);

		this._evaluationPanel = new MultipleCollapsiblePanel({
			title: translate("LBL_PMSE_EXPCONTROL_EVALUATIONS_TITLE"),
			onExpand: this._onExpandPanel()
		});
		if (this._evaluationSettings) {
			this._createModulePanel();
			this._createFormResponsePanel();
			this._createBusinessRulePanel();
			this._createUserPanel();
		}
		items.push(this._evaluationPanel);
		this._evaluationPanel.setVisible(!!this._evaluationPanel.getItems().length);

		this._constantPanel = new MultipleCollapsiblePanel({
			title: translate("LBL_PMSE_EXPCONTROL_CONSTANTS_TITLE"),
			onExpand: this._onExpandPanel()
		});
		if (this._constantSettings) {
			this._createBasicConstantPanel();
			this._createDateConstantPanel();
			this._createTimespanPanel();
		}
		items.push(this._constantPanel);
		this._constantPanel.setVisible(!!this._constantPanel.getItems().length);

		this._panel.setItems(items);
		this._createVariablePanel();
	}
	return this._panel;
};

/*ExpressionControl.prototype._appendPanel = function () {
	var position, appendPanelTo = this._appendTo, owner = this._owner, offsetHeight = 1, zIndex = 0, siblings, aux;
	if (owner) {
		if (!isHTMLElement(owner)) {
			owner = owner.html;
		}
		offsetHeight = owner.offsetHeight;
	}
	if (typeof appendPanelTo === 'function') {
		appendPanelTo = appendPanelTo.call(this);
	}
	if (!isHTMLElement(appendPanelTo)) {
		appendPanelTo = appendPanelTo.html;
	}
	siblings = appendPanelTo.children;
	for (i = 0; i < siblings.length; i += 1) {
		aux = jQuery(siblings[i]).zIndex();
		if (aux > zIndex) {
			zIndex = aux;
		} 
	}

	this.setZOrder(zIndex + 1);

	if (!owner || isInDOM(owner)) {
		appendPanelTo.appendChild(this.html);
	}
	if (owner) {
		this._panel.setWidth(this._matchOwnerWidth ? owner.offsetWidth : this.width);
		position = getRelativePosition(owner, appendPanelTo);
	} else {
		this._panel.setWidth(this.width);
		position = {left: 0, top: 0};
	}
	this._panel.setPosition(position.left, position.top + offsetHeight - 1);
	return this;
};*/

ExpressionControl.prototype.isPanelOpen = function () {
	return this._panel && this._panel.isOpen();
};

ExpressionControl.prototype.open = function () {
	this.getHTML();
	if (!this.isPanelOpen()) {
		this._constantPanel.collapse(true);
		this._variablePanel.collapse(true);
		this._evaluationPanel.collapse(true);
	}
	this._panel.open();
	return this;
};

ExpressionControl.prototype.close = function () {
	this._panel.close();
	return this;
};

ExpressionControl.prototype.isValid = function() {
    var i, cIsEval, pIsEval, valid = true, prev = null, current, pendingToClose = 0, dataNum = 0, msg = "invalid criteria syntax", items = this._itemContainer.getItems();

    for (i = 0; i < items.length; i += 1) {
        current = items[i].getData();
        cIsEval = current.expType === "MODULE" || current.expType === "BUSINESS_RULES" || current.expType === "CONTROL"
        || current.expType === "USER_ADMIN" || current.expType === "USER_ROLE"
        || current.expType === "USER_IDENTITY" || current.expType === "CONSTANT" || current.expType === "VARIABLE";

        if (cIsEval || (current.expType === "GROUP" && current.expValue === "(") || (current.expType === "LOGIC" && current.expValue === "NOT")) {
            valid = !(prev && (pIsEval || (prev.expType === "GROUP" && prev.expValue === ")")));
        } else {
            valid = prev && ((prev.expType === "GROUP" && prev.expValue === ")") || (pIsEval || cIsEval));
            valid = valid === null ? true : valid;
        }

        if (current.expType === 'GROUP') {
            if (current.expValue === ')') {
                valid = valid && pendingToClose > 0;
                pendingToClose -= 1;
            } else if (current.expValue === '(') {
                pendingToClose += 1;
            }
        }

        if (!valid) {
            break;
        }
        prev = current;
        pIsEval = cIsEval;
    }

    if (valid) {
        if (prev) {
            valid = valid && prev.expType !== 'LOGIC' && prev.expType !== 'ARITHMETIC' && !(prev.expType === 'GROUP' && prev.expValue === "(");
        }
        valid = valid && pendingToClose === 0;
    }

    return valid;
};

ExpressionControl.prototype.createHTML = function () {
	var control;
	if (!this.html) {
		this._createMainPanel();
		this.html = this._panel.getHTML();

		this.style.applyStyle();

        this.style.addProperties({
            width: this.width,
            height: this.height,
            zIndex: this.zOrder
        });
	}

	return this.html;
};
//@ sourceURL=pmse.br.js
