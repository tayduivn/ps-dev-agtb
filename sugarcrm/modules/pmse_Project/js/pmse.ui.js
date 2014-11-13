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
 * @class Modal
 * Handle modal divs
 * @extend Base
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var Modal = function (options) {
    Base.call(this, options);
    /**
     * Defines the state of the modal object
     * @type {Boolean}
     */
    this.visible = null;
    /**
     * Defines the property of loading
     * @type {Boolean}
     */
    this.loaded = false;
    /**
     * Defines the HTML Element Pointer
     * @type {HTMLElement}
     */
    this.html = null;
    /**
     * Defines the click handler
     * @type {Function}
     */
    this.clickHander = null;
    Modal.prototype.initObject.call(this, options);
};

Modal.prototype = new Base();

/**
 * Defines the object's type
 * @type {String}
 */
Modal.prototype.type = "Modal";

/**
 * Initializes the object with default values
 * @param {Object} options
 */
Modal.prototype.initObject = function (options) {
    var defaults = {
        visible: false,
        clickHander: function () {}
    };
    $.extend(true, defaults, options);
    this.setVisible(defaults.visible)
        .setClickHandler(defaults.clickHander);
};

/**
 * Sets the visible property
 * @param {Boolean} value
 * @return {*}
 */
Modal.prototype.setVisible = function (value) {
    this.visible = value;
    return this;
};

/**
 * Sets the click handler
 * @param {Function} fn
 * @return {*}
 */
Modal.prototype.setClickHandler = function (fn) {
    this.clickHander = fn;
    return this;
};

/**
 * Shows the modal object
 */
Modal.prototype.show = function () {
    var modalDiv;
    if (!this.html) {
        modalDiv = document.createElement('div');
        modalDiv.className = 'adam-modal';
        modalDiv.id = this.id;
        this.html = modalDiv;
    }
    document.body.appendChild(this.html);
    this.setVisible(true);
    if (!this.loaded) {
        this.attachListeners();
        this.loaded = true;
    }
};

/**
 * Hide the modal object
 */
Modal.prototype.hide = function () {
    if (this.visible) {
        document.body.removeChild(this.html);
        this.setVisible(false);
    }
};

/**
 * Initializes the modal listeners
 */
Modal.prototype.attachListeners = function () {
    var self = this;
    if (this.html) {
        $(this.html)
            .click(function (e) {
                e.stopPropagation();
                if (self.clickHander) {
                    self.clickHander();
                }
            })
            .mouseover(function (e) {
                e.stopPropagation();
            })
            .mouseout(function (e) {
                e.stopPropagation();
            })
            .mouseup(function (e) {
                e.stopPropagation();
            })
            .mousedown(function (e) {
                e.stopPropagation();
            });
    }
};

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
/**
 * @class Container
 * Handle Containers
 * @extend Element
 *
 * @constructor
 * Create a new instance of the container class
 * @param {Object} options
 */
var Container = function (options) {
    Element.call(this, options);
    /**
     * Defines the items part of the container
     * @type {Array}
     */
    this.items = [];
    /**
     * Defines the pointer to the body HTML Element
     * @type {HTMLElement}
     */
    this.body = null;
    /**
     * Defines the height for the body HTML Element, if is not specified then the body height is auto
     * @type {Number}
     */
    this.bodyHeight = null;
    Container.prototype.initObject.call(this, options);
};

Container.prototype = new Element();
/**
 * Defines the object's type
 * @type {String}
 */
Container.prototype.type = "Container";
/**
 * Defines the object's family
 * @type {String}
 */
Container.prototype.family = "Container";

/**
 * Initialize the object with the default values
 */
Container.prototype.initObject = function (options) {
    var defaults = {
        items: [],
        body: null
    };
    $.extend(true, defaults, options);
    this.setItems(defaults.items)
        .setBody(defaults.body)
        .setBodyHeight(defaults.bodyHeight);
};

/**
 * Sets the items property
 * @param {Array}
 */
Container.prototype.setItems = function (items) {
    this.items = items;
    return this;
};

Container.prototype.setBodyHeight = function(height) {
    this.bodyHeight = height;
    if(this.body) {
        if(typeof height === 'number') {
            this.body.style.height = height + 'px';    
        } else {
            this.bodyHeight = null;
            this.body.style.height = '';
        }
        this.height = $(this.html).height();
    }
    return this;
};

/**
 * Sets the body HTML Element
 * @param {HTMLElement} html
 */
Container.prototype.setBody = function (html) {
    this.body = html;
    return this;
};

/**
 * Returns the body HTML Element
 */
Container.prototype.getBody = function () {
    return this.body;
};

/**
 * Creates the HTML Element
 */
Container.prototype.createHTML = function () {
    var body;
    Element.prototype.createHTML.call(this);
    body = this.createHTMLElement('div');
    body.className = 'j-container';
    this.html.appendChild(body);
    this.body = body;
    this.setBodyHeight(this.bodyHeight);
    return this.html;
};

/*globals Container, $, Modal, TabPanelElement, Panel, Base*/
/**
 * @class Window
 * Handle window objects
 * @extend Container
 *
 * @constructor
 * Creates a new instance of the window's class
 * @param {Object} options
 */
var Window = function (options) {
    Container.call(this, options);
    /**
     * Defines the window's title
     * @type {String}
     */
    this.title = null;
    /**
     * Defines the window's modal property
     * @type {Boolean}
     */
    this.modal = null;
    /**
     * Defines the Modal Object to handle modal windows
     * @type {Modal}
     */
    this.modalObject = null;
    /**
     * Defines the window header HTML Element where are placed the title label HTML Element and the Close Button HTML Element
     * @type {HTMLElement}
     */
    this.windowHeader = null;
    /**
     * Defines the Close Button HTML Element
     * @type {HTMLElement}
     */
    this.closeButtonObject = null;
    /**
     * Defines the title label HTML Element
     * @type {HTMLElement}
     */
    this.titleLabelObject = null;
    /**
     * Records the loading state of the window
     * @type {Boolean}
     * @private
     */
    this.loaded = false;

    /**
     * Defines the DestroyOnHide property
     * @type {Boolean}
     */
    this.destroyOnHide = null;

    /**
     * Defines the modal handler HTML Element pointer
     * @type {HTMLElement}
     */
    this.modalHandler = null;

    /**
     * Defines the close button property
     * @type {Boolean}
     */
    this.closeButton = null;
    /**
     * Defines the window's panel objects
     * @type {Array<Panel>}
     */
    this.panels = [];
    /**
     * Defines the HTML Element to apply the modal mask
     * @type {HTMLElement}
     * @private
     */
    this.modalContainer = null;
    /**
     * Defines the HTML Element which contains the tabs
     * @type {HTMLElement}
     */
    this.tabsContainer = null;
    /**
     * Defines the current selected tab/panel
     * @type {[TabPanelElement]}
     */
    this.selectedTab = null;
    Window.prototype.initObject.call(this, options);
};

Window.prototype = new Container();

/**
 * Defines the object's type
 * @type {String}
 */
Window.prototype.type = "Window";

/**
 * Initialize the object with the default values
 */
Window.prototype.initObject = function (options) {
    var defaults = {
        title: 'No Title',
        modal: true,
        modalHandler: null,
        destroyOnHide: false,
        closeButton: true,
        panels: []
    };
    $.extend(true, defaults, options);
    this.setTitle(defaults.title)
        .setModalHandler(defaults.modalHandler)
        .setModal(defaults.modal)
        .setVisible(false)
        .setCloseButton(defaults.closeButton)
        .setDestroyOnHide(defaults.destroyOnHide)
        .setPanels(defaults.panels);

    this.modalContainer = $('body');
};

/**
 * Sets the window's title
 * @param {String} text
 */
Window.prototype.setTitle = function (text) {
    this.title = text;
    if (this.titleLabelObject) {
        this.titleLabelObject.innerHTML = text;
    }
    return this;
};

/**
 * Sets the Modal handler function
 * @param {Function} fn
 * @return {*}
 */
Window.prototype.setModalHandler = function (fn) {
    this.modalHandler = fn;
    return this;
};

/**
 * Sets the window's modal property
 * @param {Boolean} value
 */
Window.prototype.setModal = function (value) {
    if (value) {
        this.modalObject = new Modal({
            clickHandler: this.modalHandler
        });
    } else {
        this.modalObject = null;
    }
    this.modal = value;
    return this;
};

/**
 * Sets the destroy on hide property
 * @param {Boolean} value
 * @return {*}
 */
Window.prototype.setDestroyOnHide = function (value) {
    this.destroyOnHide = value;
    return this;
};

/**
 * Sets the close Button property
 * @param {Boolean} value
 * @return {*}
 */
Window.prototype.setCloseButton = function (value) {
    this.closeButton = value;
    return this;
};

Window.prototype.onTabSelectedHandler = function() {
    var that = this;
    return function() {
        var newContent;
        if(this !== that.selectedTab) {
            $(that.selectedTab.unselect().getContent().getHTML()).detach();
            that.selectedTab = this.select();
            newContent = that.selectedTab.getContent();
            newContent.setHeight($(that.body).innerHeight());
            that.body.appendChild(newContent.getHTML());
            if(typeof newContent.load === 'function') {
                newContent.load();
            }
        }
    };
};

Window.prototype.clearPanels = function() {
    var i;
    for(i = 0; i < this.panels.length; i += 1) {
        $(this.panels[i].getTab()).remove();
        $(this.panels[i].getContent().getHTML()).remove();
    }
    this.panels = [];
    return this;
};

Window.prototype.setPanels = function(panels) {
    var i;
    if(!(panels.hasOwnProperty("length") && typeof panels.push === 'function')) {
        return this;
    }
    this.clearPanels();

    for(i = 0; i < panels.length; i += 1) {
        this.addPanel(panels[i]);
    }

    return this;
};

/**
 * Adds a panel to the container window
 * @param {Panel} p
 */
Window.prototype.addPanel = function (panel) {
    var p = panel.panel, tabPanelElement;

    if(panel instanceof TabPanelElement) {
        tabPanelElement = panel;
    } else if(panel instanceof Panel) {
        p = panel;
        tabPanelElement = new TabPanelElement({
            content: panel
        });
    } else if(p instanceof Panel) {
        tabPanelElement = new TabPanelElement({
            title: panel.title,
            content: p
        });
    } else {
        return this;
    }

    tabPanelElement.onClick = this.onTabSelectedHandler();
    tabPanelElement.setParent(this);
    this.panels.push(tabPanelElement);
    if(this.panels.length === 1) {
        this.selectedTab = tabPanelElement;
    }
    if (this.loaded) {
        this.tabsContainer.appendChild(tabPanelElement.getTab());
        if(this.panels.length === 1) {
            this.body.appendChild(p.getHTML());
        }
    }
    p.setParent(this);

    return this;
};

Window.prototype.getPanel = function(i) {
    return this.panels[i];
};

Window.prototype.getPanels = function() {
    return this.panels;
};

/**
 * Creates the HTML Element fot the object
 * @return {*}
 */
Window.prototype.createHTML = function () {
    var marginProps, closeBtn, titleLabel, windowHeader, tabsContainer, i;
    Container.prototype.createHTML.call(this);
    marginProps = '-' + parseInt(this.height / 2, 10) + 'px 0 0 -' + parseInt(this.width / 2, 10) + 'px';
    this.style.addClasses(['adam-window']);
    this.style.addProperties({
        'z-index': 1033,
        'left': '50%',
        'top': '50%',
        'margin': marginProps
    });

    this.height -= 16;
    this.html.style.height = this.height + "px";

    windowHeader = this.createHTMLElement('div');
    windowHeader.className = 'adam-window-header';

    titleLabel = this.createHTMLElement('label');
    titleLabel.className = 'adam-window-title';
    titleLabel.innerHTML = this.title || "&nbsp;";
    titleLabel.title = titleLabel.innerHTML;

    if (this.closeButton) {
        closeBtn = this.createHTMLElement('span');
        closeBtn.className = 'adam-window-close';
        windowHeader.appendChild(closeBtn);
        this.html.insertBefore(windowHeader, this.body);
        this.closeButtonObject = closeBtn;
    } else {
        this.html.insertBefore(windowHeader, this.body);
    }

    windowHeader.appendChild(titleLabel);

    tabsContainer = this.createHTMLElement("ul");
    tabsContainer.className = 'adam-tabs';
    this.html.insertBefore(tabsContainer, this.body);
    this.tabsContainer = tabsContainer;

    for(i = 0; i < this.panels.length; i += 1) {
        tabsContainer.appendChild(this.panels[i].getTab());
    }

    if(i <= 1) {
        tabsContainer.style.display = 'none';
    }

    this.windowHeader = windowHeader;
    this.titleLabelObject = titleLabel;
    if (this.body) {
        this.body.className = 'adam-window-body';
        this.body.style.height = (this.height - 22 - (i > 1 ? 22 : 0)) + 'px';
        //this.body.innerHTML = 'test';
    }
    return this.html;
};

/**
 * Shows the window
 */
Window.prototype.show = function () {
    var panel;
    if (!this.loaded) {
        this.load();
    }
    if (this.modal) {
        this.modalObject.show();
    }
    if (this.selectedTab) {
        this.selectedTab.select();
        panel = this.selectedTab.getContent();
        panel.setHeight($(this.body).innerHeight());
        this.body.appendChild(panel.getHTML());
        panel.load();
    }
    document.body.appendChild(this.html);

    //here do visible the window
    this.setVisible(true);
};

/**
 * Opens/Creates the windows object
 * @private
 */
Window.prototype.load = function () {
    if (!this.html) {
        this.createHTML();
        this.attachListeners();
        this.loaded = true;
    }
};


/**
 * Close the window and destroy the object
 */
Window.prototype.close = function () {
    if (this.visible) {
        this.hide();
    }
    if (this.dispose) {
        this.dispose();
    }
};

/**
 * Hides the window
 * @param {Boolean} [destroy]
 */
Window.prototype.hide = function (destroy) {
    if (this.modal) {
        this.modalObject.hide();
    }
    document.body.removeChild(this.html);
    this.setVisible(false);
    if (destroy || this.destroyOnHide) {
        this.close();
    }
};

/**
 * Sets the window listeners
 */
Window.prototype.attachListeners = function () {
    var self = this;
    $(this.html).draggable({
        cursor: "move",
        scroll: false,
        containment: "document"
    }).on('keydown keyup keypress', function(e) {
        e.stopPropagation();
    });
    if (this.closeButton && this.closeButtonObject) {
        $(this.closeButtonObject).click(function (e) {
            e.stopPropagation();
            self.hide();
        });
    }
};

//TabPanelElement
    var TabPanelElement = function(settings) {
        Base.call(this, settings);
        this.title = null;
        this.tab = null;
        this.content = null;
        this.parent = null;
        this.onClick = null;
        this.selected = null;
        TabPanelElement.prototype.initObject.call(this, settings);
    };

    TabPanelElement.prototype.initObject = function(settings) {
        var defaults = {
            title: null,
            content: null,
            parent: null,
            onClick: null
        };

        $.extend(true, defaults, settings );

        this.onClick = defaults.onClick;

        this.setTitle(defaults.title)
            .setContent(defaults.content)
            .setParent(defaults.parent);
    };

    TabPanelElement.prototype.setTitle = function(title) {
        this.title = title;
        return this;
    };

    TabPanelElement.prototype.getTitle = function() {
        return this.title;
    };

    TabPanelElement.prototype.setContent = function(content) {
        if(content instanceof Panel) {
            this.content = content;
        }

        return this;
    };

    TabPanelElement.prototype.getContent = function() {
        return this.content;
    };

    TabPanelElement.prototype.setParent = function(parent) {
        this.parent = parent;
        return this;
    };

    TabPanelElement.prototype.getParent = function() {
        return this.parent;
    };

    TabPanelElement.prototype.attachListeners = function() {
        var that = this;
        $(this.tab).on("click", "a", function(e) {
            e.preventDefault();
        }).on("click", function() {
            if(typeof that.onClick === 'function') {
                that.onClick.call(that);
            }
        }).on("keydown", function(e) {
            e.stopPropagation();
        });

        return this;
    };

    TabPanelElement.prototype.createTab = function() {
        var tab, link;

        if(this.tab) {
            return this.tab;
        }

        tab = document.createElement('li');
        tab.id = this.id;
        tab.className = 'adam-tab';
        link = document.createElement("a");
        link.href= '#';
        if(this.title) {
        link.appendChild(document.createTextNode(this.title));
        } else {
            link.innerHTML = "&nbsp;";
        }

        tab.appendChild(link);
        this.tab = tab;

        this.attachListeners();

        return this.tab;
    };

    TabPanelElement.prototype.getTab = function() {
        if(!this.tab) {
            this.createTab();
            if(this.selected) {
                this.select();    
            } else {
                this.unselect();
            }
            
        }
        return this.tab;
    };

    TabPanelElement.prototype.isSelected =function() {
        return this.selected;
    };

    TabPanelElement.prototype.select = function() {
        this.selected = true;
        if(this.tab) {
            $(this.tab).addClass("active");
        }
        return this;
    };

    TabPanelElement.prototype.unselect = function() {
        this.selected = false;
        if(this.tab) {
            $(this.tab).removeClass("active");
        }
        return this;
    };

/**
 * @class Action
 * Handle Actions
 * @extend Base
 *
 *
 * @constructor
 * Create a new instance of the class
 * @param {Object} options
 */
var Action = function (options) {
    Base.call(this, options);
    /**
     * Defines the text of the action
     * @type {String}
     */
    this.text = null;
    /**
     * Defines if the actions is enabled
     * @type {Boolean}
     */
    this.disabled = null;
    /**
     * Defines if the action should be showed
     * @type {Boolean}
     */
    this.hidden = null;
    /**
     * Defines the handler of the action
     * @type {Function}
     */
    this.handler = null;
    /**
     * Defines a style for the action
     * @type {String}
     */
    this.cssStyle = null;
    /**
     * Defines the object associated to this action
     * @type {Object}
     */
    this.related = null;
    Action.prototype.initObject.call(this, options);
};
Action.prototype = new Base();
/**
 * Defines the object's type
 * @type {String}
 */
Action.prototype.type = "Action";
/**
 * Defines the object's family
 * @type {String}
 */
Action.prototype.family = "Action";

/**
 * Initialize the object with default values
 * @param {Object} options
 */
Action.prototype.initObject = function (options) {
    var defaults = {
        text: null,
        cssStyle: null,
        disabled: false,
        hidden: false,
        handler: function () {

        },
        related: null
    };
    $.extend(true, defaults, options);
    this.setText(defaults.text)
        .setCssClass(defaults.cssStyle)
        .setDisabled(defaults.disabled)
        .setHidden(defaults.hidden)
        .setHandler(defaults.handler)
        .setRelated(defaults.related);
};

/**
 * Sets the action text property
 * @param text
 * @return {*}
 */
Action.prototype.setText = function (text) {
    this.text = text;
    return this;
};

/**
 * Sets the action's handler
 * @param {Function} fn
 * @return {*}
 */
Action.prototype.setHandler = function (fn) {
    if (_.isFunction(fn)) {
        this.handler = fn;
    }
    return this;
};

/**
 * Sets the CSS classes
 * @param {String} css
 * @return {*}
 */
Action.prototype.setCssClass = function (css) {
    this.cssStyle = css;
    return this;
};

/**
 * Sets the enabled property
 * @param {Boolean} value
 * @return {*}
 */
Action.prototype.setDisabled = function (value) {
    if (_.isBoolean(value)) {
        this.disabled = value;
        if (this.related) {
            if (_.isFunction(this.related.paint)) {
                this.related.paint();
            }
        }
    }
    return this;
};


/**
 * Sets the hidden property
 * @param {Boolean} value
 * @return {*}
 */
Action.prototype.setHidden = function (value) {
    if (_.isBoolean(value)) {
        this.hidden = value;
        if (this.related) {
            if (_.isFunction(this.related.paint)) {
                this.related.paint();
            }
        }
    }
    return this;
};

/**
 * Sets the action's associated object
 * @param {Object} relation
 * @return {*}
 */
Action.prototype.setRelated = function (relation) {
    this.related = relation;
    return this;
};

/**
 * Turns on the action
 */
Action.prototype.enable = function () {
    this.setDisabled(false);
};

/**
 * Turns off the action
 */
Action.prototype.disable = function () {
    this.setDisabled(true);
};

/**
 * Shows the action
 */
Action.prototype.hide = function () {
    this.setHidden(true);
};

/**
 * Hides the action
 */
Action.prototype.show = function () {
    this.setHidden(false);
};

/**
 * Returns the enabled property
 * @return {Boolean}
 */
Action.prototype.isEnabled = function () {
    return !this.disabled;
};

/**
 * Returns the hidden property
 * @return {Boolean}
 */
Action.prototype.isHidden = function () {
    return this.hidden;
};

/**
 * Defines the action validation
 * @type {Boolean}
 */
Action.prototype.isAction = true;


/**
 * @class Menu
 * Handles the Menues
 * @extend Container
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var Menu = function (options) {
    Container.call(this, options);
    /**
     * Items Arrays
     * @type {Array}
     */
    this.items = [];
    /**
     * Defines the menu name
     * @type {String}
     */
    this.name = null;
    /**
     * Defines the menu's state
     * @type {String}
     */
    this.state = null;
    /**
     * Defines the menu's tooltip
     * @type {String}
     */
    this.toolTip = null;
    /**
     * Defines the parent object
     * @type {Object}
     */
    this.parent = null;

    this.canvas = null;

    this.visible = null;

    this.currentItem = null;

    this.loaded = false;

    Menu.prototype.initObject.call(this, options);
};
Menu.prototype = new Container();

/**
 * Defines the object's type
 * @type {String}
 */
Menu.prototype.type = "Menu";

/**
 * Defines the object's family
 * @type {String}
 */
Menu.prototype.family = "Menu";

/**
 * Initialize the object with default values
 * @param {Object} options
 */
Menu.prototype.initObject = function (options) {
    var defaults = {
        parent: null,
        items: [],
        name: null,
        state: null,
        toolTip: null,
        parentMenu: null,
        canvas: null,
        visible: false,
        currentItem: null
    };
    $.extend(true, defaults, options);
    this.setCanvas(defaults.canvas)
        .setItems(defaults.items)
        .setName(defaults.name)
        .setState(defaults.state)
        .setParent(defaults.parent)
        //.setParentMenu(defaults.parentMenu)
        .setToolTip(defaults.toolTip)
        .setVisible(defaults.visible)
        .setCurrentItem(defaults.currentItem);
};

/**
 * Sets the items of the menu
 * @param {Array} items
 * @return {*}
 */
Menu.prototype.setItems = function (items) {
    var item,
        i;
    for (i = 0; i < items.length; i += 1) {
        switch (items[i].jtype) {
        case 'separator':
            item = new SeparatorItem(items[i], this);
            break;
        case 'checkbox':
            item = new CheckboxItem(items[i], this);
            break;
        default:
            item = new MenuItem(items[i], this);
        }
        this.items.push(item);
    }
    this.calculateDimension();
    return this;
};

/**
 * Sets the name property
 * @param {String} text
 * @return {*}
 */
Menu.prototype.setName = function (text) {
    this.name = text;
    return this;
};

/**
 * Sets the state property
 * @param {String} state
 * @return {*}
 */
Menu.prototype.setState = function (state) {
    this.state = state;
    return this;
};

/**
 * Sets the tool tip property
 * @param {String} text
 * @return {*}
 */
Menu.prototype.setToolTip = function (text) {
    this.toolTip = text;
    return this;
};

/**
 * Sets the parent's menu property
 * @param {Object} obj
 * @return {*}
 */
Menu.prototype.setParent = function (obj) {
    if (typeof obj === 'object') {
        this.parent = obj;
    }
    return this;
};

// Menu.prototype.setParentMenu = function (obj) {
//     if (typeof obj === 'object') {
//         this.parentMenu = obj;
//     }
//     return this;
// };

Menu.prototype.setCanvas = function (obj) {
    this.canvas = obj;
    return this;
};

Menu.prototype.setVisible = function (value) {
    this.visible = value;
    return this;
};

Menu.prototype.setCurrentItem = function (item) {
    if (this.currentItem && this.currentItem.hasMenuActive) {
        this.currentItem.setFocused(false);
        this.currentItem.setHasMenuActive(false);
        this.currentItem.setActiveItem(false);
        this.currentItem.setActiveMenu(false);
    }
    this.currentItem = item;
    return this;
};

Menu.prototype.createHTML = function () {
    Element.prototype.createHTML.call(this);
    this.style.addClasses(['adam-menu']);
    this.setZOrder(1000);
    this.generateMenu();
    return this.html;
};

Menu.prototype.generateMenu = function () {
    var i, ul;
    ul = this.createHTMLElement('ul');
    ul.className = 'adam-list';
    for (i = 0; i < this.items.length; i += 1) {
        ul.appendChild(this.items[i].getHTML());
    }
    this.html.appendChild(ul);
    return this;
};

Menu.prototype.paint = function () {

};

/**
 * Sets the menu's position and show the menu
 * @param {Number} x
 * @param {Number} y
 */
Menu.prototype.show = function (x, y) {
    if (this.canvas) {
        if (!this.loaded) {
            this.setPosition(x, y);
            this.calculateItemCoords();
        }
        this.canvas.html.appendChild(this.getHTML());
        if (!this.loaded) {
            this.attachListeners();
            this.loaded = true;
        }
        this.setVisible(true);
        if (this.parent.type === 'AdamCanvas') {
            this.parent.setCurrentMenu(this);
        } else if (this.parent.type !== 'MenuItem') {
            this.parent.canvas.setCurrentMenu(this);
        }
    }
};

Menu.prototype.calculateDimension = function () {
    var c, h, i, len, label, w;
    h = 4;
    c = 0;
    for (i = 0; i < this.items.length; i += 1) {
        switch (this.items[i].getType()) {
        case 'MenuItem':
        case 'CheckboxItem':
            h += 24;
            break;
        case 'SeparatorItem':
            h += 4;
            break;
        }
        label = this.items[i].label || "";
        if (label !== "") {
            len = this.calculateWidth(label);
            if (len > c) {
                c = len;
            }
        }
    }

    w = 21 + 48 + 2 + c;
    this.setDimension(w, h);
    return this;
};

Menu.prototype.attachListeners = function () {
    var i;
    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].attachListeners();
    }
    return this;
};

Menu.prototype.hide = function () {
    var i;
    if (this.canvas && this.visible) {
        for (i = 0; i < this.items.length; i += 1) {
            if (this.items[i].menu) {
                this.items[i].menu.hide();
            }
        }
        this.canvas.html.removeChild(this.getHTML());
        this.setVisible(false);
        if (this.parent.type === "AdamCanvas") {
            this.parent.setCurrentMenu(null);
        }
    }
};

Menu.prototype.calculateItemCoords = function () {
    var h, ht, i;
    ht = 2;
    for (i = 0; i < this.items.length; i += 1) {
        switch (this.items[i].getType()) {
        case 'CheckboxItem':
        case 'MenuItem':
            this.items[i].setPosition(this.x, this.y + ht);
            this.items[i].setDimension(this.width - 2, 24);
            h = 24;
            ht += h;
            break;
        default:
            this.items[i].setPosition(this.x, ht);
            this.items[i].setDimension(this.width - 2, 4);
            h = 4;
            ht += h;
        }
    }
};

/**
 * @class Item
 * Handles a menu item
 * @extend Element
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object/Action} options
 * @param {Menu} [parent]
 */
var Item = function (options, parent) {
    Element.call(this, options);
    /**
     * Defines the Label of the item
     * @type {String}
     */
    this.label = null;
    /**
     * Defines the action associated
     * @type {Action}
     */
    this.action = null;
    /**
     * Defines the parent menu associated
     * @type {Menu}
     */
    this.parentMenu = null;
    /**
     * Defines the child menu associated
     * @type {Menu}
     */
    this.menu = null;
    /**
     * Defines the tooltip value
     * @type {String}
     */
    this.toolTip = null;

    this.disabled = null;

    this.focused = null;

    this.icon = null;

    Item.prototype.initObject.call(this, options, parent);
};
Item.prototype = new Element();

/**
 * Defines the object's type
 * @type {String}
 */
Item.prototype.type = "Item";

/**
 * Defines the object's family
 * @type {String}
 */
Item.prototype.family = "Item";

/**
 * Initialize the object with the default values
 * @param {Object/Action} options
 */
Item.prototype.initObject = function (options, parent) {

    var defaults = {
        label: null,
        menu: null,
        toolTip: null,
        parentMenu: parent || null,
        disabled: false,
        focused: false,
        icon: 'adam-menu-icon-empty'
    };
    if (options && options.isAction) {
        this.loadAction(options, parent);
    } else {
        $.extend(true, defaults, options);
        this.setLabel(defaults.label)
            .setToolTip(defaults.toolTip)
            .setParentMenu(defaults.parentMenu)
            .setDisabled(defaults.disabled)
            .setIcon(defaults.icon)
            .setFocused(defaults.focused);
        if (!defaults.action) {
            this.action = new Action({
                text: defaults.label,
                cssStyle: defaults.icon,
                handler: defaults.handler
            });
        }
        if (defaults.menu) {
            this.setChildMenu(defaults.menu);
        }
    }
};

/**
 * Loads the action to the item
 * @param {Action} action
 */
Item.prototype.loadAction = function (action, parent) {
    this.action = action;
    this.setLabel(this.action.text);
    this.setIcon(this.action.cssStyle);
    this.setDisabled(this.action.disabled);
    this.setParentMenu(parent);
    this.setFocused(false);
    if (action.menu) {
        this.setChildMenu(action.menu);
    }
};

/**
 * Sets the item's label
 * @param {String} text
 * @return {*}
 */
Item.prototype.setLabel = function (text) {
    this.label = text;
    if (this.action) {
        this.action.setText(text);
    }
    return this;
};

Item.prototype.setIcon = function (icon) {
    this.icon = icon;
    if (this.action) {
        this.action.setCssClass(icon);
    }
    return this;
};



/**
 * Defines the way to paint this item
 */
Item.prototype.paint = function () {
    //TODO Implement this class
};

/**
 * Sets the parent menu
 * @param {Menu} parent
 * @return {*}
 */
Item.prototype.setParentMenu = function (parent) {
    this.parentMenu = parent;
    return this;
};

/**
 * Sets the child Menu
 * @param {Menu} child
 * @return {*}
 */
Item.prototype.setChildMenu = function (child) {
    if (child instanceof Menu) {
        //child.setParentMenu(this.parentMenu);
        child.setCanvas(this.parentMenu.canvas);
        child.setParent(this);
        this.menu = child;
    } else {
        //child.parentMenu = this.parentMenu;
        child.canvas = this.parentMenu.canvas;
        child.parent = this;
        this.menu = new Menu(child);
    }
    return this;
};

Item.prototype.setDisabled = function (value) {
    this.disabled = value;
    return this;
};

Item.prototype.setFocused = function (value) {
    this.focused = value;
    return this;
};

/**
 * Sets the tool tip value
 * @param {String} value
 * @return {*}
 */
Item.prototype.setToolTip = function (value) {
    this.toolTip = value;
    return this;
};

Item.prototype.createHTML = function () {
    var li;
    li = this.createHTMLElement('li');
    li.className = 'adam-item';
    if (this.disabled) {
        li.className = li.className + ' adam-disabled';
    }
    li.id = UITools.getIndex();
    this.html = li;
    return this.html;
};

Item.prototype.attachListeners = function () {

};
Item.prototype.closeMenu = function () {
    if (this.parentMenu && this.parentMenu.canvas && this.parentMenu.canvas.currentMenu) {
        this.parentMenu.canvas.currentMenu.hide();
    }
};
/**
 * @class CheckboxItem
 * Handle checkboxes into the context menu
 * @extend Item
 *
 *
 * @constructor
 * Creates a new instance of this class
 * @param {Object} options
 * @param {Menu} [parent]
 */
var CheckboxItem = function (options, parent) {
    Item.call(this, options, parent);
    /**
     * Defines the checkbox's status
     * @type {Boolean}
     */
    this.checked = null;
    this.itemAnchor = null;
    CheckboxItem.prototype.initObject.call(this, options);
};
CheckboxItem.prototype = new Item();

/**
 * Defines the object's type
 * @type {String}
 */
CheckboxItem.prototype.type = "CheckboxItem";

/**
 * Initializes the object with the default values
 * @param {Object} options
 * @private
 */
CheckboxItem.prototype.initObject = function (options) {
    var defaults = {
        checked: false
    };
    $.extend(true, defaults, options);
    this.setChecked(defaults.checked);
};

/**
 * Sets the checkbox checked property
 * @param {Boolean} value
 * @return {*}
 */
CheckboxItem.prototype.setChecked = function (value) {
    if (_.isBoolean(value)) {
        this.checked = value;
    }
    return this;
};

CheckboxItem.prototype.createHTML = function () {
    var labelSpan, iconSpan;
    Item.prototype.createHTML.call(this);

    this.itemAnchor = this.createHTMLElement('a');
    this.itemAnchor.href = "#";

    labelSpan = this.createHTMLElement('span');
    labelSpan.innerHTML = this.label;
    labelSpan.className = "adam-label";

    iconSpan = this.createHTMLElement('span');
    iconSpan.className = (this.checked) ? 'adam-check-checked' : 'adam-check-unchecked';

    this.itemAnchor.appendChild(iconSpan);
    this.itemAnchor.appendChild(labelSpan);

    this.html.appendChild(this.itemAnchor);
    return this.html;
};

CheckboxItem.prototype.attachListeners = function () {
    var self = this;
    if (this.html) {
        $(this.itemAnchor)
            .click(function (e) {
                e.stopPropagation();
                if (!self.disabled) {
                    self.closeMenu();
                    self.action.handler(!self.checked);
                }
            })
            .mouseover(function () {
                self.setActiveItem(true);
            })
            .mouseout(function () {
                self.setActiveItem(false);
            })
            .mouseup(function (e) {
                e.stopPropagation();
            })
            .mousedown(function (e) {
                e.stopPropagation();
            });
    }
};

CheckboxItem.prototype.setActiveItem = function (value) {
    if (!this.disabled) {
        if (value) {
            this.style.addClasses(['adam-item-active']);
            this.style.applyStyle();
            this.parentMenu.setCurrentItem(this);
        } else {
            this.style.removeClasses(['adam-item-active']);
            this.style.applyStyle();
        }
    }
};

/**
 * @class SeparatorItem
 * Handles the menu item separator
 * @extend Item
 *
 * @constructor
 * Creates a new instance of a class
 * @param {Object} options
 * @param {Menu} [parent]
 */
var SeparatorItem = function (options, parent) {
    Item.call(this, options, parent);
};
SeparatorItem.prototype = new Item();

/**
 * Defines the object's type
 * @type {String}
 */
SeparatorItem.prototype.type = "SeparatorItem";

/**
 * Creates the HTML
 * @return {HTMLElement}
 */
SeparatorItem.prototype.createHTML = function () {
    var spanSep, itemSep;

    itemSep = this.createHTMLElement('li');
    itemSep.className = 'adam-item-separator';

    spanSep = this.createHTMLElement('span');
    spanSep.className = 'adam-separator';
    spanSep.innerHTML = " ";

    itemSep.appendChild(spanSep);
    this.html = itemSep;

    return this.html;
};

/**
 * @class MenuItem
 * Handles the items into the menu
 * @extend Item
 *
 * @constructor
 * Creates a new instance of the MenuItem Class
 * @param {Object} options
 * @param {Menu} [parent]
 */
var MenuItem = function (options, parent) {
    Item.call(this, options, parent);
    /**
     * Defines the icon to be used into the item
     * @type {String}
     */
    this.itemAnchor = null;
    this.hasMenuActive = null;
    MenuItem.prototype.initObject.call(this, options);
};
MenuItem.prototype = new Item();

/**
 * Defines the object's type
 * @type {String}
 */
MenuItem.prototype.type = "MenuItem";

/**
 * Initializes the object with default values
 * @param {Object} options
 * @private
 */
MenuItem.prototype.initObject = function (options) {
    var defaults = {
        hasMenuActive: false
    };
    $.extend(true, defaults, options);
    this.setHasMenuActive(defaults.hasMenuActive);
};


MenuItem.prototype.setHasMenuActive = function (value) {
    this.hasMenuActive = value;
    return this;
};

MenuItem.prototype.createHTML = function () {
    var labelSpan, iconSpan;
    Item.prototype.createHTML.call(this);


    this.itemAnchor = this.createHTMLElement('a');
    this.itemAnchor.href = '#';

    if (this.menu) {
        this.itemAnchor.className = 'adam-item-arrow';
    }

    labelSpan = this.createHTMLElement('span');
    labelSpan.innerHTML = this.label;
    labelSpan.className = "adam-label";

    iconSpan = this.createHTMLElement('span');
    iconSpan.className = 'adam-item-icon ' + this.icon;

    this.itemAnchor.appendChild(iconSpan);
    this.itemAnchor.appendChild(labelSpan);

    this.html.appendChild(this.itemAnchor);
    return this.html;

};

MenuItem.prototype.attachListeners = function () {
    var self = this;
    if (this.html) {
        $(this.itemAnchor)
            .click(function (e) {

               e.stopPropagation();
               if (!self.menu && !self.disabled) {
                    self.closeMenu();
                    self.action.handler();
                }
                e.preventDefault();
            })
            .mouseover(function () {
                self.setActiveItem(true);
                self.setActiveMenu(true);
                // if (self.menu && !self.disabled) {
                //     self.menu.show(self.x + self.width, self.y);
                //     self.setHasMenuActive(true);
                // }
            })
            .mouseout(function () {
                self.setActiveItem(false);
                self.setActiveMenu(false);
                // if (self.menu && !self.disabled) {
                //     self.menu.hide();
                // }
            })
            .mouseup(function (e) {
                e.stopPropagation();
            })
            .mousedown(function (e) {
                e.stopPropagation();
            });
    }
};

MenuItem.prototype.setActiveItem = function (value) {
    if (!this.disabled) {
        if (value) {
            if (!this.focused) {
                this.style.addClasses(['adam-item-active']);
                this.style.applyStyle();
                this.parentMenu.setCurrentItem(this);
            }
        } else {
            if (!this.hasMenuActive) {
                this.style.removeClasses(['adam-item-active']);
                this.style.applyStyle();
                this.setFocused(false);
            }
        }
    }
};

MenuItem.prototype.setActiveMenu = function (value) {
    if (this.menu && !this.disabled) {
        if (value) {
            if (!this.focused) {
                this.menu.show(this.x + this.width, this.y);
                this.setHasMenuActive(true);
                this.setFocused(true);
            }
        } else {
            if (!this.hasMenuActive) {
                this.menu.hide();
            }
        }
    }
};

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

/**
 * @class Tooltip
 * Handle tool tip messages
 * @extend Element
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Object} parent
 */
var Tooltip = function (options, parent) {
    Element.call(this, options);
    this.icon =  null;
    this.css = null;
    this.message = null;
    this.parent = null;
    this.messageObject = null;
    this.hoverParent = null;
    this.hoverClass = null;
    Tooltip.prototype.initObject.call(this, options, parent);
};

Tooltip.prototype = new Element();

Tooltip.prototype.type = 'Tooltip';

Tooltip.prototype.family = 'Tooltip';

Tooltip.prototype.initObject = function (options, parent) {
    var defaults = {
        message: null,
        icon: 'adam-tooltip-icon-default',
        css: '',
        parent: parent || null,
        hoverParent: true,
        hoverClass: 'hovered'
    };
    $.extend(true, defaults, options);
    this.setIcon(defaults.icon)
        .setMessage(defaults.message)
        .setParent(defaults.parent)
        .setCss(defaults.css)
        .setHoverClass(defaults.hoverClass)
        .setHoverParent(defaults.hoverParent);
};

Tooltip.prototype.setIcon = function (icon) {
    this.icon = icon;
    return this;
};

Tooltip.prototype.setMessage = function (msg) {
    this.message = msg;
    return this;
};

Tooltip.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};


Tooltip.prototype.setCss = function (value) {
    this.css = value;
    return this;
};

Tooltip.prototype.setHoverParent = function (value) {
    this.hoverParent = value;
    return this;
};

Tooltip.prototype.setHoverClass = function (css) {
    this.hoverClass = css;
    return this;
};

Tooltip.prototype.createHTML = function () {
    var msgDiv, iconSpan, tooltipAnchor;

    tooltipAnchor = this.createHTMLElement('a');
    tooltipAnchor.href = '#';
    tooltipAnchor.className = 'adam-tooltip ' + this.css;

    iconSpan = this.createHTMLElement('span');
    iconSpan.className = this.icon;

    // msgDiv = this.createHTMLElement('div');
    // msgDiv.className = 'adam-tooltip-message-off';
    // msgDiv.innerHTML = this.message;

    //this.messageObject = msgDiv;

    tooltipAnchor.appendChild(iconSpan);
    //tooltipAnchor.appendChild(msgDiv);

    this.html = tooltipAnchor;

    this.attachListeners();
    return this.html;
};

Tooltip.prototype.attachListeners = function () {
    var self = this;
    $(this.html).click(function (e) {
            e.preventDefault();
        })
        .mouseover(function (e) {
            e.stopPropagation();
            //console.log(e);
            self.show(e.pageX, e.pageY);
        })
        .mouseout(function (e) {
            e.stopPropagation();
            self.hide();
        });
};

Tooltip.prototype.show = function (x, y) {
    var msgDiv;

    if (!this.messageObject) {
        msgDiv = this.createHTMLElement('div');
        msgDiv.className = 'adam-tooltip-message';
        msgDiv.innerHTML = this.message;
        msgDiv.style.position = 'absolute';
        msgDiv.style.top = (y + 10) + 'px';
        msgDiv.style.left = (x + 10) + 'px';
        msgDiv.style.zIndex = 1034;

        this.messageObject = msgDiv;
    }

    document.body.appendChild(this.messageObject);
    if (this.hoverParent && this.parent) {
        $(this.parent.html).addClass(this.hoverClass);
    }
};

Tooltip.prototype.hide = function () {
    document.body.removeChild(this.messageObject);
    this.messageObject = null;
    if (this.hoverParent && this.parent) {
        $(this.parent.html).removeClass(this.hoverClass);
    }
};

/**
 * @class Panel
 * Handles panels to be inserted into containers
 * @extend Container
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var Panel = function (options) {
    Container.call(this, options);
    /**
     * Defines the header HTML element
     * @type {HTMLElement}
     */
    this.header = null;
    /**
     * Defines the footer HTML Element
     * @type {HTMLElement}
     */
    this.footer = null;
    /**
     * Defines the layout object
     * @type {Layout}
     */
    this.layout = null;

    this.language = {};
    Panel.prototype.initObject.call(this, options);
};

Panel.prototype = new Container();
/**
 * Defines the object's type
 * @type {String}
 */
Panel.prototype.type = 'Panel';

/**
 * Defines the object's family
 * @type {String}
 */
Panel.prototype.family = 'Panel';

/**
 * Initializes the object with the default values
 */
Panel.prototype.initObject = function (options) {
    var defaults = {
        layout: null
    };
    $.extend(true, defaults, options);
    this.setHeader(defaults.header)
        .setFooter(defaults.footer)
        .setLayout(defaults.layout);
};

/**
 * Sets the header HTML element
 * @param {HTMLElement} h
 */
Panel.prototype.setHeader = function (h) {
    this.header = h;
    return this;
};

/**
 * Sets the header HTML element
 * @param {HTMLElement} f
 */
Panel.prototype.setFooter = function (f) {
    this.footer = f;
    return this;
};

/**
 * Sets the header HTML element
 * @param {Layout} layout
 */
Panel.prototype.setLayout = function (layout) {
    if (layout && layout.family && layout.family === 'Layout') {
        this.layout = layout;
    } else {
        this.layout = new Layout(layout);
    }
    return this;
};

Panel.prototype.createHTML = function () {
    var headerDiv, footerDiv;
    Container.prototype.createHTML.call(this);
    this.style.removeProperties(['width', 'height', 'position', 'top', 'left', 'z-index']);
    this.style.addClasses(['adam-panel']);
    if (this.header) {
        this.html.insertBefore(this.header, this.body);
    } else {
        headerDiv = this.createHTMLElement('div');
        headerDiv.className = 'adam-panel-header';
        this.html.insertBefore(headerDiv, this.body);
        this.header = headerDiv;
    }
    if (this.footer) {
        this.html.appendChild(this.footer);
    } else {
        footerDiv = this.createHTMLElement('div');
        footerDiv.className = 'adam-panel-footer';
        this.html.appendChild(footerDiv);
        this.footer = footerDiv;
    }
    this.body.className = 'adam-panel-body';
    return this.html;
};

Panel.prototype.load = function () {

};

/*globals Panel, $, Proxy, TextField, ComboboxField, HiddenField, EmailPickerField, ItemMatrixField, MultipleItemField,
    CriteriaField, ItemUpdaterField, ExpressionField, TextareaField, CheckboxField, Button, RadiobuttonField */
/**
 * @class Form
 * Handles form panels
 * @extend Panel
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var Form = function (options) {
    Panel.call(this, options);

    /**
     * Defines if the form has a proxy
     * @type {Boolean}
     */
    this.proxyEnabled = null;

    /**
     * Defines the form's url
     * @type {String}
     */
    this.url = null;

    /**
     * Defines the form's proxy object
     * @type {Proxy}
     */
    this.proxy = null;
    /**
     * Defines the form loading state
     * @type {Boolean}
     */
    this.loaded = false;
    /**
     * Defines the form's data
     * @type {Object}
     */
    this.data = null;
    /**
     * Defines the callback functions
     * @type {Object}
     */
    this.callback = {};
    /**
     * Defines the dirty form state
     * @type {Boolean}
     */
    this.dirty = false;

    this.buttons = [];

    this.footerAlign = null;

    this.labelWidth = null;

    this.footerHeight = null;

    this.headerHeight = null;

    this.closeContainerOnSubmit = null;

    this.parent = null;

    Form.prototype.initObject.call(this, options);
};

Form.prototype = new Panel();

/**
 * Defines the object's type
 * @type {String}
 */
Form.prototype.type = 'Form';

/**
 * Initializes the object with the default values
 */
Form.prototype.initObject = function (options) {
    var defaults = {
        url: null,
        data: null,
        proxyEnabled: true,
        callback: {},
        buttons: [],
        footerAlign: 'center',
        labelWidth: '30%',
        footerHeight: 40,
        headerHeight: 0,
        closeContainerOnSubmit: false,
        language: {
            ERROR_INVALID_EMAIL: 'You must enter a valid email',
            ERROR_INVALID_INTEGER: 'Please enter only integer values',
            ERROR_REQUIRED_FIELD: 'This field is required',
            ERROR_COMPARISON: 'The comparison failed',
            ERROR_REGEXP: 'The pattern text didn\'t match with the specified one',
            ERROR_TEXT_LENGTH: 'The text length must be',
            ERROR_CHECKBOX_VALUES: 'Please insert Checkbox values (0 or 1)',
            ERROR_TEXT: 'Please insert text',
            ERROR_DATE : 'Please insert only valid dates',
            ERROR_PHONE: 'Please enter a valid Phone',
            ERROR_FLOAT: 'Please enter only valid float values',
            ERROR_DECIMAL: 'Please enter only valid decimal values',
            ERROR_URL: 'Please enter only valid url',

            TITLE_BUSINESS_RULE_EVALUATION: 'Business Rules Evaluation',
            LBL_BUSINESS: 'Business',
            LBL_OPERATOR: 'Operator',
            LBL_RESPONSE: 'Response',
            LBL_LOGIC_OPERATORS: 'Logic Operators',
            LBL_GROUP: 'Group',
            LBL_DIRECTION: 'Direction',
            LBL_MODULE: 'Module',
            LBL_FIELD: 'Field',
            LBL_VALUE: 'Value',
            LBL_TARGET_MODULE: 'Target Module',
            LBL_VARIABLE: 'Variable',
            LBL_USER: 'User',
            TITLE_MODULE_FIELD_EVALUATION: 'Module Field Evaluation',
            TITLE_FORM_RESPONSE_EVALUATION: 'Form Response Evaluation',
            TITLE_USER_EVALUATION: 'User Evaluation',
            LBL_FORM: 'Form',
            LBL_STATUS: 'Status',
            LBL_APPROVED: 'Approved',
            LBL_REJECTED: 'Rejected',
            BUTTON_SUBMIT: 'Submit',
            BUTTON_CANCEL: 'Cancel'
        }
    };
    $.extend(true, defaults, options);
    this.language = defaults.language;
    this.setUrl(defaults.url)
        .setData(defaults.data)
        .setProxyEnabled(defaults.proxyEnabled)
        .setProxy(defaults.proxy)
        .setCallback(defaults.callback)
        .setButtons(defaults.buttons)
        .setLabelWidth(defaults.labelWidth)
        .setFooterHeight(defaults.footerHeight)
        .setHeaderHeight(defaults.headerHeight)
        .setCloseContainerOnSubmit(defaults.closeContainerOnSubmit)
        .setFooterAlign(defaults.footerAlign);
};

/**
 * Sets the form's url
 * @param {String} url
 * @return {*}
 */
Form.prototype.setUrl = function (url) {
    this.url = url;
    return this;
};

/**
 * Sets the Proxy Enabled property
 * @param {Boolean} value
 * @return {*}
 */
Form.prototype.setProxyEnabled = function (value) {
    this.proxyEnabled = value;
    return this;
};

/**
 * Defines the proxy object
 * @param {Proxy} proxy
 * @return {*}
 */
Form.prototype.setProxy = function (proxy) {
    if (proxy && proxy.family && proxy.family === 'Proxy') {
        this.proxy = proxy;
        this.url = proxy.url;
        this.proxyEnabled = true;
    } else {
        if (this.proxyEnabled) {
            if (proxy) {
                if (!proxy.url) {
                    proxy.url = this.url;
                }
                this.proxy = new Proxy(proxy);
            } else {
                if (this.url) {
                    this.proxy = new Proxy({url: this.url});
                }
            }
        }
    }
    return this;
};

/**
 * Defines the form's data object
 * @param {Object} data
 * @return {*}
 */
Form.prototype.setData = function (data) {
    this.data = data;
    if (this.loaded) {
        this.applyData();
    }
    return this;
};

/**
 * Sets the form's callback object
 * @param {Object} cb
 * @return {*}
 */
Form.prototype.setCallback = function (cb) {
    this.callback = cb;
    return this;
};

Form.prototype.setFooterAlign = function (position) {
    this.footerAlign = position;
    return this;
};

Form.prototype.setLabelWidth = function (width) {
    this.labelWidth = width;
    return this;
};

Form.prototype.setFooterHeight = function (width) {
    this.footerHeight = width;
    return this;
};

Form.prototype.setHeaderHeight = function (width) {
    this.headerHeight = width;
    return this;
};

Form.prototype.setHeight = function (height) {
    var bodyHeight;
    Panel.prototype.setHeight.call(this, height);
    bodyHeight = this.height - this.footerHeight - this.headerHeight;
    this.setBodyHeight(bodyHeight);
    return this;
};

Form.prototype.setCloseContainerOnSubmit = function (value) {
    this.closeContainerOnSubmit = value;
    return this;
};
/**
 * Loads the form
 */
Form.prototype.load = function () {
    var self = this, params = null;
    if (!this.loaded) {
        if (this.proxy) {
            params = this.getRelatedFields();
            this.proxy.getData(params, {
                success: function (response) {
                    self.data = response;
                    self.applyData.call(self);
                    self.loaded = true;
                    self.attachListeners();
                    self.setDirty(false);
                }
            });

        } else {
            this.applyData.call(this);
            this.attachListeners();
            this.loaded = true;
        }

    }
};

/**
 * Returns the URL params if the form has related records
 */
Form.prototype.getRelatedFields = function () {
    var related = [];
    if (this.items) {
        for (i = 0; i < this.items.length; i += 1) {
            if (this.items[i].related) {
                related.push(this.items[i].related);
            }
        }
    }
    if (related.length > 0) {
        return {related: related.join(',')};
    } else {
        return null;
    }
};

/**
 * Reloads the form
 */

Form.prototype.reload = function () {
    this.loaded = false;
    this.load();
};

/**
 * Applies the data to the form
 * @param dontLoad boolean Set the flag to trigger loaded event. Default value is FALSE
 */
Form.prototype.applyData = function (dontLoad) {
    var propertyName, i, related;
    if (this.data) {
        //Applying related data
        if (this.data.related) {
            for (i = 0; i < this.items.length; i += 1) {
                if (this.items[i].getType() === 'ComboboxField' && this.items[i].related) {
                    related = this.items[i].related;
                    if (this.data.related[related]) {
                        this.items[i].setOptions(this.data.related[related]);
                    }
                }
            }
        }
        //Applying loaded values
        for (propertyName in this.data) {
            if (this.data.hasOwnProperty(propertyName)) {
                for (i = 0; i < this.items.length; i += 1) {
                    if (this.items[i].name === propertyName) {
                        try {
                            this.items[i].setValue(this.data[propertyName]);    
                        } catch(e) {}
                        break;
                    }
                }
            }
        }
    }
    //Triggering 'loaded' form event
    if (this.callback && this.callback.loaded && !dontLoad) {
        this.callback.loaded.call(this, this.data, this.proxy !== null);
    }
};

/**
 * Add Fields Items
 * @param {Object/Field}item
 */
Form.prototype.addItem = function (item) {
    var newItem;
    if (item && item.family && item.family === 'Field') {
        newItem = item;
        newItem.setParent(this);
    } else {
        $.extend(true, item, {language: this.language});
        if (item.jtype) {
            switch (item.jtype) {
            case 'text':
                newItem = new TextField(item, this);
                break;
            case 'combobox':
                newItem = new ComboboxField(item, this);
                break;
            case 'textarea':
                newItem = new TextareaField(item, this);
                break;
            case 'checkbox':
                newItem = new CheckboxField(item, this);
                break;
            case 'hidden':
                newItem = new HiddenField(item, this);
                break;
            case 'emailpicker':
                newItem = new EmailPickerField(item, this);
                break;
            case 'itemmatrix':
                newItem = new ItemMatrixField(item, this);
                break;
            case 'multipleitem':
                newItem = new MultipleItemField(item, this);
                break;
            case 'criteria':
                newItem = new CriteriaField(item, this);
                break;
            case 'itemupdater':
                newItem = new ItemUpdaterField(item, this);
                break;
            case 'radio':
                newItem = new RadiobuttonField(item, this);
                break;

            }
        }
    }
    if (newItem) {
        this.items.push(newItem);
    }
};

/**
 * Sets the items
 * @param {Array} items
 * @return {*}
 */
Form.prototype.setItems = function (items) {
    var i;
    for (i = 0; i < items.length; i += 1) {
        this.addItem(items[i]);
    }
    return this;
};

/**
 * Sets the buttons
 * @param {Array} buttons
 * @return {*}
 */
Form.prototype.setButtons = function (buttons) {
    var i;
    for (i = 0; i < buttons.length; i += 1) {
        this.addButton(buttons[i]);
    }
    return this;
};

/**
 * Resets the form
 */
Form.prototype.reset = function () {
    var i;
    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].reset();
    }
    this.setDirty(false);
    if (this.callback.reset) {
        this.callback.reset();
    }
};

/**
 * Submits the form
 */
Form.prototype.submit = function () {
    var data;
    if (this.testRequired()) {
        if (this.validate()) {
            data = this.getData();
            if (this.proxy) {
                this.proxy.sendData(data, this.callback);
            } else {
                if (this.callback.submit) {
                    this.callback.submit(data);
                }
            }
            if (this.closeContainerOnSubmit) {
                if (this.parent && this.parent.close) {
                    this.parent.close();
                }
            }
        } else {
            if (this.callback.failed) {
                this.callback.failed();
            }
        }
    } else {
        if (this.callback.required) {
            this.callback.required();
        }
    }
};

/**
 * Returns the data
 * @return {Object}
 */
Form.prototype.getData = function () {
    var i, result = {};
    for (i = 0; i < this.items.length; i += 1) {
        $.extend(result, this.items[i].getObjectValue());
    }
    return result;
};

/**
 * Sets the dirty form property
 * @param {Boolean} value
 * @return {*}
 */
Form.prototype.setDirty = function (value) {
    this.dirty = value;
    return this;
};

/**
 * Returns the dirty form property
 * @return {*}
 */
Form.prototype.isDirty = function () {
    return this.dirty;
};

/**
 * Evaluate the fields' validations
 * @return {Boolean}
 */
Form.prototype.validate = function () {
    var i, valid = true, current;
    for (i = 0; i < this.items.length; i += 1) {
        current = this.items[i].isValid();
        valid = valid && current;
        if (!current && this.items[i].errorTooltip) {
            $(this.items[i].errorTooltip.html).removeClass('adam-tooltip-error-off');
            $(this.items[i].errorTooltip.html).addClass('adam-tooltip-error-on');
        }
    }
    return valid;
};

Form.prototype.testRequired = function () {
    var i, response = true;
    for (i = 0; i < this.items.length; i += 1) {
        response = response && this.items[i].evalRequired();
    }
    return response;
};

Form.prototype.addButton = function (button) {
    var newButton;
    if (button && button.family && button.family === 'Button') {
        newButton = button;
        newButton.setParent(this);
    } else {
        newButton = new Button(button, this);
    }
    if (newButton) {
        this.buttons.push(newButton);
    }
};

Form.prototype.attachListeners = function () {
    var i;
    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].attachListeners();
    }
    for (i = 0; i < this.buttons.length; i += 1) {
        this.buttons[i].attachListeners();
    }
    //$(this.footer).draggable( "option", "disabled", true);
    $(this.body).mousedown(function (e) {
        e.stopPropagation();
    });
};

Form.prototype.onEnterFieldHandler = function (fieldObject) {
    var that = this;
    return function () {
        var i;

        for (i = 0; i < that.items.length; i += 1) {
            if (that.items[i] !== fieldObject && (that.items[i] instanceof MultipleItemField || that.items[i] instanceof CriteriaField)) {
                that.items[i].closePanel();
            }
        }
    };
};

Form.prototype.createHTML = function () {
    var i, html;
    Panel.prototype.createHTML.call(this);
    this.footer.style.textAlign = this.footerAlign;
    for (i = 0; i < this.items.length; i += 1) {
        html = this.items[i].getHTML();
        $(html).find("select, input, textarea").focus(this.onEnterFieldHandler(this.items[i]));
        this.body.appendChild(html);
    }
    for (i = 0; i < this.buttons.length; i += 1) {
        this.footer.appendChild(this.buttons[i].getHTML());
    }
    this.body.style.bottom = (this.footerHeight + 8) + 'px';
    this.footer.style.height = this.footerHeight + 'px';
    return this.html;
};

Form.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

/**
 * @class Field
 * Handle form fields
 * @extend Element
 *
 * @constructor
 * Creates a new instace of the object
 * @param {Object} options
 * @param {Form} parent
 */
var Field = function (options, parent) {
    Element.call(this, options);
    /**
     * Defines the parent Form
     * @type {Form}
     */
    this.parent = null;
    /**
     * Defines the field's label
     * @type {String}
     */
    this.label = null;
    /**
     * Defines the Value
     * @type {*}
     */
    this.value = null;
    /**
     * Defines the validator object
     * @type {Validator}
     */
    this.validators = [];
    /**
     * Defines the field's name
     * @type {String}
     */
    this.name = null;
    /**
     * Defines the required state of the field
     * @type {Boolean}
     */
    this.required = null;
    /**
     * Defines the error message to show
     * @type {String}
     */
    this.messageError = null;
    /**
     * Defines the initial value of the field
     * @type {*}
     */
    this.initialValue = null;

    /**
     * Defines if the field is required but cannot be submited
     * @type {Boolean}
     */
    this.requiredFailed = false;

    this.fieldWidth = null;

    this.helpTooltip = null;

    this.errorTooltip = null;

    this.controlObject = null;

    this.labelObject = null;

    this.change = null;

    this.readOnly = null;

    this.submit = null;

    this.proxy = null;

    this.oldRequiredValue = null;

    Field.prototype.initObject.call(this, options, parent);
};
Field.prototype = new Element();

/**
 * Defines the object's type
 * @type {String}
 */
Field.prototype.type = 'Field';

/**
 * Defines the object's family
 * @type {String}
 */
Field.prototype.family = 'Field';

/**
 * Initializes the object with the default values
 * @param {Object} options
 * @param {Form} parent
 */
Field.prototype.initObject = function (options, parent) {
    var defaults = {
        required: false,
        label: '',
        validators: [],
        value: null,
        messageError: null,
        initialValue: null,
        fieldWidth: null,
        helpTooltip: null,
        change: function () {},
        readOnly: false,
        submit: true,
        proxy: null
    };
    $.extend(true, defaults, options);
    this.setParent(parent);
    this.setRequired(defaults.required)
        .setLabel(defaults.label)
        .setName(defaults.name || (this.type + '_' + this.id))
        .setValidators(defaults.validators)
        .setMessageError(defaults.messageError)
        .setInitialValue(defaults.initialValue)
        .setFieldWidth(defaults.fieldWidth)
        .setHelpTooltip(defaults.helpTooltip)
        .setErrorTooltip({})
        .setChangeHandler(defaults.change)
        .setReadOnly(defaults.readOnly)
        .setSubmit(defaults.submit)
        .setProxy(defaults.proxy)
        .setValue(defaults.value);
};

/**
 * Sets the required property
 * @param {Boolean} value
 * @return {*}
 */
Field.prototype.setRequired = function (value) {
    this.required = value;
    return this;
};
/**
 * Takes the sent parameter and set it as the value in the control.
 * @param {String} value
 * @private
 */
Field.prototype._setValueToControl = function (value) {
    if (this.html && this.controlObject) {
        this.controlObject.value = this.value;
    }
    return this;
};
/**
 * Sets the field's value
 * @param {*} value
 * @param {Boolean} [change]
 * @return {*}
 */
Field.prototype.setValue = function (value, change) {
    if (change) {
        this.value = value;
    } else {
        this.value = value || this.initialValue;
    }
    this._setValueToControl(this.value);
    if (this.proxy) {
        this.load();
    }
    return this;
};

/**
 * Sets the field's name
 * @param {String} name
 * @return {*}
 */
Field.prototype.setName = function (name) {
    this.name = name;
    return this;
};

/**
 * Sets the field's label
 * @param {String} label
 * @return {*}
 */
Field.prototype.setLabel = function (label) {
    this.label = label;
    return this;
};

/**
 * Sets the validator property
 * @param {Object/Validator} val
 * @return {*}
 */
Field.prototype.setValidators = function (val) {
    var i;

    for (i = 0; i < val.length; i += 1) {
        if (val[i] && val[i].family && val[i].family === 'Validator') {
            this.validators.push(val[i]);
        } else {
            this.validators.push(this.validatorFactory(val[i]));
        }
    }
    return this;
};

/**
 * Sets the fields validation error message
 * @param {String} msg
 * @return {*}
 */
Field.prototype.setMessageError = function (msg) {
    this.messageError = msg;
    return this;
};

/**
 * Sets the parent object
 * @param {Form} parent
 * @return {*}
 */
Field.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

/**
 * Sets the initial value property
 * @param {*} value
 * @return {*}
 */

Field.prototype.setInitialValue = function (value) {
    this.initialValue = value;
    return this;
};

Field.prototype.setFieldWidth = function (width) {
    this.fieldWidth = width;
    return this;
};

Field.prototype.setHelpTooltip = function (tooltip) {
    if (tooltip) {
        if (!tooltip.css) {
            tooltip.css = 'adam-tooltip-help';
        }
        this.helpTooltip = new Tooltip(tooltip, this);
    } else {
        this.helpTooltip = null;
    }
    return this;
};

Field.prototype.setErrorTooltip = function (tooltip) {
    if (tooltip) {
        if (!tooltip.css) {
            tooltip.css = 'adam-tooltip-error-off';
        }
        if (!tooltip.icon) {
            tooltip.icon = 'adam-tooltip-icon-error';
        }
        tooltip.visible = false;
        this.errorTooltip = new Tooltip(tooltip, this);
    } else {
        this.errorTooltip = null;
    }
    return this;
};

Field.prototype.setChangeHandler = function (fn) {
    this.change = fn;
    return this;
};

Field.prototype.setReadOnly = function (value) {
    this.readOnly = value;
    if (this.html) {
        this.controlObject.disabled = value;
    }
    return this;
};

Field.prototype.setSubmit = function (value) {
    this.submit = value;
    return this;
};

Field.prototype.setProxy = function (newProxy) {
    this.proxy = newProxy;
    return this;
};

/**
 * Returns a validator object
 * @param {Object} validator
 * @return {Validator}
 */
Field.prototype.validatorFactory = function (validator) {
    var out = null,
        regexp = {
            email: {
                pattern: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                //message: this.parent.language.ERROR_INVALID_EMAIL
            },
            integer: {
                pattern: /^\s*(\+|-)?\d+\s*$/
                //message: this.parent.language.ERROR_INVALID_INTEGER
            },
            required: {
                pattern: /\S/
                //message: this.parent.language.ERROR_REQUIRED_FIELD
            }
        };
    if (validator && validator.jtype) {
        switch (validator.jtype) {
        case 'required':
            if (validator.criteria && !validator.criteria.trim) {
                /*validator.jtype = 'required_without_spaces';*/
                /*} else {*/
                out = new TextLengthValidator({
                    criteria: {
                        trim: false,
                        minLength: 1
                    },
                    errorMessage: validator.errorMessage || this.parent.language.ERROR_REQUIRED_FIELD
                }, this);
            }
            break;
        case 'email':
        case 'integer':
            validator.criteria = regexp[validator.jtype].pattern;
            out = new RegExpValidator($.extend({
                errorMessage: regexp[validator.jtype].message
            }, validator), this);
            break;
        case 'comparison':
            out = new ComparisonValidator($.extend({
                errorMessage: this.parent.language.ERROR_COMPARISON
            }, validator), this);
            break;
        case 'regexp':
            out = new RegExpValidator($.extend({
                errorMessage: this.parent.language.ERROR_REGEXP
            }, validator), this);
            break;
        case 'textLength':
            out = new TextLengthValidator($.extend({
                errorMessage: this.parent.language.ERROR_TEXT_LENGTH
            }, validator), this);
            break;
        case 'custom':
            out = new CustomValidator($.extend({
                errorMessage: ""
            }, validator), this);
            break;
        case 'number':
            out = new NumberValidator($.extend({
                errorMessage: ""
            }, validator), this);
            break;
        case 'range':
            out = new RangeValidator($.extend({
                errorMessage: ""
            }, validator), this);
            break;
        default:
            out = new Validator($.extend({
                errorMessage: ""
            }, validator), this);
        }
    } else {
        out = new Validator(null, this);
    }
    return out;
};

/**
 * Returns the object representation of the field
 * @return {Object}
 */
Field.prototype.getObjectValue = function () {
    var result = {};
    if (this.submit) {
        result[this.name] = this.value;
    }
    return result;
};

/**
 * Returns the evaluation if the fields is required
 * @return {Boolean}
 */
Field.prototype.evalRequired = function () {
    var response = true, value;
    if (this.required) {
        response = (this.value !== null && this.value !== '' && this.value.trim());
        if (!response) {
            $(this.controlObject).addClass('required');
        } else {
            $(this.controlObject).removeClass('required');
        }
    }
    return response;
};

/**
 * Resets the field
 */
Field.prototype.reset = function () {
    this.setValue(this.initialValue || null, true);
    if (this.errorTooltip) {
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-on');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-off');
    }
    if (this.required && this.controlObject) {
        $(this.controlObject).removeClass('required');
    }
};

Field.prototype.attachListeners = function () {

};

Field.prototype.createHTML = function () {
    Element.prototype.createHTML.call(this);
    this.style.removeProperties(['position', 'width', 'height', 'top', 'left', 'z-index']);
    this.style.addClasses(['adam-field']);
    return this.html;
};

Field.prototype.isValid = function () {
    var i, res = true;

    for (i = 0; i < this.validators.length; i += 1) {
        res = res && this.validators[i].isValid();

        if (!res) {
            this.errorTooltip.setMessage(this.validators[i].getErrorMessage());
            $(this.errorTooltip.html).removeClass('adam-tooltip-error-off');
            $(this.errorTooltip.html).addClass('adam-tooltip-error-on');
            return res;
        }
    }

    if (res) {
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-on');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-off');
    }

    return res;
};

Field.prototype.onChange = function (newValue, oldValue) {
    if (this.required) {
        this.evalRequired();
    }

    this.isValid();

    if (this.change) {
        this.change(this, newValue, oldValue);
    }
    this.parent.setDirty(true);
    return this;
};


Field.prototype.doLoad = function () {
    if (this.proxy) {
        this.load();
    }
};

/**
 * @abstract
 * Loads the field through the proxy defined
 */
Field.prototype.load = function () {

};

/**
 * @class Validator
 * Handles the validations of the fields
 * @extend Base
 *
 * @constructor
 * Create a new instance of the class
 * @param {Object} options
 * @param {Field} parent
 */
var Validator = function (options, parent) {
    Base.call(this, options);
    /**
     * Defines the Field parent
     * @type {Field}
     */
    this.parent = null;
    /**
     * Defines the criteria object
     * @type {Object}
     */
    this.criteria = null;
    /**
     * Defines if the object is validated
     * @type {Boolean}
     */
    this.validated = false;
    /**
     * Defines the validation state
     * @type {null/Boolean}
     */
    this.valid = null;
    /**
     * Defines the error message to show in case of the validation fails
     * @type {null/Boolean}
     */
    this.errorMessage = null;
    Validator.prototype.initObject.call(this, options, parent);
};
Validator.prototype = new Base();

/**
 * Defines the object's type
 * @type {String}
 */
Validator.prototype.type = 'Validator';

/**
 * Defines the object's family
 * @type {String}
 */
Validator.prototype.family = 'Validator';

/**
 * Initializes the object with default values
 * @param {Object} options
 * @param {Field} parent
 */
Validator.prototype.initObject = function (options, parent) {
    var defaults = {
        criteria: null,
        errorMessage: 'the validation has failed'
    };
    $.extend(true, defaults, options);
    this.setCriteria(defaults.criteria)
        .setParent(parent)
        .setErrorMessage(defaults.errorMessage);
};

/**
 * Sets the validation error message to show in case of the validation fails
 * @param {String} errorMessage
 * @return {*}
 */
Validator.prototype.setErrorMessage = function (errorMessage) {
    this.errorMessage = errorMessage;
    return this;
};

/**
 * GSets the validation error message to show in case of the validation fails
 * @param {String} errorMessage
 * @return {*}
 */
Validator.prototype.getErrorMessage = function () {
    return this.errorMessage;
};

/**
 * Sets the validation criteria
 * @param {Object} criteria
 * @return {*}
 */
Validator.prototype.setCriteria = function (criteria) {
    this.criteria = criteria;
    return this;
};

/**
 * Sets the parent field
 * @param {Field} parent
 * @return {*}
 */
Validator.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

/**
 * Evaluates the validator
 */
Validator.prototype.validate = function () {
    this.valid = true;
};

/**
 * Returns the validation response
 * @return {*}
 */
Validator.prototype.isValid = function () {
    this.validate();
    this.updateTooltip();
    return this.valid;
};

Validator.prototype.updateTooltip = function () {
    if (this.parent && this.parent.errorTooltip) {
        if (this.valid) {
            $(this.parent.errorTooltip.html)
                .removeClass('adam-tooltip-error-on')
                .addClass('adam-tooltip-error-off');
        } else {
            this.parent.errorTooltip.setMessage(this.errorMessage);
            $(this.parent.errorTooltip.html)
                .removeClass('adam-tooltip-error-off')
                .addClass('adam-tooltip-error-on');
        }
    }
};

var RegExpValidator = function (options, parent) {
    Validator.call(this, options, parent);
    RegExpValidator.prototype.initObject.call(this, options);
};

RegExpValidator.prototype = new Validator();

RegExpValidator.prototype.type = "RegExpValidator";

RegExpValidator.prototype.initObject = function (options) {
    var defaults = {
        errorMessage: "The text pattern doesn't match"
    };

    $.extend(true, defaults, options);

    this.setErrorMessage(defaults.errorMessage);
};

RegExpValidator.prototype.validate = function () {
    var res = false;
    if (this.criteria instanceof RegExp && this.parent && this.parent.value) {
        this.valid = this.criteria.test(this.parent.value);
    } else {
        this.valid = false;
    }
};

var TextLengthValidator = function (options, parent) {
    Validator.call(this, options, parent);
    TextLengthValidator.prototype.initObject(this, options);
};

TextLengthValidator.prototype = new Validator();

TextLengthValidator.prototype.type = 'TextLengthValidator';

TextLengthValidator.prototype.initObject = function (options) {
    var defaults = {
        errorMessage: "The text length doesn't match with the specified one"
    };

    $.extend(true, defaults, options);

    this.setErrorMessage(defaults.errorMessage);
};

TextLengthValidator.prototype.validate = function () {
    var res = false,
        value = this.criteria.trim ? $.trim(this.parent.value) : this.parent.value;

    this.valid = true;

    if (this.criteria.maxLength) {
        this.valid = value.length <= parseInt(this.criteria.maxLength, 10);
    }
    if (this.criteria.minLength) {
        this.valid = (this.valid !== null ? this.valid : true) && value.length >= parseInt(this.criteria.minLength, 10);
    }
};

var CustomValidator = function (options, parent) {
    Validator.call(this, options, parent);
};

CustomValidator.prototype = new Validator();

CustomValidator.prototype.type = "CustomValidator";

CustomValidator.prototype.validate = function () {
    if (typeof this.criteria.validationFunction === 'function') {
        this.valid = this.criteria.validationFunction.call(this.parent, this.parent.parent);
    }
    if (typeof this.valid === 'undefined' || this.valid === null) {
        this.valid = false;
    }
};

var NumberValidator = function (options, parent) {
    Validator.call(this, options, parent);

    NumberValidator.prototype.initObject.call(this, options);
};

NumberValidator.prototype = new Validator();

NumberValidator.prototype.initObject = function (options) {
    var defaults = {
        criteria: {
            decimalSeparator: ".",
            errorMessage: 'The value must be a number'
        }
    };
    $.extend(true, defaults, options);

    this.setDecimalSeparator(defaults.criteria.decimalSeparator)
        .setErrorMessage(defaults.errorMessage);
};

NumberValidator.prototype.setDecimalSeparator = function (separator) {
    this.criteria.decimalSeparator = separator;
};

NumberValidator.prototype.validate = function () {
    var evaluate, n, aux,
        intValid = false,
        decValid = false,
        i, r, c,
        milesSeparator;
    this.valid = false;
    if (this.parent && this.parent.value) {
        evaluate = this.parent.value.replace(/\./g, "");
        evaluate = evaluate.replace(/,/g, "");
        if (! /^\s*\d+\s*$/.test(evaluate)) {
            return;
        }

        if (this.criteria.decimalSeparator !== '.' && this.criteria.decimalSeparator !== ',') {
            return;
        }

        milesSeparator = this.criteria.decimalSeparator === ',' ? '.' : ',';

        r = new RegExp("\\" + milesSeparator, "g"); //generates a regular expression equivalent to /\./g
        //split the string into integer part and decimal part
        n = this.parent.value.split(this.criteria.decimalSeparator);
        //checks if there's at most one decimal separator
        aux = this.parent.value.match(new RegExp("\\" + this.criteria.decimalSeparator, 'g'));
        if (aux && aux.length > 1) {
            return;
        }
        //checks if the integer part (witouth miles separator) is composed only by digits
        if (!/^\s*\d+\s*$/.test(n[0].replace(new RegExp('\\' + milesSeparator, 'g'), ""))) {
            return;
        }
        //checks if the integer part has at least one miles separator, if it is 
        //check the number of them is the correct
        if (n[0].match(r) && n[0].match(r).length !== 0) {
            if (n[0].charAt(0) === '0') {
                return;
            }
            aux = Math.floor(n[0].length / 4);
            aux -= (n[0].length % 4) ? 0 : 1; //the number of separators
            if (n[0].match(r).length !== aux) {
                return;
            }
            i = n[0].length - 4;
            c = 0;
            while (i > 0) {
                if (n[0].charAt(i) === milesSeparator) {
                    c += 1;
                }
                i -= 4;
            }
            if (c != aux) {
                return;
            }
            intValid = true;
        }

        if (n[1]) {
            if (!/^\s*\d+\s*$/.test(n[1])) {
                return;
            }
        }
        this.valid = true;
    }
};

var ComparisonValidator = function (options, parent) {
    Validator.call(this, options, parent);
    ComparisonValidator.prototype.initObject(this, options);
};

ComparisonValidator.prototype = new Validator();

ComparisonValidator.prototype.type = "ComparisonValidator";

ComparisonValidator.prototype.initObject = function (options) {
    var defaults = {
        errorMessage: "The comparison failed"
    };

    $.extend(true, defaults, options);

    this.setErrorMessage(defaults.errorMessage);
};

ComparisonValidator.prototype.validate = function () {
    var evaluate, i, operators = {
        '==': function (a, b) {
            return a === b;
        },
        '>': function (a, b) {
            return a > b;
        },
        '>=': function (a, b) {
            return a >= b;
        },
        '<': function (a, b) {
            return a < b;
        },
        '<=': function (a, b) {
            return a <= b;
        }
    }, fields = this.parent.parent.items.slice(0), currentField, j;
    this.valid = false;
    if (!operators[this.criteria.operator]) {
        return;
    }
    switch (this.criteria.compare) {
    case 'textLength':
        evaluate = this.parent.value.length;
        for (i = 0; i < this.criteria.compareWith.length; i += 1) {
            for (j = 0; j < fields.length; j += 1) {
                currentField = fields.shift();
                if (currentField.name === this.criteria.compareWith[j]) {
                    break;
                }
            }
            if (!operators[this.criteria.operator](evaluate, currentField.value.length)) {
                return;
            }
        }
        break;
    case 'numeric':
        if (isNaN(this.parent.value.replace(/,/g, ""))) {
            return;
        }
        evaluate = parseFloat(this.parent.value.replace(/,/g, ""));
        for (i = 0; i < this.criteria.compareWith.length; i += 1) {
            for (j = 0; j < fields.length; j += 1) {
                currentField = fields.shift();
                if (currentField.name === this.criteria.compareWith[j]) {
                    break;
                }
            }
            if (isNaN(currentField.value.replace(/,/g, ""))) {
                return;
            }
            if (!operators[this.criteria.operator](evaluate, parseFloat(currentField.value.replace(/,/g, "")))) {
                return;
            }
        }
        break;
    default: //string
        evaluate = this.parent.value;
        for (i = 0; i < this.criteria.compareWith.length; i += 1) {
            for (j = 0; j < fields.length; j += 1) {
                currentField = fields.shift();
                if (currentField.name === this.criteria.compareWith[j]) {
                    break;
                }
            }
            if (!operators[this.criteria.operator](evaluate, currentField.value)) {
                return;
            }
        }
    }
    this.valid = true;
};

var RangeValidator = function (options, parent) {
    Validator.call(this, options, parent);
    RangeValidator.prototype.initObject.call(this, options);
};

RangeValidator.prototype = new Validator();

RangeValidator.prototype.initObject = function (options) {
    var defaults = {
        criteria: {
            type: "string",
            dateFormat: "yyyy-mm-dd"
        },
        errorMessage: "the value is out of ranges"
    };

    $.extend(true, defaults, options);

    this.setCriteria(defaults.criteria)
        .setErrorMessage(defaults.errorMessage);
};

RangeValidator.prototype.validate = function () {
    var that = this,
        options = [
            "minValue",
            "maxValue"
        ],
        parser = {
            string: function (val) {
                return val.toString();
            },
            numeric: function (val) {
                if (isNaN(val)) {
                    return NaN;
                }
                return parseFloat(val);
            },
            date: function (val) {
                var i, date, aux = {}, dateParts = {}, length,
                    indexes = ["yyyy", "mm", "dd", "hh", "ii", "ss"];
                if (typeof val === 'object') {
                    date = new Date(
                        val.year,
                        val.month - 1,
                        val.day,
                        val.hours || 0,
                        val.minutes || 0,
                        val.seconds || 0,
                        val.milliseconds || 0
                    );
                } else if (typeof val === 'string') {
                    that.criteria.dateFormat = $.trim(that.criteria.dateFormat);
                    /*if(that.criteria.dateFormat.length !== val.length) {
                        return null;
                    }*/
                    for (i = 0; i < indexes.length; i += 1) {
                        aux[indexes[i]] = that.criteria.dateFormat.toLowerCase().indexOf(indexes[i]);
                        switch (indexes[i]) {
                        case 'yyyy':
                        case 'mm':
                        case 'dd':
                            dateParts[indexes[i]] = aux[indexes[i]] >= 0 ? val.substr(aux[indexes[i]], indexes[i].length) : "x";
                            break;
                        default:
                            dateParts[indexes[i]] = (aux[indexes[i]] >= 0 ? val.substr(aux[indexes[i]], 2) : 0) || 0;
                        }

                        if (isNaN(dateParts[indexes[i]]) || !/^\s*\d+\s*$/.test(dateParts[indexes[i]])) {
                            return null;
                        } else {
                            dateParts[indexes[i]] = parseInt(dateParts[indexes[i]], 10);
                        }
                    }

                    if (dateParts.mm <= 0 && dateParts.dd <= 0) {
                        return null;
                    }
                    switch (dateParts.mm) {
                    case 4:
                    case 6:
                    case 9:
                    case 11:
                        if (dateParts.dd > 30) {
                            return null;
                        }
                        break;
                    case 2:
                        if (((dateParts.yyyy % 4 === 0 && dateParts.yyyy % 100 !== 0) || (dateParts.yyyy % 400 === 0))
                                && dateParts.dd > 29) {
                            return null;
                        } else {
                            if (dateParts.dd > 28) {
                                return null;
                            }
                        }
                        break;
                    default:
                        if (dateParts.dd > 31) {
                            return null;
                        }
                        break;
                    }

                    date = new Date(
                        dateParts.yyyy,
                        dateParts.mm > 0 && dateParts.mm < 13 ? dateParts.mm - 1 : "x",
                        dateParts.dd,
                        dateParts.hh >= 0 && dateParts.hh < 24 ? dateParts.hh : "x",
                        dateParts.ii >= 0 && dateParts.ii < 60 ? dateParts.ii : "x",
                        dateParts.ss >= 0 && dateParts.ss < 60 ? dateParts.ss : "x"
                    );

                } else {
                    return null;
                }
                if (Object.prototype.toString.call(date) !== "[object Date]") {
                    return null;
                }
                return !isNaN(date.getTime()) ? date : null;
            }
        },
        i,
        parsedValues = {};

    for (i = 0; i < options.length; i += 1) {
        if (this.criteria[options[i]]) {
            parsedValues[options[i]] = parser[this.criteria.type.toLowerCase()](this.criteria[options[i]]);
        }
    }

    if (!(this.criteria.minValue || this.criteria.maxValue)) {
        this.valid = false;
    } else {
        this.valid = true;
        if (parsedValues.maxValue) {
            this.valid = parser[this.criteria.type.toLowerCase()](this.parent.value) <= parsedValues.maxValue;
        }

        if (parsedValues.minValue) {
            this.valid = this.valid && parser[this.criteria.type.toLowerCase()](this.parent.value) >= parsedValues.minValue;
        }
    }
};

/*global Field, $, document, Element*/
/**
 * @class TextField
 * Handle text input fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var TextField = function (options, parent) {
    Field.call(this, options, parent);
    /**
     * Defines the maximum number of characters supported
     * @type {Number}
     */
    this.maxCharacters = null;
    TextField.prototype.initObject.call(this, options);
};
TextField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
TextField.prototype.type = 'TextField';

/**
 * Initializes the object with the default values
 * @param {Object} options
 */
TextField.prototype.initObject = function (options) {
    var defaults = {
        maxCharacters: 0
    };
    $.extend(true, defaults, options);
    this.setMaxCharacters(defaults.maxCharacters);
};

/**
 * Sets the maximun characters property
 * @param {Number} value
 * @return {*}
 */
TextField.prototype.setMaxCharacters = function (value) {
    this.maxCharacters = value;
    return this;
};

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
TextField.prototype.createHTML = function () {
    var fieldLabel, textInput, required = '', readAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
    this.html.appendChild(fieldLabel);

    textInput = this.createHTMLElement('input');
    textInput.type = "text";
    textInput.id = this.name;
    textInput.value = this.value || "";
    if (this.fieldWidth) {
        textInput.style.width = this.fieldWidth;
    }
    if (this.readOnly) {
        readAtt = document.createAttribute('readonly');
        textInput.setAttributeNode(readAtt);
    }
    this.html.appendChild(textInput);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }
    this.labelObject = fieldLabel;
    this.controlObject = textInput;

    return this.html;
};

/**
 * Attaches event listeners to the text field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link TextField#event-change Change Input field event}
 * - {@link TextField#event-keydown key down event into an input field}
 *
 * @chainable
 */
TextField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        $(this.controlObject)
            .change(function () {
                self.setValue(this.value, true);
                self.onChange();
            })
            .keydown(function (e) {
                e.stopPropagation();
            });
    }
    return this;
};

TextField.prototype.disable = function () {
    this.labelObject.className = 'adam-form-label-disabled';
    this.controlObject.disabled = true;
    if (!this.oldRequiredValue) {
        this.oldRequiredValue = this.required;
    }
    this.setRequired(false);
    $(this.controlObject).removeClass('required');
};
TextField.prototype.enable = function () {
    this.labelObject.className = 'adam-form-label';
    this.controlObject.disabled = false;
    if (this.oldRequiredValue) {
        this.setRequired(this.oldRequiredValue);
    }
};
//

/**
 * @class ComboboxField
 * Handles drop down fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var ComboboxField = function (options, parent) {
    Field.call(this, options, parent);
    /**
     * Defines the combobox options
     * @type {Array}
     */
    this.options = [];
    this.related = null;
    ComboboxField.prototype.initObject.call(this, options);
};
ComboboxField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
ComboboxField.prototype.type = 'ComboboxField';

/**
 * Initializes the object with default values
 * @param {Object} options
 */
ComboboxField.prototype.initObject = function (options) {
    var defaults = {
        options: [],
        related: null
    };
    $.extend(true, defaults, options);
    this.setOptions(defaults.options)
        .setRelated(defaults.related);
};

/**
 * Sets the combo box options
 * @param {Array} data
 * @return {*}
 */
ComboboxField.prototype.setOptions = function (data) {
    var i;
    this.options = data;
    if (this.html) {
        for (i = 0; i < this.options.length; i += 1) {
            this.controlObject.appendChild(this.generateOption(this.options[i]));
        }

        if (!this.value) {
            this.value = this.controlObject.value;
        }
    }
    return this;
};

ComboboxField.prototype.setRelated = function (data) {
    this.related = data;
    return this;
};

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
ComboboxField.prototype.createHTML = function () {
    var fieldLabel, selectInput, required = '', opt, i, disableAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
    this.html.appendChild(fieldLabel);

    selectInput = this.createHTMLElement('select');
    selectInput.id = this.name;
    for (i = 0; i < this.options.length; i += 1) {
        selectInput.appendChild(this.generateOption(this.options[i]));
    }
    if (!this.value) {
        this.value = selectInput.value;
    }
    if (this.fieldWidth) {
        selectInput.style.width = this.fieldWidth;
    }
    if (this.readOnly) {
        disableAtt = document.createAttribute('disabled');
        selectInput.setAttributeNode(disableAtt);
    }
    this.html.appendChild(selectInput);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }
    this.labelObject = fieldLabel;
    this.controlObject = selectInput;

    return this.html;
};

ComboboxField.prototype.removeOptions = function () {
    if (this.options) {
        while (this.controlObject.firstChild) {
            this.controlObject.removeChild(this.controlObject.firstChild);
        }
        this.options = [];
    }
    return this;
};


ComboboxField.prototype.generateOption = function (item) {
    var out, selected = '', value, text;
    out = this.createHTMLElement('option');
    if (typeof item === 'object') {
        value = item.value;
        text = item.text;
    } else {
        value = item;
    }
    out.selected = this.value === value;
    out.value = value;
    out.label = text || value;
    out.appendChild(document.createTextNode(text || value));
    return out;
};

/**
 * Attaches event listeners to the combo box field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link TextField#event-change Change Input field event}
 *
 * @chainable
 */
ComboboxField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        $(this.controlObject)
            .change(function (e) {
                var oldValue = self.value;
                self.setValue(this.value, true);
                self.onChange(this.value, oldValue);
            });
    }
    return this;
};

ComboboxField.prototype.disable = function () {
    this.labelObject.className = 'adam-form-label-disabled';
    this.controlObject.disabled = true;
    if (!this.oldRequiredValue) {
        this.oldRequiredValue = this.required;
    }
    this.setRequired(false);
    $(this.controlObject).removeClass('required');
};

ComboboxField.prototype.enable = function () {
    this.labelObject.className = 'adam-form-label';
    this.controlObject.disabled = false;
    if (this.oldRequiredValue) {
        this.setRequired(this.oldRequiredValue);
    }
};
//

/**
 * @class TextareaField
 * Handles TextArea fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var TextareaField = function (options, parent) {
    Field.call(this, options, parent);
    this.fieldHeight = null;
    TextareaField.prototype.initObject.call(this, options);
};
TextareaField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
TextareaField.prototype.type = "TextareaField";

TextareaField.prototype.initObject = function (options) {
    var defaults = {
        fieldHeight: null
    };
    $.extend(true, defaults, options);
    this.setFieldHeight(defaults.fieldHeight);
};

TextareaField.prototype.setFieldHeight = function (height) {
    this.fieldHeight = height;
    return this;
};

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
TextareaField.prototype.createHTML = function () {
    var fieldLabel, textInput, required = '', readAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
    fieldLabel.style.verticalAlign = 'top';
    this.html.appendChild(fieldLabel);

    textInput = this.createHTMLElement('textarea');
    textInput.id = this.name;
    textInput.value = this.value;
    if (this.fieldWidth) {
        textInput.style.width = this.fieldWidth;
    }
    if (this.fieldHeight) {
        textInput.style.height = this.fieldHeight;
    }
    if (this.readOnly) {
        readAtt = document.createAttribute('readonly');
        textInput.setAttributeNode(readAtt);
    }
    this.html.appendChild(textInput);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }

    this.controlObject = textInput;

    return this.html;
};

/**
 * Attaches event listeners to the text area , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link TextareaField#event-change Change Input field event}
 * - {@link TextareaField#event-keydown key down event into an input field}
 *
 * @chainable
 */

TextareaField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        $(this.controlObject)
            .change(function () {
                self.setValue(this.value, true);
                self.onChange();
            })
            .keydown(function (e) {
                e.stopPropagation();
            });
    }
    return this;
};
//

/**
 * @class CheckboxField
 * Handles the checkbox fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var CheckboxField = function (options, parent) {
    Field.call(this, options, parent);
    this.defaults = {
        //options: {},
        onClick: function (e, ui) {}
    };
    $.extend(true, this.defaults, options);
};

CheckboxField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
CheckboxField.prototype.type = 'CheckboxField';

/**
 * Creates the HTML Element of the field
 */
CheckboxField.prototype.createHTML = function () {
    var fieldLabel, textInput, required = '', readAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
//    fieldLabel.style.verticalAlign = 'top';
    this.html.appendChild(fieldLabel);

    textInput = this.createHTMLElement('input');
    textInput.id = this.name;
    textInput.type = 'checkbox';
    if (this.value) {
        textInput.checked = true;
    } else {
        textInput.checked = false;
    }
    if (this.readOnly) {
        readAtt = document.createAttribute('readonly');
        textInput.setAttributeNode(readAtt);
    }
    this.html.appendChild(textInput);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }
    this.labelObject = fieldLabel;
    this.controlObject = textInput;

    return this.html;
};

/**
 * Attaches event listeners to checkbox field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link CheckboxField#event-onClick on click mouse event}
 * - {@link CheckboxField#event-change Change Input field event}
 * - {@link CheckboxField#event-keydown key down event into an input field}
 *
 * @chainable
 */
CheckboxField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        if (typeof this.defaults.onClick !== 'undefined' && typeof this.defaults.onClick === 'function') {
            $(this.controlObject).on('click', function (e, ui) {return self.defaults.onClick(); });
        }

        $(this.controlObject)
            .change(function (a, b) {
                var val;
                if (this.checked) {
                    val = true;
                } else {
                    val = false;
                }
                self.setValue(val, true);
                self.onChange();
            });
    }
    return this;
};

CheckboxField.prototype.getObjectValue = function () {
    var response = {};
    if (this.value) {
        response[this.name] = true;
    } else {
        response[this.name] = false;
    }
    return response;
};

CheckboxField.prototype.evalRequired = function () {
    var response = true;
    if (this.required) {
        response = this.value;
        if (!response) {
            $(this.controlObject).addClass('required');
        } else {
            $(this.controlObject).removeClass('required');
        }
    }
    return response;
};
CheckboxField.prototype.disable = function () {
    this.labelObject.className = 'adam-form-label-disabled';
    this.controlObject.disabled = true;
    if (!this.oldRequiredValue) {
        this.oldRequiredValue = this.required;
    }
    this.setRequired(false);
    $(this.controlObject).removeClass('required');

};
CheckboxField.prototype.enable = function () {
    this.labelObject.className = 'adam-form-label';
    this.controlObject.disabled = false;
    if (this.oldRequiredValue) {
        this.setRequired(this.oldRequiredValue);
    }
};
/**
 * @class RadiobuttonField
 * Handles the radio button fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var RadiobuttonField = function (options, parent) {
    Field.call(this, options, parent);
    this.defaults = {
        options: {},
        onClick: function (e, ui) {}
    };
    $.extend(true, this.defaults, options);
    //RadiobuttonField.prototype.initObject.call(this, options);
};
RadiobuttonField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
RadiobuttonField.prototype.type = 'RadiobuttonField';

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
RadiobuttonField.prototype.createHTML = function () {
    var fieldLabel, textInput, required = '', readAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }
//    console.log(this.defaults);
    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';

    textInput = this.createHTMLElement('input');
    textInput.name = this.name;
    textInput.type = 'radio';
    textInput.value = this.value;

    if (typeof (this.defaults.labelAlign) === 'undefined' ||
            this.defaults.labelAlign === 'left') {
        fieldLabel.style.width = this.parent.labelWidth;
        fieldLabel.innerHTML = this.label + ': ' + required;
        fieldLabel.style.verticalAlign = 'top';
        fieldLabel.style.width = this.parent.labelWidth;
        this.html.appendChild(fieldLabel);
        this.html.appendChild(textInput);
    } else if (this.defaults.labelAlign === 'right') {
        fieldLabel.innerHTML = '&nbsp;' + this.label + required;
        textInput.style.marginLeft = (this.defaults.marginLeft) ? this.defaults.marginLeft + 'px' : '0px';
        fieldLabel.style.width = this.parent.labelWidth;
        this.html.appendChild(textInput);
        this.html.appendChild(fieldLabel);
    }

    if (this.value) {
        textInput.checked = true;
    } else {
        textInput.checked = false;
    }

    if (this.readOnly) {
        readAtt = document.createAttribute('readonly');
        textInput.setAttributeNode(readAtt);
    }

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }

    this.controlObject = textInput;

    return this.html;
};

/**
 * Attaches event listeners to radio field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link RadiobuttonField#event-onClick on click mouse event}
 * - {@link RadiobuttonField#event-change Change Input field event}
 *
 * @chainable
 */
RadiobuttonField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        if (typeof this.defaults.onClick !== 'undefined' && typeof this.defaults.onClick === 'function') {
            $(this.controlObject).on('click', function (e, ui) {return self.defaults.onClick(); });
        }
        $(this.controlObject)
            .change(function (a, b) {
                self.onChange();
            });
//        $(this.controlObject)
//            .change(function (a, b) {
//                var val;
//                if (this.checked) {
//                    val = true;
//                } else {
//                    val = false;
//                }
//                self.setValue(val, true);
//                self.onChange();
//            });
    }
    return this;
};

RadiobuttonField.prototype.getObjectValue = function () {
    return this.value;
};

RadiobuttonField.prototype.evalRequired = function () {
    var response = true;
    if (this.required) {
        response = this.value;
        if (!response) {
            $(this.controlObject).addClass('required');
        } else {
            $(this.controlObject).removeClass('required');
        }
    }
    return response;
};

RadiobuttonField.prototype._setValueToControl = function (value) {
    if (this.html && this.controlObject) {
        this.controlObject.checked = this.value;
    }
    return this;
};

/**
 * @class LabelField
 * Handles the Label fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var LabelField = function (options, parent) {
    Field.call(this, options, parent);
    this.submit = false;
    this.defaults = {
        options: {
            marginLeft : 10
        }
    };
    $.extend(true, this.defaults, options);
};
LabelField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
LabelField.prototype.type = 'LabelField';

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
LabelField.prototype.createHTML = function () {
    var fieldLabel;
    Field.prototype.createHTML.call(this);

    fieldLabel = this.createHTMLElement('span');
//    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ':';
    fieldLabel.style.verticalAlign = 'top';
    fieldLabel.style.marginLeft = this.defaults.options.marginLeft + 'px';
    this.html.appendChild(fieldLabel);

    return this.html;
};

/**
 * @class HiddenField
 * Handle the hidden fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var HiddenField = function (options, parent) {
    Field.call(this, options, parent);
};
HiddenField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
HiddenField.prototype.type = 'HiddenField';

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
HiddenField.prototype.createHTML = function () {
    Element.prototype.createHTML.call(this);
    return this.html;
};

//

var EmailGroupField = function (options, parent) {
    Field.call(this, options, parent);
};

EmailGroupField.prototype = new Field();

EmailGroupField.prototype.type = 'EmailGroupField';

/**
 * @class DateField
 * Handle text input fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var DateField = function (options, parent) {
    Field.call(this, options, parent);
    /**
     * Defines the maximum number of characters supported
     * @type {Number}
     */
    this.maxCharacters = null;
    DateField.prototype.initObject.call(this, options);
};
DateField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
DateField.prototype.type = 'TextField';

/**
 * Initializes the object with the default values
 * @param {Object} options
 */
DateField.prototype.initObject = function (options) {
    var defaults = {
        maxCharacters: 0
    };
    $.extend(true, defaults, options);
    this.setMaxCharacters(defaults.maxCharacters);
};

/**
 * Sets the maximun characters property
 * @param {Number} value
 * @return {*}
 */
DateField.prototype.setMaxCharacters = function (value) {
    this.maxCharacters = value;
    return this;
};

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
DateField.prototype.createHTML = function () {
    var fieldLabel, textInput, required = '', readAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
    this.html.appendChild(fieldLabel);

    textInput = this.createHTMLElement('input');
    textInput.id = this.name;
    textInput.value = this.value || "";
    $(textInput).datepicker();
    if (this.fieldWidth) {
        textInput.style.width = this.fieldWidth;
    }
    if (this.readOnly) {
        readAtt = document.createAttribute('readonly');
        textInput.setAttributeNode(readAtt);
    }
    this.html.appendChild(textInput);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }
    this.labelObject = fieldLabel;
    this.controlObject = textInput;
    return this.html;
};

/**
 * Attaches event listeners to date field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link TextareaField#event-change Change Input field event}
 * - {@link TextareaField#event-keydown key down event into an input field}
 *
 * @chainable
 */
DateField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        $(this.controlObject)
            .change(function () {
                self.setValue(this.value, true);
                self.onChange();
            })
            .keydown(function (e) {
                e.stopPropagation();
            });
    }
    return this;
};
DateField.prototype.disable = function () {
    this.labelObject.className = 'adam-form-label-disabled';
    this.controlObject.disabled = true;
    if (!this.oldRequiredValue) {
        this.oldRequiredValue = this.required;
    }
    this.setRequired(false);
    $(this.controlObject).removeClass('required');
    $(this.controlObject).datepicker('hide');
};
DateField.prototype.enable = function () {
    this.labelObject.className = 'adam-form-label';
    this.controlObject.disabled = false;
    if (this.oldRequiredValue) {
        this.setRequired(this.oldRequiredValue);
    }
};

/**
 * @class NumberField
 * Handle text input fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var NumberField = function (options, parent) {
    Field.call(this, options, parent);
    /**
     * Defines the maximum number of characters supported
     * @type {Number}
     */
    this.maxCharacters = null;
    NumberField.prototype.initObject.call(this, options);
};
NumberField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
NumberField.prototype.type = 'TextField';

/**
 * Initializes the object with the default values
 * @param {Object} options
 */
NumberField.prototype.initObject = function (options) {
    var defaults = {
        maxCharacters: 0
    };
    $.extend(true, defaults, options);
    this.setMaxCharacters(defaults.maxCharacters);
};

/**
 * Sets the maximun characters property
 * @param {Number} value
 * @return {*}
 */
NumberField.prototype.setMaxCharacters = function (value) {
    this.maxCharacters = value;
    return this;
};

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
NumberField.prototype.createHTML = function () {
    var fieldLabel, textInput, required = '', readAtt;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
    this.html.appendChild(fieldLabel);

    textInput = this.createHTMLElement('input');
    textInput.id = this.name;
    textInput.value = this.value || "";
    if (this.fieldWidth) {
        textInput.style.width = this.fieldWidth;
    }
    if (this.readOnly) {
        readAtt = document.createAttribute('readonly');
        textInput.setAttributeNode(readAtt);
    }
    this.html.appendChild(textInput);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }
    this.labelObject = fieldLabel;
    this.controlObject = textInput;

    return this.html;
};

/**
 * Attaches event listeners to the text field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link TextField#event-change Change Input field event}
 * - {@link TextField#event-keydown key down event into an input field}
 *
 * @chainable
 */
NumberField.prototype.attachListeners = function () {
    var self = this;
    if (this.controlObject) {
        $(this.controlObject)
            .change(function () {
                self.setValue(this.value, true);
                self.onChange();
            })
            .keydown(function (e) {
                e.stopPropagation();
            });
    }

    $(this.controlObject).on('keydown', function (event) {
        event.stopPropagation();
        // Allow: backspace, delete, tab, escape, and enter
        if (event.keyCode === 46 || event.keyCode === 8 || event.keyCode === 9 || event.keyCode === 27 || event.keyCode === 13 ||
            // Allow: Ctrl+A
            (event.keyCode === 65 && event.ctrlKey === true) ||
            // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
            // let it happen, don't do anything

            return;
        } else {
            // Ensure that it is a number and stop the keypress
            if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault();
            }
        }
    }).on('keyup', function (e) {
        e.stopPropagation();
    });
    return this;
};

NumberField.prototype.disable = function () {
    this.labelObject.className = 'adam-form-label-disabled';
    this.controlObject.disabled = true;
    if (!this.oldRequiredValue) {
        this.oldRequiredValue = this.required;
    }
    this.setRequired(false);
    $(this.controlObject).removeClass('required');
};
NumberField.prototype.enable = function () {
    this.labelObject.className = 'adam-form-label';
    this.controlObject.disabled = false;
    if (this.oldRequiredValue) {
        this.setRequired(this.oldRequiredValue);
    }
};




/**
 * @class CheckboxGroup
 * Handles the checkbox fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var CheckboxGroup = function (options, parent) {
    Field.call(this, options, parent);
//    this.defaults = {
//        options: {},
//        onClick: function (e, ui) {}
//    };
//    $.extend(true, this.defaults, options);
    this.controlObject = {};
    CheckboxGroup.prototype.initObject.call(this, options);
};

CheckboxGroup.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
CheckboxGroup.prototype.type = 'CheckboxGroup';

/**
 * Initializes the object with the default values
 * @param {Object} options
 */
CheckboxGroup.prototype.initObject = function (options) {
    var defaults = {
        items: []
    };
    $.extend(true, defaults, options);
    //this.setMaxCharacters(defaults.maxCharacters);
    this.items = defaults.items;
};

/**
 * Creates the HTML Element of the field
 */
CheckboxGroup.prototype.createHTML = function () {
    var fieldLabel, input, required = '', readAtt, div, i, text, ul, li, root = this, object;
    //this.controlObject.control = [];
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }
    div = this.createHTMLElement('div');
    div.style.display = 'inline-block';
    div.style.width = "30%";
    div.style.verticalAlign = 'top';
    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required + '&nbsp;&nbsp;&nbsp;&nbsp;';
    fieldLabel.style.width = this.parent.labelWidth;
//    fieldLabel.style.verticalAlign = 'top';
    div.appendChild(fieldLabel);
    this.html.appendChild(div);


    div = this.createHTMLElement('div');
    div.style.display = 'inline-block';
    div.style.width = "40%";
    ul =  this.createHTMLElement('ul');

    for (i = 0; i < this.items.length; i += 1) {
        li = this.createHTMLElement('li');
        li.style.listStyleType = 'none';
        input = this.createHTMLElement('input');
        input.id = this.items[i].value;
        input.type = 'checkbox';
        if (this.items[i].checked) {
            input.checked = true;
        } else {
            input.checked = false;
        }
        if (this.readOnly) {
            readAtt = document.createAttribute('readonly');
            input.setAttributeNode(readAtt);
        }
        li.appendChild(input);

        object = {'control': input};
        if (this.items[i].checked) {
            object.checked = true;
        }
        this.controlObject[this.items[i].value] = object;
//        <label for="male">Male</label>
        text = document.createElement("Label");
        text.innerHTML = ' &nbsp;&nbsp;' + this.items[i].text;
        li.appendChild(text);

        ul.appendChild(li);

        $(input).change(function () {
            if (this.checked) {
                //control.checked = true;
                root.controlObject[$(this).attr('id')].checked = true;
            } else {
                //control.checked = false;
                root.controlObject[$(this).attr('id')].checked = false;
            }
        });
    }
    div.appendChild(ul);
    this.html.appendChild(div);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }
    this.labelObject = fieldLabel;
    //

    return this.html;
};

/**
 * Attaches event listeners to checkbox field , it also call some methods to set and evaluate
 * the current value (to send it to the database later).
 *
 * The events attached to this field are:
 *
 * - {@link CheckboxField#event-onClick on click mouse event}
 * - {@link CheckboxField#event-change Change Input field event}
 * - {@link CheckboxField#event-keydown key down event into an input field}
 *
 * @chainable
 */
CheckboxGroup.prototype.attachListeners = function () {
    var self = this, i, control;
//    if (this.controlObject) {
//        if (typeof this.defaults.onClick !== 'undefined' && typeof this.defaults.onClick === 'function') {
//            $(this.controlObject).on('click', function (e, ui) {return self.defaults.onClick(); });
//        }
//
//        $(this.controlObject)
//            .change(function (a, b) {
//                var val;
//                if (this.checked) {
//                    val = true;
//                } else {
//                    val = false;
//                }
//                self.setValue(val, true);
//                self.onChange();
//            });
//    }
//    for (i = 0; i < this.controlObject.length; i += 1) {
//    }
    return this;
};

CheckboxGroup.prototype.getObjectValue = function () {
    var response = {}, i, control, array = [];
    $.each(this.controlObject, function (key, value) {
        //console.log(key);
        if (value.checked) {
            array.push(key);
        }
    });

    response[this.name] = array;
    return response;
};

CheckboxGroup.prototype.evalRequired = function () { var response = true;
    if (this.required) {
        response = this.value;
        if (!response) {
            $(this.controlObject).addClass('required');
        } else {
            $(this.controlObject).removeClass('required');
        }
    }
    return response;
};
CheckboxGroup.prototype.disable = function () {
    this.labelObject.className = 'adam-form-label-disabled';
    this.controlObject.disabled = true;
    if (!this.oldRequiredValue) {
        this.oldRequiredValue = this.required;
    }
    this.setRequired(false);
    $(this.controlObject).removeClass('required');

};
CheckboxGroup.prototype.enable = function () {
    this.labelObject.className = 'adam-form-label';
    this.controlObject.disabled = false;
    if (this.oldRequiredValue) {
        this.setRequired(this.oldRequiredValue);
    }
};
/**
 * @class Button
 * Handles buttons
 * @extend Element
 *
 * @constructor
 * Create a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var Button = function (options, parent) {
    Element.call(this, options);
    this.parent = null;
    this.caption = null;
    this.action = null;
    this.icon = null;
    Button.prototype.initObject.call(this, options, parent);
};

Button.prototype = new Element();

Button.prototype.type = 'Button';
Button.prototype.family = 'Button';

Button.prototype.initObject = function (options, parent) {
    var defaults, self = this;
    if (options.isAction) {
        this.loadAction(options, parent);
    } else {
        defaults = {
            caption: null,
            parent: parent || null,
            jtype: 'normal',
            handler: function () {},
            icon: null
        };
        $.extend(true, defaults, options);
        this.setCaption(defaults.caption)
            .setParent(defaults.parent)
            .setIcon(defaults.icon);
        switch (defaults.jtype) {
        case 'reset':
            this.action = new Action({
                text: this.caption,
                handler: function () {
                    self.parent.reset();
                },
                cssStyle: this.icon
            });
            break;
        case 'submit':
            this.action = new Action({
                text: this.caption,
                handler: function () {
                    self.parent.submit();
                },
                cssStyle: this.icon
            });
            break;
        case 'normal':
            this.action = new Action({
                text: this.caption,
                handler: defaults.handler,
                cssStyle: this.icon
            });
            break;
        }
    }
};

Button.prototype.loadAction = function (action, parent) {
    this.action = action;
    this.setCaption(this.action.text);
    this.setIcon(this.action.cssStyle);
    this.setParent(parent);
};

Button.prototype.setCaption = function (text) {
    this.caption = text;
    return this;
};

Button.prototype.setIcon = function (value) {
    this.icon = value;
    return this;
};

Button.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

Button.prototype.createHTML = function () {
    var buttonAnchor, iconSpan, labelSpan;

    buttonAnchor = this.createHTMLElement('a');
    buttonAnchor.href = '#';
    buttonAnchor.className = 'adam-button';
    buttonAnchor.id = this.id;


    if (this.icon) {
        iconSpan = this.createHTMLElement('span');
        iconSpan.className = this.icon;
        buttonAnchor.appendChild(iconSpan);
    }

    labelSpan = this.createHTMLElement('span');
    labelSpan.className = 'adam-button-label';
    labelSpan.innerHTML = this.caption;
    buttonAnchor.appendChild(labelSpan);

    this.html = buttonAnchor;

    return this.html;
};

Button.prototype.attachListeners = function () {
    var self = this;
    $(this.html)
        .click(function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (self.action.handler) {
                self.action.handler();
            }
        })
        .mousedown(function (e) {
            e.stopPropagation();
        });
};

var RestProxy = function (options) {
    Proxy.call(this, options);
    this.restClient = null;
    this.getMethod = null;
    this.sendMethod = null;
    this.uid = null;
    RestProxy.prototype.initObject.call(this, options);
};

RestProxy.prototype = new Proxy();

RestProxy.prototype.type = 'RestProxy';

RestProxy.prototype.initObject = function (options) {
    var defaults = {
        restClient: null,
        sendMethod: 'PUT',
        getMethod: 'GET',
        uid: null
    };
    $.extend(true, defaults, options);
    this.setUid(defaults.uid)
        .setRestClient(defaults.restClient)
        .setSendMethod(defaults.sendMethod)
        .setGetMethod(defaults.getMethod);
};

RestProxy.prototype.setUid = function (id) {
    this.uid = id;
    return this;
};


RestProxy.prototype.setRestClient = function (restClient) {
    this.restClient = restClient;
    return this;
};

RestProxy.prototype.setSendMethod = function (method) {
    this.sendMethod = method;
    return this;
};

RestProxy.prototype.setGetMethod = function (method) {
    this.getMethod = method;
    return this;
};

RestProxy.prototype.getData = function (params) {
    var operation, self = this, resp;
    if (this.restClient) {
        operation = this.getOperation(this.getMethod);
        this.restClient.consume({
            operation: operation,
            url: this.url,
            id: this.uid,
            data: params,
            success: function (xhr, response) {
                status = response.success;
                if (response.success) {
                    resp = response;
                }
            }
        });
    }
    return resp;
};

RestProxy.prototype.sendData = function (data, callback) {
    var operation, self = this, send;
    if (this.restClient) {
        operation = this.getOperation(this.sendMethod);
        send = {
            operation: operation,
            url: this.url,
            id: this.uid,
            data: data
        };
        if (callback) {
            if (callback.success) {
                send.success = callback.success;
            }
            if (callback.failure) {
                send.failure = callback.failure;
            }
        }
        this.restClient.consume(send);
    }
};

RestProxy.prototype.getOperation = function (method) {
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

/*globals Field, $, document*/
var ItemMatrixField = function (options, parent) {
    Field.call(this, options, parent);
    this.moduleName = null;
    this.lockedFields = [];
    this.terminateFields = {};
    this.fieldWidth = null;
    this.fieldHeight = null;
    this.keyDelay = null;
    this.selectedHandler = null;
    this.searchValue = null;
    this.visualStyle = null;
    this.nColumns = null;
    ItemMatrixField.prototype.initObject.call(this, options);
};

ItemMatrixField.prototype = new Field();

ItemMatrixField.prototype.initObject = function (options) {
    var defaults = {
        visualStyle : 'list',
        nColumns : 2
    };
    $.extend(true, defaults, options);
//    this.setItems(defaults.items)
    this.setFieldWidth(defaults.fieldWidth)
        .setFieldHeight(defaults.fieldHeight)
        .setName(defaults.name)
        .setVisualStyle(defaults.visualStyle)
        .setNColumns(defaults.nColumns);
//        .setValueField(defaults.valueField);
};

ItemMatrixField.prototype.createHTML = function () {
    var fieldLabel, required = '', checkContainer, style;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = required + this.label + ':';
    fieldLabel.style.width = this.parent.labelWidth;
    fieldLabel.style.verticalAlign = 'top';
    this.html.appendChild(fieldLabel);

    if (this.visualStyle === 'list') {
        checkContainer = this.createHTMLElement('ul');
        checkContainer.className = 'adam-item-matrix';
    } else {
        checkContainer = this.createHTMLElement('div');
        checkContainer.className = 'adam-item-matrix table';
    }

    if (this.fieldWidth && this.fieldHeight) {
        style = document.createAttribute('style');
        if (this.fieldWidth) {
            style.value += 'width: ' + this.fieldWidth + 'px; ';
        }
        if (this.fieldHeight) {
            style.value += 'height: ' + this.fieldHeight + 'px; ';
        }
        style.value += 'display: inline-block; margin: 0; overflow: auto; padding: 3px;';

        checkContainer.setAttributeNode(style);
    }
    this.html.appendChild(checkContainer);

    this.controlObject = checkContainer;

    return this.html;
};

ItemMatrixField.prototype.attachListeners = function () {
    var self = this;
    $(this.controlObject).on('click', '.item-matrix-field', function () {
        if ($(this).is(":checked")) {
            self.addLockedFields($(this).attr('value'));
        } else {
            self.removeLockedFields($(this).attr('value'));
        }
    });
    $(this.controlObject).on('change', '.item-matrix-field', function () {
        self.parent.setDirty(true);
    });
};

/* **** SETTERS **** */
ItemMatrixField.prototype.setFieldHeight = function (height) {
    this.fieldHeight = height;
    return this;
};

ItemMatrixField.prototype.setFieldWidth = function (width) {
    this.fieldWidth = width;
    return this;
};

ItemMatrixField.prototype.setNColumns = function (nColumns) {
    this.nColumns = nColumns;
    return this;
};

ItemMatrixField.prototype.setNameModule = function (moduleName) {
    this.nameModule = moduleName;
    return this;
};

ItemMatrixField.prototype.setLockedFields = function (lockedFields) {
    if (typeof lockedFields === 'object' && (lockedFields instanceof Array)) {
        this.lockedFields = lockedFields;
    }
    return this;
};

ItemMatrixField.prototype.setVisualStyle = function (vStyle) {
    this.visualStyle = vStyle;
    return this;
};

ItemMatrixField.prototype.addLockedFields = function (fieldName) {
    this.lockedFields.push(fieldName);
    return this;
};

ItemMatrixField.prototype.removeLockedFields = function (fieldName) {
    var index = this.lockedFields.indexOf(fieldName);
    this.lockedFields.splice(index, 1);
    return this;
};
/**
 * Sets the combo box options
 * @param {Array} data
 * @return {*}
 */
ItemMatrixField.prototype.setList = function (data, selected) {
    var i, opt = '';
    if (this.html) {
        $(this.controlObject).empty();
        this.lockedFields = [];
        if (this.visualStyle === 'table') {
            opt += '<div class="row">';
        }
        for (i = 0; i < data.length; i += 1) {
            opt += this.generateOption(data[i], selected);
            if ((i + 1) % this.nColumns === 0) {
                opt += '</div><div class="row">';
            }
        }
        if (this.visualStyle === 'table') {
            opt += '</div></div>';
        }
        this.controlObject.innerHTML = opt;
    }
    return this;
};

ItemMatrixField.prototype.generateOption = function (item, selected) {
    var out = '', value, text, select;
    if (typeof item === 'object') {
        value = item.value;
        text = item.text;
    }
    if (typeof selected === 'object' && (selected instanceof Array)) {
        if (selected.indexOf(value) !== -1) {
            this.addLockedFields(value);
            select = 'checked = "checked"';
        }
    }
    if (this.visualStyle === 'list') {
        out = '<li style="list-style-type: none;"><label><input type="checkbox" name="' + value + '" value="' + value + '" class="item-matrix-field" ' + select + '/> ' + text + '</label></li>';
    } else {
        //out = '<div class="box cell"><label><input type="checkbox" name="' + value + '" value="' + value + '" class="item-matrix-field" ' + select + '/> ' + text + '</label></div>';
        out = '<div class="box cell"><input type="checkbox" name="' + value + '" value="' + value + '" class="item-matrix-field" ' + select + '/> <span>' + text + '</span></div>';
    }
    return out;
};

/* **** GETTERS **** */
ItemMatrixField.prototype.getFieldHeight = function () {
    return this.fieldHeight;
};

ItemMatrixField.prototype.getFieldWidth = function () {
    return this.fieldWidth;
};

ItemMatrixField.prototype.getNameModule = function () {
    return this.nameModule;
};

ItemMatrixField.prototype.getLockedField = function () {
    return this.lockedFields;
};

ItemMatrixField.prototype.getObjectValue = function () {
    this.value = JSON.stringify(this.lockedFields);
    return Field.prototype.getObjectValue.call(this);
};
var ItemUpdaterField = function (options, parent) {
    Field.call(this, options, parent);
    this.fields = [];
    this.options = [];
    this.fieldHeight = null;
    this.visualObject = null;
    this.language = {};
    ItemUpdaterField.prototype.initObject.call(this, options);
};

ItemUpdaterField.prototype = new Field();
ItemUpdaterField.prototype.type = 'ItemUpdaterField';

ItemUpdaterField.prototype.initObject = function (options){
    var defaults = {
        fields: [],
        fieldHeight: null,
        language: {
            LBL_ERROR_ON_FIELDS: 'Please, correct the fields with errors'
        }
    };
    $.extend(true, defaults, options);
    this.language = defaults.language;
    this.setFields(defaults.fields)
        .setFieldHeight(defaults.fieldHeight);
};

ItemUpdaterField.prototype.setFields = function (items) {
    var i, aItems = [], newItem;
    for (i = 0; i < items.length; i += 1) {
        if (items[i].type === 'FieldUpdater') {
            items[i].setParent(this);
            aItems.push(items[i]);
        } else {
            newItem = new FieldUpdater(item[i], this);
            aItems.push(newItem);
        }
    }
    this.fields = aItems;
    return this;
};

ItemUpdaterField.prototype.setFieldHeight = function (value) {
    this.fieldHeight = value;
    return this;
};

ItemUpdaterField.prototype.getObjectValue = function () {
    var f, auxValue = [];
    this.convertOptionsToFields();
    for (f = 0; f < this.fields.length; f += 1) {
        auxValue.push(this.fields[f].getJSONObject());
    }
    this.value = JSON.stringify(auxValue);
    return Field.prototype.getObjectValue.call(this);
};

ItemUpdaterField.prototype.getJsonValue = function () {
    var index;
    var jsonFields = [];
    for (index = 0; index < this.options.length; index++) {
        if (this.options && this.options[index].active) {
            field = new FieldUpdater(this.options[index], this)
            jsonFields.push(field.getJSONObject());
        }
    }
    return JSON.stringify(jsonFields);
};

ItemUpdaterField.prototype.convertOptionsToFields = function () {
    var fields = [], i;
    for (i = 0; i < this.options.length; i += 1) {
        if (this.options && this.options[i].active) {
            fields.push(new FieldUpdater(this.options[i], this));
        }
    }
    this.fields = fields;
    return this;
};

ItemUpdaterField.prototype.setOptions = function (data) {
    var i, options = [], newOption, messageMap;
    if (data) {
        for (i = 0; i < data.length; i += 1) {
            if (data[i].type.toLowerCase() !== 'id') {
                if (data[i].type === 'FieldOption') {
                    newOption = data[i];
                } else {
                    newOption =  new FieldOption({
                        fieldId   : data[i].value,
                        fieldName : data[i].text,
                        fieldType : data[i].type.toLowerCase(),
                        fieldItems: data[i].optionItem,
                        required  : !!data[i].required
                    }, this);
                }
                options.push(newOption);
            }
        }
    }
    this.options = options;

    if (this.html) {
        this.visualObject.innerHTML = '';
        for (i = 0; i < this.options.length; i += 1) {
            insert = this.options[i].getHTML();
            if (i % 2 === 0) {
                insert.className += ' updater-inverse';
            }
            this.visualObject.appendChild(insert);
        }
    }
    return this;
};

ItemUpdaterField.prototype.createHTML = function () {
    var fieldLabel, required = '', criteriaContainer;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = required + this.label + ':';
    fieldLabel.style.width = this.parent.labelWidth;
    fieldLabel.style.verticalAlign = 'top';
    this.html.appendChild(fieldLabel);

    criteriaContainer = this.createHTMLElement('div');
    criteriaContainer.className = 'adam-item-updater table';
    criteriaContainer.id = this.id;

    if (this.fieldWidth || this.fieldHeight) {
        style = document.createAttribute('style');
        if (this.fieldWidth) {
            style.value += 'width: ' + this.fieldWidth + 'px; ';
        }
        if (this.fieldHeight) {
            style.value += 'height: ' + this.fieldHeight + 'px; ';
        }
        style.value += 'display: inline-block; margin: 0; overflow: auto; padding: 3px;';
        criteriaContainer.setAttributeNode(style);
    }

    for (i = 0; i < this.options.length; i += 1) {
        insert = this.options[i].getHTML();
        if (i % 2 === 0) {
            insert.className = insert.className + ' updater-inverse';
        }
        criteriaContainer.appendChild(insert);
    }

    this.html.appendChild(criteriaContainer);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }

    this.visualObject = criteriaContainer;

    return this.html;
};

ItemUpdaterField.prototype.setValue = function (value) {
    this.value = value;
    if (this.options && this.options.length > 0) {
        try{
            fields = JSON.parse(value);
            if (fields && fields.length > 0) {
                for (i = 0; i < fields.length; i += 1) {
                    for (j = 0; j < this.options.length; j += 1) {
                        if (fields[i].field === this.options[j].fieldId) {
                            this.options[j].active = true;
                            this.options[j].checkboxControl.checked = true;
                            this.options[j].textControl.disabled = false;
                            this.options[j].fieldValue = fields[i].value;
                            this.options[j].value = fields[i].value;
                            if(this.options[j].fieldType === 'date') {
                                $(this.options[j].textControl)
                                    .datepicker( "option", {disabled: false});
                            } else if(this.options[j].fieldType === 'datetime') {
                                $(this.options[j].textControl)
                                    .datetimepicker( "option", {disabled: false});
                            }
                            if (this.options[j].fieldType == 'checkbox') {
                                this.options[j].textControl.checked = ((fields[i].value == 'on')?true:false);
                            }
                            this.options[j].textControl.value = fields[i].value;
                            //
                            break;
                        }
                    }
                }
            }
        } catch (e) {}
    }
    return this;
};

ItemUpdaterField.prototype.isValid = function() {
    var i, valid = true, current, field;
    for (i = 0; i < this.options.length; i += 1) {
        field = this.options[i];
        valid = valid && field.isValid();
        if(!valid) {
            break;
        }
    }

    if (valid) {
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-on');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-off');
        valid = valid && Field.prototype.isValid.call(this);
    } else {
        this.visualObject.scrollTop += getRelativePosition(field.getHTML(), this.visualObject).top;
        this.errorTooltip.setMessage(this.language.LBL_ERROR_ON_FIELDS);
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-off');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-on');
    }
    return valid;
};

/*
ItemUpdaterField.prototype.validate = function () {
    var i, valid = true, current;
    for (i = 0; i < this.options.length; i += 1) {
        if (this.options[i].checkboxControl.checked) {
            current = this.options[i].isValid();
            valid = valid && current;
            if (!current && this.options[i].errorTooltip) {
                $(this.options[i].errorTooltip.html).removeClass('adam-tooltip-error-off');
                $(this.options[i].errorTooltip.html).addClass('adam-tooltip-error-on');
            }
        }

    }
    return valid;
};*/

//

var FieldUpdater = function (options, parent) {
    Base.call(this, options);
    this.field = null;
    this.fieldName = null;
    this.value = null;
    this.parent = null;
    this.label = null;
    this.module = null;
    FieldUpdater.prototype.initObject.call(this, options, parent);
};

FieldUpdater.prototype = new Base();
FieldUpdater.prototype.type = "FieldUpdater";
FieldUpdater.prototype.initObject = function (options, parent) {
    if (options && options.type === 'FieldOption') {
        this.setField(options.fieldId)
            .setFieldName(options.fieldName)
            .setValue(options.fieldValue)
            .setParent(parent || null);
    } else {
        var defaults = {
            field: null,
            fieldName: null,
            value: null,
            label: null,
            module: null
        };
        $.extend(true, defaults, options);
        this.setField(defaults.field)
            .setFieldName(defaults.fieldName)
            .setValue(defaults.value)
            .setLabel(defaults.label)
            .setModule(defaults.module)
            .setParent(parent || null);
    }
};

FieldUpdater.prototype.setField = function (value, name) {
    this.field = value;
    if (typeof name !== 'undefined') {
        this.fieldName = name;
    }
    return this;
};

FieldUpdater.prototype.setFieldName = function (value) {
    this.fieldName = value;
    return this;
};

FieldUpdater.prototype.setValue = function (value) {
    this.value = value;
    return this;
};

FieldUpdater.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

FieldUpdater.prototype.setLabel = function (label) {
    this.label = label;
    return this;
};

FieldUpdater.prototype.setModule = function (value) {
    this.module = value;
    return this;
};

FieldUpdater.prototype.getLabel = function () {
    var output;
    if (!this.label) {
        if (this.field && this.fieldName) {
            this.label = this.fieldName + ' = ' + "'" + this.value + "'";
        }
    }
    return this.label;
};

FieldUpdater.prototype.getJSONObject = function() {
    return {
        field: this.field,
        fieldName: this.fieldName,
        value: this.value
    };
};

//FieldOption
    var FieldOption = function (options, parent) {
        Element.call(this, options);
        /**
         * Defines the parent Form
         * @type {Form}
         */
        this.parent = null;
        this.active = null;
        this.fieldId = null;
        this.fieldName = null;
        this.fieldValue = null;
        this.fieldItems = null;
        this.checkboxControl = null;
        this.textControl = null;
        this.parent = null;
        this.value = null;
        this.language = {};
        this.maxLength = null;
        this.required = null;
        FieldOption.prototype.initObject.call(this, options, parent);
    };

    FieldOption.prototype = new Element();
    FieldOption.prototype.type = 'FieldOption';

    FieldOption.prototype.initObject = function (options, parent) {
        var defaults;

        defaults = {
            active: false,
            fieldId: null,
            fieldValue: "",
            fieldName: null,
            fieldType: null,
            fieldItems: null,
            maxLength: 0,
            language: {
                ERROR_FIELD_REQUIRED: 'This field is required',
                ERROR_INVALID_INTEGER: 'This field accepts only a integer value',
                ERROR_INVALID_DATETIME: 'This field accepts only a datetime value',
                ERROR_INVALID_DATE: 'This field accepts only a date value',
                ERROR_INVALID_PHONE: 'This field accepts only a phone value',
                ERROR_INVALID_FLOAT: 'This field accepts only a float value',
                ERROR_INVALID_DECIMAL: 'This field accepts only a decimal value',
                ERROR_INVALID_URL: 'This field accpets only an url',
                ERROR_INVALID_CURRENCY: 'This field accepts only a currency value',
                ERROR_INVALID_EMAIL: 'This field accepts only e-mail addresses'
            },
            required: false
        };
        $.extend(true, defaults, options);
        this.language = defaults.language;
        this.setParent(parent);
        this.setRequired(defaults.required)
            .setMaxLength(defaults.maxLength)
            .setActive(defaults.active)
            .setFieldId(defaults.fieldId)
            .setFieldName(defaults.fieldName)
            .setFieldValue(defaults.fieldValue)
            .setFieldType(defaults.fieldType)
            .setFieldItems(defaults.fieldItems)
            .setMessageError(defaults.messageError)
            .setErrorTooltip({});
    };

    FieldOption.prototype.setRequired = function(required) {
        this.required = !!required;
        if(this.html) {
            $(this.html).find('.required.noshadow').show();
        }
        return this;
    };

    FieldOption.prototype.setMaxLength = function(maxLength) {
        var maxLength = parseInt(maxLength, 10);
        if(!isNaN(maxLength)) {
            this.maxLength = maxLength;
            if(this.textControl) {
                if(maxLength > 0) {
                    this.textControl.maxLength = maxLength;    
                } else {
                    this.textControl.removeAttribute('maxlength');
                }
            }
        }
        return this;
    };

    FieldOption.prototype.setActive = function (value) {
        this.active = value;
        return this;
    };

    FieldOption.prototype.setFieldId = function (value) {
        this.fieldId = value;
        return this;
    };

    FieldOption.prototype.setFieldName = function (value) {
        this.fieldName = value;
        return this;
    };

    FieldOption.prototype.setFieldValue = function (value) {
        this.fieldValue = value;
        this.value = value;
        if(this.textControl) {
            $(this.textControl).val(value);
        }
        return this;
    };

    FieldOption.prototype.setFieldType = function (value) {
        this.fieldType = value;
        return this;
    };

    FieldOption.prototype.setFieldItems = function (value) {
        this.fieldItems = value;
        return this;
    };

    FieldOption.prototype.setParent = function (value) {
        this.parent = value;
        return this;
    };

    FieldOption.prototype.createHTML = function () {
        var div,
            checkbox,
            label,
            edit,
            combo,
            checkboxf,
            readAtt,
            disabledValue, 
            span;
        Element.prototype.createHTML.call(this);
        this.style.removeProperties(['width', 'height', 'position', 'top', 'left', 'z-index']);
        this.style.width = '100%';
        this.style.addClasses(['row']);

        div = this.createHTMLElement('div');
        div.className = 'cell';
        div.style.width = '30%';
        checkbox = document.createElement('input');
        checkbox.id = "chk_" + this.id;
        checkbox.type = 'checkbox';
        checkbox.className = 'adam-updater-checkbox';
        div.appendChild(checkbox);

        this.checkboxControl = checkbox;
        label = document.createElement('span');
        label.innerHTML = this.fieldName;
        label.className = 'adam-updater-label';
        div.appendChild(label);
        label = label.cloneNode(false);
        label.className = 'required noshadow';
        label.textContent = '*';
        label.style.display = this.required ? 'inline' : 'none';
        div.appendChild(label);
        this.html.appendChild(div);

        div = this.createHTMLElement('div');
        div.className = 'cell';
        div.style.width = '58%';

        if (this.fieldType === 'dropdown') {
            combo = document.createElement('select');
            for (var item in this.fieldItems) {
                var optionItem = document.createElement("option");
                optionItem.value = item;
                optionItem.style.marginBottom = '0px';
                optionItem.innerHTML = item;
                combo.appendChild(optionItem);
            }
            combo.id = "val_" + this.id;
            combo.type = 'dropdown';
            combo.className = 'adam-updater-value';
            div.appendChild(combo);
            combo.value = this.fieldValue;
            readAtt = document.createAttribute('disabled');
            combo.setAttributeNode(readAtt);
            this.textControl = combo;
        } else if (this.fieldType === 'checkbox') {
            checkboxf = document.createElement('input');
            checkboxf.id = "val_" + this.id;
            checkboxf.type = 'checkbox';
            checkboxf.className = 'adam-updater-checkbox';
            var label = document.createElement('label')
            label.htmlFor = "label" + this.id;
            //label.appendChild(document.createTextNode('Enabled'));
            div.appendChild(label);
            div.appendChild(checkboxf);
            checkboxf.checked = ((this.fieldValue == 'on')?true:false);
            readAtt = document.createAttribute('disabled');
            readAttLabel = document.createAttribute('disabled');
            checkboxf.setAttributeNode(readAtt);
            label.setAttributeNode(readAttLabel);
            this.textControl = checkboxf;
        } else {
            edit = document.createElement('input');
            edit.id = "val_" + this.id;
            edit.type = 'text';
            edit.className = 'adam-updater-value';
            edit.readOnly = this.fieldType === 'date' || this.fieldType === 'datetime';
            div.appendChild(edit);
            edit.value = this.fieldValue;
            readAtt = document.createAttribute('disabled');
            edit.setAttributeNode(readAtt);
            if (this.fieldType === 'password') {
                edit.type = 'password';
            }
            this.textControl = edit;
        }
        this.setMaxLength(this.maxLength);
        if (this.fieldType === 'date') {
            $(edit).datepicker({
                showOn: 'button',
                constrainInput: false,
                disabled : true
            }).next('button').text('').button({icons:{primary : 'ui-icon-calendar'}});
        }
        if (this.fieldType === 'datetime') {
            $(edit).datetimepicker({
                showOn: 'button',
                constrainInput: false,
                disabled : true
            }).next('button').text('').button({icons:{primary : 'ui-icon-calendar'}});
        }


        this.html.appendChild(div);

        div = this.createHTMLElement('div');
        div.className = 'cell';
        div.style.width = '5%';
        if (this.errorTooltip) {
            div.appendChild(this.errorTooltip.getHTML());
        }
        this.html.appendChild(div);

        div = this.createHTMLElement('div');
        div.className = 'clear';
        this.html.appendChild(div);

        this.attachListeners();

        return this.html;
    };

    FieldOption.prototype.attachListeners = function () {
        var root = this;
        $(this.checkboxControl).click(function (e) {
            if (root.checkboxControl.checked) {
                root.textControl.disabled = false;
                root.setActive(true).setFieldValue(root.textControl.value);
                //console.log(root);
                if (root.fieldType  === 'date' || root.fieldType === 'datetime') {
                    $(root.textControl).datepicker( "option", { disabled: false } );
                }


            } else {
                root.textControl.disabled = true;
                root.setActive(false);
                $(root.textControl).removeClass('required');
                $(root.errorTooltip.html).addClass('adam-tooltip-error-off');
                if (root.fieldType  === 'date' || root.fieldType === 'datetime') {
                    $(root.textControl).datepicker( "option", { disabled: true } );
                }
                root.setFieldValue('');
            }
        });
        $(this.textControl).change(function (e) {
            if (root.textControl.type == 'checkbox') {
                root.textControl.value = 'off';
                if (root.textControl.checked == true) {
                    root.textControl.value = 'on';
                }
            }
            root.setFieldValue(root.textControl.value);
            if (!root.isValid() && root.errorTooltip) {
                $(root.errorTooltip.html).removeClass('adam-tooltip-error-off');
                $(root.errorTooltip.html).addClass('adam-tooltip-error-on');
            }
        });
        $(this.checkboxControl).change(function (e) {
            root.parent.parent.setDirty(true);
        });

    };

    /**
     * Sets the fields validation error message
     * @param {String} msg
     * @return {*}
     */
    FieldOption.prototype.setMessageError = function (msg) {
        this.messageError = msg;
        return this;
    };


    FieldOption.prototype.setErrorTooltip = function (tooltip) {
        if (tooltip) {
            if (!tooltip.css) {
                tooltip.css = 'adam-tooltip-error-off';
            }
            if (!tooltip.icon) {
                tooltip.icon = 'adam-tooltip-icon-error';
            }
            tooltip.visible = false;
            this.errorTooltip = new Tooltip(tooltip, this);
        } else {
            this.errorTooltip = null;
        }
        return this;
    };

    FieldOption.prototype.evalRequired = function() {
        if(this.required) {
            switch(this.fieldType) {
                case 'checkbox':
                    return true;
                default:
                    return !!this.textControl.value;
            }
        } else {
            return true;
        }
    };

    FieldOption.prototype.validInput = function() {
        var valid = true, 
            value = this.textControl.value,
            aux;

        switch(this.fieldType) {
            case "integer":
                return /^-?\d+$/.test(value);
            case "datetime":
                if(!/^\d\d(\/\d\d){2}(\d){2}\s\d\d(:\d\d){1,2}$/.test(value)) {
                    return false;
                }
                aux = value.split(" ");
                value = aux[1];
                value = value.split(":");
                value[0] = parseInt(value[0], 10);
                value[1] = parseInt(value[1], 10);
                value[2] = value[2] ? parseInt(value[2], 10) : null;
                if(value[0] > 23 || value[1] > 59 || (!value[2] ? false : value[2] > 59)) {
                    return false;
                }
                value = aux[0];
            case "date":
                if(!/^\d\d\/\d\d\/(\d){4}$/.test(value)) {
                    return false;
                }
                value = value.split("/");
                aux = {};
                aux.y = parseInt(value[2], 10);
                aux.m = parseInt(value[0], 10);
                aux.d = parseInt(value[1], 10);

                if(aux.m < 1 || aux.m > 12 || aux.d < 1 || aux.d > 31) {
                    return false;
                }

                if(aux.m === 4 || aux.m === 6 || aux.m === 9 || aux.m === 11) {
                    if(aux.d > 30) {
                        return false;
                    } 
                } else if(aux.m === 2) {
                    //check if it's a leap year
                    if((aux.y % 4 === 0 && aux.y % 100 !== 0) || aux.y % 400 === 0) {
                        if(aux.d > 29) {
                            return false;
                        }
                    } else {
                        if(aux.d > 28) {
                            return false;
                        }
                    }
                }
                break;
            case "float":
                return /^-?\d*(\.\d+)?$/.test(value);
            case "decimal":
                return /^-?\d+(\.\d{1,2})?$/.test(value);
            case "url":
                return /^(ht|f)tps?:\/\/\w+([\.\-\w]+)?\.([a-z]{2,4}|travel)(:\d{2,5})?(\/.*)?$/i.test(value);
            case "currency":
                return /^-?\d*(\.\d+)?$/.test(value);
            case "email":
                return /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value);
            case "checkbox":
            case "dropdown":
            case "textfield":
            case "name":
            case "password":
            case "textarea":
            case "phone":
                return true;
                break;
        }

        return true;
    };

    FieldOption.prototype.isValid = function () {
        var i, res = true, message;

        if(!this.checkboxControl.checked) {
            return true;
        }

        res = this.evalRequired();
        if(res) {
            switch(this.fieldType) {
                case 'currency':
                case 'date':
                case 'datetime':
                case 'decimal':
                case 'float':
                case 'integer': 
                case 'email':
                    res = this.validInput();
                    break;
                default:
                    res = this.textControl.value ? this.validInput() : true;
            }

            if(!res) {
                switch(this.fieldType) {
                    case "integer":
                        message = this.language.ERROR_INVALID_INTEGER;
                        break;
                    case "datetime":
                        message = this.language.ERROR_INVALID_DATETIME;
                        break;
                    case "date":
                        message = this.language.ERROR_INVALID_DATE;
                        break;
                    case "phone":
                        message = this.language.ERROR_INVALID_PHONE;
                        break;
                    case "float":
                        message = this.language.ERROR_INVALID_FLOAT;
                        break;
                    case "decimal":
                        message = this.language.ERROR_INVALID_DECIMAL;
                        break;
                    case "url":
                        message = this.language.ERROR_INVALID_URL;
                        break;
                    case "currency":
                        message = this.language.ERROR_INVALID_CURRENCY;
                        break;
                    case "email":
                        message = this.language.ERROR_INVALID_EMAIL;
                        break;
                    case "checkbox":
                    case "dropdown":
                    case "textfield":
                    case "name":
                    case "password":
                    case "textarea":
                        message = '';
                        break;
                }
                this.errorTooltip.setMessage(message);
            }

        } else {
            this.errorTooltip.setMessage(this.language.ERROR_FIELD_REQUIRED); 
        }

        if (res) {
            $(this.errorTooltip.html).removeClass('adam-tooltip-error-on');
            $(this.errorTooltip.html).addClass('adam-tooltip-error-off');
        } else {
            $(this.errorTooltip.html).removeClass('adam-tooltip-error-off');
            $(this.errorTooltip.html).addClass('adam-tooltip-error-on');
        }

        return res;
    };



var HtmlPanel = function (options) {
    Panel.call(this, options);
    this.source = this;
    this.scroll = null;
    this.parent = null;
    HtmlPanel.prototype.initObject.call(this, options);
};

HtmlPanel.prototype = new Panel();

HtmlPanel.prototype.type = "HtmlPanel";

HtmlPanel.prototype.initObject = function (options) {
    var defaults = {
        source: null,
        scroll: true
    };
    $.extend(true, defaults, options);
    this.setSource(defaults.source)
        .setScroll(defaults.scroll);
};

HtmlPanel.prototype.setSource = function (source) {
    this.source = source;
    return this;
};

HtmlPanel.prototype.setScroll = function (value) {
    this.scroll = value;
    return this;
};

HtmlPanel.prototype.createHTML = function () {
    var HPDiv,
        scrollMode;
    Panel.prototype.createHTML.call(this);
    if (this.source) {
        scrollMode = (this.scroll) ? 'auto' : 'none';
        HPDiv = this.createHTMLElement('div');
        HPDiv.id = this.id;
        HPDiv.innerHTML = this.source;
        HPDiv.style.overflow = scrollMode;
        HPDiv.style.height = (this.height - 2) + 'px';
        this.body.appendChild(HPDiv);
        this.body.style.bottom = '8px';
    }
    this.attachListeners();
    return this.html;
};

HtmlPanel.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};
HtmlPanel.prototype.attachListeners = function () {
    $(this.body).on('mousedown', function (e) {
        e.stopPropagation();
    });
};
/**
 * @class Store
 * Description of the class Store...
 * @constructor Creates an instance of the class Store
 */
var Store = function (options) {
    /**
     * Array of records defined by a model
     * @type {Array}
     */
    this.records = [];

    /**
     * The model this Store must work with
     * @type {Object}
     */
    this.model = null;

    /**
     * The proxy of this store
     * @type {null}
     */
    this.proxy = null;

    Store.prototype.initObject.call(this, options);
};

/**
 * The type of each instance of this class
 * @property {string}
 */
Store.prototype.type = 'Store';

/**
 * Initializes the element with the options given
 * @param {Object} options options for initializing the object
 */
Store.prototype.initObject = function (options) {
    var defaults = {};
    $.extend(true, defaults, options);
};

/**
 * Adds a record to this store
 * @param record
 * @chainable
 */
Store.prototype.addRecord = function (record) {
    this.records.push(record);
    return this;
};

/**
 * Gets a record by an index
 * @param index
 * @return {Object}
 */
Store.prototype.getRecord = function (index) {
    return this.records[index];
};

/**
 * Gets the size of this store
 * @return {Number}
 */
Store.prototype.getSize = function () {
    return this.records.length;
};

var Grid = function (options) {
    Container.call(this, options);

    /**
     * Array of JS objects describing all the columns of this grid
     * The required properties of each objects are:
     *
     * - text (Text to give to the column)
     * - dataIndex (index located in each record of the store used to give a value to a cell)
     *
     * @type {Array}
     */
    this.columns = [];

    /**
     * The data of this grid is stored in a store
     * @type {JCore.data.Store}
     */
    this.store = null;

    Grid.prototype.initObject.call(this, options);
};

Grid.prototype = new Container();

/**
 * The type of each instance of this class
 * @property {string}
 */
Grid.prototype.type = 'Grid';
Grid.prototype.family = 'Panel';

/**
 * Initializes the element with the options given
 * @param {Object} options options for initializing the object
 */
Grid.prototype.initObject = function (options) {
    var defaults = {
        store: null,
        columns: []
    };
    $.extend(true, defaults, options);
    this.setStore(defaults.store)
        .setColumns(defaults.columns);
};

/**
 * TODO: ADD COMMENTS HERE
 */
Grid.prototype.createHTML = function () {
    var i,
        table,
        record;
    //Grid.superclass.prototype.createHTML.call(this);
    Container.prototype.createHTML.call(this);
    // create the table
    table = document.createElement('table');
    // header
    this.createTableHeaders(table, this.columns);
    // content
    for (i = 0; this.store && i < this.store.getSize(); i += 1) {
        record = this.store.getRecord(i);
        this.createTableRow(table, this.columns, record);
    }

    // append the table's html to the body
    this.body.html.appendChild(table);
};

/**
 *
 * @param table
 * @param {Array} headers Array of JSON, each contains a property called 'text' used
 * to create the header of the table
 */
Grid.prototype.createTableHeaders = function (table, headers) {
    var row = document.createElement('tr'),
        th,
        i;
    for (i = 0; i < headers.length; i += 1) {
        th = document.createElement('th');
        th.innerHTML = headers[i].text;
        row.appendChild(th);
    }
    table.appendChild(row);
    return table;
};

/**
 * Sets the parent object
 * @param {Panel} parent
 * @return {*}
 */
Grid.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
}

/**
 *
 * @param table
 * @param columns
 * @param record
 */
Grid.prototype.createTableRow = function (table, columns, record) {
    var i,
        td,
        row = document.createElement('tr');
    for (i = 0; i < columns.length; i += 1) {
        td = document.createElement('td');
        td.innerHTML = record[columns[i].dataIndex] || "";
        row.appendChild(td);
    }
    table.appendChild(row);
    return table;
};

/**
 * Setter of the store of this object
 * @param {JCore.data.Store} newStore
 * @chainable
 */
Grid.prototype.setStore = function (newStore) {
    this.store = newStore;
    return this;
};

/**
 * Getter of the store of this object
 * @return {JCore.data.Store}
 */
Grid.prototype.getStore = function () {
    return this.store;
};

/**
 * Setter of the columns of this object
 * @param {Array} newColumns
 * @chainable
 */
Grid.prototype.setColumns = function (newColumns) {
    this.columns = newColumns;
    return this;
};

/**
 * Getter of the columns of this object
 * @return {Array}
 */
Grid.prototype.getColumns = function () {
    return this.columns;
};

/**
 * @class Form
 * Handles form panels
 * @extend Panel
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var HistoryPanel = function (options) {
    Panel.call(this, options);

    /**
     * Defines if the form has a proxy
     * @type {Boolean}
     */
    this.proxyEnabled = null;

    /**
     * Defines the form's url
     * @type {String}
     */
    this.url = null;

    /**
     * Defines the form's proxy object
     * @type {Proxy}
     */
    this.proxy = null;
    /**
     * Defines the form loading state
     * @type {Boolean}
     */
    this.loaded = false;
    /**
     * Defines the form's data
     * @type {Object}
     */
    this.data = null;
    /**
     * Defines the callback functions
     * @type {Object}
     */
    this.callback = {};
    /**
     * Defines the dirty form state
     * @type {Boolean}
     */
    this.dirty = false;

    this.buttons = [];

    this.footerAlign = null;

    this.labelWidth = null;

    this.footerHeight = null;

    this.headerHeight = null;

    this.closeContainerOnSubmit = null;

    this.parent = null;

    HistoryPanel.prototype.initObject.call(this, options);
};

HistoryPanel.prototype = new Panel();

/**
 * Defines the object's type
 * @type {String}
 */
HistoryPanel.prototype.type = 'HistoryPanel';

/**
 * Initializes the object with the default values
 */
HistoryPanel.prototype.initObject = function (options) {
    var defaults = {
        url: null,
        data: null,
        proxyEnabled: true,
        callback: {},
        buttons: [],
        footerAlign: 'center',
        labelWidth: '30%',
        footerHeight: 10,
        headerHeight: 0,
        closeContainerOnSubmit: false,
        logType: 'message'

    };
    $.extend(true, defaults, options);
    this.setUrl(defaults.url)
        .setCallback(defaults.callback)
        .setLabelWidth(defaults.labelWidth)
        .setFooterAlign(defaults.footerAlign)
        .setLogType(defaults.logType);
};

/**
 * Sets the form's url
 * @param {String} url
 * @return {*}
 */
HistoryPanel.prototype.setUrl = function (url) {
    this.url = url;
    return this;
};

/**
 * Sets the Proxy Enabled property
 * @param {Boolean} value
 * @return {*}
 */
HistoryPanel.prototype.setProxyEnabled = function (value) {
    this.proxyEnabled = value;
    return this;
};

/**
 * Defines the proxy object
 * @param {Proxy} proxy
 * @return {*}
 */
HistoryPanel.prototype.setProxy = function (proxy) {
    if (proxy && proxy.family && proxy.family === 'Proxy') {
        this.proxy = proxy;
        this.url = proxy.url;
        this.proxyEnabled = true;
    } else {
        if (this.proxyEnabled) {
            if (proxy) {
                if (!proxy.url) {
                    proxy.url = this.url;
                }
                this.proxy = new Proxy(proxy);
            } else {
                if (this.url) {
                    this.proxy = new Proxy({url: this.url});
                }
            }
        }
    }
    return this;
};

/**
 * Defines the form's data object
 * @param {Object} data
 * @return {*}
 */
HistoryPanel.prototype.setData = function (data) {
    this.data = data;
    if (this.loaded) {
        this.applyData();
    }
    return this;
};

/**
 * Sets the form's callback object
 * @param {Object} cb
 * @return {*}
 */
HistoryPanel.prototype.setCallback = function (cb) {
    this.callback = cb;
    return this;
};

HistoryPanel.prototype.setFooterAlign = function (position) {
    this.footerAlign = position;
    return this;
};

HistoryPanel.prototype.setLabelWidth = function (width) {
    this.labelWidth = width;
    return this;
};

HistoryPanel.prototype.setFooterHeight = function (width) {
    this.footerHeight = width;
    return this;
};

HistoryPanel.prototype.setHeaderHeight = function (height) {
    this.headerHeight = height;
    return this;
};

HistoryPanel.prototype.setCloseContainerOnSubmit = function (value) {
    this.closeContainerOnSubmit = value;
    return this;
};
HistoryPanel.prototype.setLogType = function (type) {
    this.logType = type;
    return this;
};
/**
 * Loads the form
 */
HistoryPanel.prototype.load = function () {
    if (!this.loaded) {
        if (this.proxy) {
            this.data = this.proxy.getData();
        }
        if (this.callback.loaded) {
            this.callback.loaded(this.data, this.proxy !== null);
        }
        //this.applyData();
        this.attachListeners();
        this.loaded = true;
    }
};

/**
 * Reloads the form
 */
//
//HistoryPanel.prototype.reload = function () {
//    this.loaded = false;
//    this.load();
//};

/**
 * Applies the data to the form
 */
//HistoryPanel.prototype.applyData = function (dontLoad) {
//    var propertyName, i, related;
//    if (this.data) {
//        if (this.data.related) {
//            for (i = 0; i < this.items.length; i += 1) {
//                if (this.items[i].getType() === 'ComboboxField' && this.items[i].related) {
//                    related = this.items[i].related;
//                    if (this.data.related[related]) {
//                        this.items[i].setOptions(this.data.related[related]);
//                    }
//                }
//            }
//        }
//        for (propertyName in this.data) {
//            for (i = 0; i < this.items.length; i += 1) {
//                if (this.items[i].name === propertyName) {
//                    this.items[i].setValue(this.data[propertyName]);
//                    break;
//                }
//            }
//        }
//    }
//    if (this.callback.loaded && !dontLoad) {
//        this.callback.loaded(this.data, this.proxy !== null);
//    }
//};

/**
 * Add Fields Items
 * @param {Object/Field}item
 */
HistoryPanel.prototype.addLog = function (options) {
    var html,
        newItem;
    newItem = new LogField(options);



    newItem.setParent(this);
    html = newItem.createHTML();

    this.body.appendChild(html);
    this.items.push(newItem);
    return this;
};


/**
 * Sets the items
 * @param {Array} items
 * @return {*}
 */
//HistoryPanel.prototype.setItems = function (items) {
//    var i;
//    for (i = 0; i < items.length; i += 1) {
//        this.addItem(items[i]);
//    }
//    return this;
//};


/**
 * Returns the data
 * @return {Object}
 */
HistoryPanel.prototype.getData = function () {
    var i, result = {};
    for (i = 0; i < this.items.length; i += 1) {
        $.extend(result, this.items[i].getObjectValue());
    }
    return result;
};

/**
 * Sets the dirty form property
 * @param {Boolean} value
 * @return {*}
 */
HistoryPanel.prototype.setDirty = function (value) {
    this.dirty = value;
    return this;
};

/**
 * Returns the dirty form property
 * @return {*}
 */
HistoryPanel.prototype.isDirty = function () {
    return this.dirty;
};

/**
 * Evaluate the fields' validations
 * @return {Boolean}
 */
HistoryPanel.prototype.validate = function () {
    var i, valid = true, current;
    for (i = 0; i < this.items.length; i += 1) {
        current = this.items[i].isValid();
        valid = valid && current;
        if (!current && this.items[i].errorTooltip) {
            $(this.items[i].errorTooltip.html).removeClass('adam-tooltip-error-off');
            $(this.items[i].errorTooltip.html).addClass('adam-tooltip-error-on');
        }
    }
    return valid;
};

HistoryPanel.prototype.testRequired = function () {
    var i, response = true;
    for (i = 0; i < this.items.length; i += 1) {
        response = response && this.items[i].evalRequired();
    }
    return response;
};



HistoryPanel.prototype.attachListeners = function () {
    var i;
    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].attachListeners();
    }
//    for (i = 0; i < this.buttons.length; i += 1) {
//        this.buttons[i].attachListeners();
//    }
    //$(this.footer).draggable( "option", "disabled", true);
    $(this.body).mousedown(function (e) {
        e.stopPropagation();
    });
};



HistoryPanel.prototype.setHeight = function (height) {
    var bodyHeight;
    Panel.prototype.setHeight.call(this, height);
    bodyHeight = this.height - this.footerHeight - this.headerHeight;
    this.setBodyHeight(bodyHeight);
    return this;
};

HistoryPanel.prototype.createHTML = function () {
    var i, footerHeight, html;
    Panel.prototype.createHTML.call(this);
    this.footer.style.textAlign = this.footerAlign;
    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].setParent(this);
        html = this.items[i].getHTML();
        //$(html).find("select, input, textarea").focus(this.onEnterFieldHandler(this.items[i]));
        this.body.appendChild(html);
    }
//    for (i = 0; i < this.buttons.length; i += 1) {
//        this.footer.appendChild(this.buttons[i].getHTML());
//    }
    this.body.style.bottom = '8px';
    //this.footer.style.height = this.footerHeight + 'px';
    return this.html;
};

HistoryPanel.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

HistoryPanel.prototype.getLogField = function (id) {
    var field = null, i;
    for (i = 0; i < this.items.length; i += 1) {
        if (this.items[i].id === id) {
            field = this.items[i];
            return field;
        }
    }
    return field;
};
/**
 * @class LabelField
 * Handles the Label fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var LogField = function (options, parent) {
    Field.call(this, options, parent);
    this.submit = false;
    this.items = [];
    this.deleteBtn = false;
    this.deleteControl = null;
    LogField.prototype.initObject.call(this, options);
    //$.extend(true, this.defaults, options);
};
LogField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
LogField.prototype.type = 'LogField';

LogField.prototype.initObject = function (options) {
    var defaults = {
        marginLeft : 10,
        timeTextSize: 11,
        picture : '/img/default_user.png',
        user: '',
        message: 'default message',
        items : [],
        startDate: '3 July 2013',
        duration: null,
        completed: false,
        deleteBtn: false
    };
    $.extend(true, defaults, options);
    this.setMarginLeft(defaults.marginLeft)
           .setPicture(defaults.picture)
           .setUser(defaults.user)
           .setTimeTextSize(defaults.timeTextSize)
           .setStartDate(defaults.startDate)
           .setMessage(defaults.message)
           .setDuration(defaults.duration)
           .setItems(defaults.items)
           .setCompleted(defaults.completed)
           .setDeleteBtn(defaults.deleteBtn);

};

LogField.prototype.setMarginLeft = function (marginLeft) {
    this.marginLeft = marginLeft;
    return this;
};
LogField.prototype.setPicture = function (picture) {
    this.picture = picture;
    return this;
};
LogField.prototype.setUser = function (user) {
    this.user = user;
    return this;
};
LogField.prototype.setTimeTextSize = function (size) {
    this.timeTextSize = size;
    return this;
};
LogField.prototype.setStartDate = function (date) {
    this.startDate = date;
    return this;
};
LogField.prototype.setMessage = function (msg) {
    this.message = msg;
    return this;
};
LogField.prototype.setDuration = function (time) {
    this.duration = time;
    return this;
};
LogField.prototype.setItems = function (items) {
    this.items = items;
    return this;
};
LogField.prototype.setCompleted = function (val) {
    this.completed = val;
    return this;
};
LogField.prototype.setDeleteBtn = function (val) {
    this.deleteBtn = val;
    return this;
};
/**
 * Creates the HTML Element of the field
 */
LogField.prototype.createHTML = function () {
    var fieldLabel, logPicture, newsItem, datetime, detailDiv, durationDiv,buttonAnchor, labelSpan, that = this;
    Field.prototype.createHTML.call(this);
    this.html.style.fontSize = "12px";
    this.html.style.display = 'table';
    this.html.style.width = '98%';
    detailDiv = this.createHTMLElement('div');
    detailDiv.style.width = '70 %';
    //detailDiv.style.cssFloat= 'left';
    detailDiv.style.display = 'table-cell';

    logPicture = this.createHTMLElement('img');
    logPicture.style.width = '32px';
    logPicture.style.height = '32px';
    logPicture.style.cssFloat = "left";
    logPicture.style.marginRight = "10px";
    logPicture.src = this.picture;
    detailDiv.appendChild(logPicture);


    newsItem = this.createHTMLElement('p');
//    fieldLabel.className = 'adam-form-label';

    newsItem.innerHTML = '<strong>' + this.user + '</strong> ' + this.label;
    //fieldLabel.style.verticalAlign = 'top';
    newsItem.style.marginLeft = this.marginLeft + 'px';
    newsItem.style.display = "block";
    detailDiv.appendChild(newsItem);

    datetime  = this.createHTMLElement('time');
  //  datetime.dateTime = '2013-07-03T11:58:45-04:00';
    datetime.style.color = '#707070';
    datetime.style.fontSize = this.timeTextSize + "px";
    datetime.textContent = this.startDate;
    detailDiv.appendChild(datetime);

    this.html.appendChild(detailDiv);
    if (this.duration) {
        durationDiv = this.createHTMLElement('div');
        durationDiv.style.width = '10%';
        durationDiv.style.paddingLeft = '15px';
        durationDiv.style.display = 'table-cell';
        //durationDiv.style.height = '100%';
        durationDiv.style.color = '#707070';
        durationDiv.style.fontSize = this.timeTextSize + "px";
        durationDiv.innerHTML =  '<p> ' + this.duration + '</p>';
        //for tuning duration section
        this.durationSection = durationDiv;
        this.html.appendChild(durationDiv);
    }


   // if (this.completed) {
    durationDiv = this.createHTMLElement('div');
    durationDiv.style.width = '2%';
    durationDiv.style.paddingLeft = '5px';
    durationDiv.style.display = 'table-cell';
    //durationDiv.style.height = '100%';
    //durationDiv.style.color = '#707070';
    durationDiv.style.fontSize = this.timeTextSize + "px";
    //durationDiv.innerHTML =  '<p> true </p>';
    if (this.completed) {
        durationDiv.className = 'adam-completed-log';
    }

    this.html.appendChild(durationDiv);
//  }


    return this.html;
};
LogField.prototype.attachListeners = function () {
    var id, logPanel, logBefore, logMidle, that;
    that = this;
    $(this.html).click(function (e) {
        id = $(e.currentTarget).attr('id');

        if (that.parent.getLogField(id).parent.itemShowed
            && that.parent.getLogField(id).parent.itemShowed === id) {
            $("#logPanel").slideToggle();
            that.parent.getLogField(id).parent.itemShowed = null;
        } else {
            $('#logPanel').remove();
            if (that.parent.getLogField(id).items.length > 0) {
                logPanel = that.createHTMLElement('div');
                logPanel.id = "logPanel";
                logPanel.style.display = 'none';
                logPanel.style.overflow = 'auto';
                logPanel.style.padding = '10px';
                logPanel.style.border = "1px solid silver";
                logPanel.style.backgroundColor = '#FAFAFA';
                $('#' + id).after(logPanel);

                if (that.parent.logType === 'difList') {
                    //console.log('difList');
                    logBefore = that.parent.getLogField(id).createDifList('before');
                    $(logPanel).append(logBefore);

                    logMidle = that.createHTMLElement('div');
                    logMidle.style.width = '5%';
                    logMidle.style.cssFloat = 'left';
                    logMidle.innerHTML = '&nbsp;';
                    $(logPanel).append(logMidle);

                    logBefore = that.parent.getLogField(id).createDifList('after');
                    $(logPanel).append(logBefore);
                    that.parent.getLogField(id).parent.itemShowed = id;
                } else {
                    logPanel.innerHTML = '<h2 style="text-align: center; font-family: Verdana;">"' + that.message + '"<h2>';
                }
                $("#logPanel").slideToggle();
            } else {
                that.parent.getLogField(id).parent.itemShowed = null;
            }


        }

    });
//    $(this.deleteControl).click(function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//        console.log('remove button');
//        console.log(that);
//    });
};
LogField.prototype.createDifList = function (type) {
    var logDiv, log, c = '', i, related;
    logDiv = this.createHTMLElement('div');
    logDiv.style.width = '45%';
    logDiv.style.position = 'relative';
    //logBefore.style.height = '100%';
    // logBefore.style.verticalAlign= 'middle';
    logDiv.style.cssFloat = 'left';
    //logBefore.style.padding = '5px';
    logDiv.style.backgroundColor = (type === 'before') ? '#fdd' : '#cfc';
    for (i = 0; i < this.items.length; i += 1) {
        related = this.items[i];
        log = this.createHTMLElement('p');
        c = (type === 'before') ? '-' : '+';
        c += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        c += related.field + ': ';
        c += (type === 'before') ? related.before : related.after;
        log.innerHTML = c;
        $(logDiv).append(log);

    }


    return logDiv;
};
LogField.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};
/*globals Container, $, Modal, TabPanelElement, Panel, Base, document, Button,
 setTimeout
 */
/**
 * @class MessagePanel
 * Handle window objects
 * @extend Container
 *
 * @constructor
 * Creates a new instance of the window's class
 * @param {Object} options
 */
var MessagePanel = function (options) {
    Container.call(this, options);
    /**
     * Defines the window's modal property
     * @type {Boolean}
     */
    this.modal = null;
    /**
     * Defines the Modal Object to handle modal windows
     * @type {Modal}
     */
    this.modalObject = null;
    /**
     * Defines the HTML Element to apply the modal mask
     * @type {HTMLElement}
     * @private
     */
    this.modalContainer = null;
    /**
     * Defines the Close Button HTML Element
     * @type {HTMLElement}
     */
    this.closeButtonObject = null;
    /**
     * Defines the window header HTML Element where are placed the title label HTML Element and the Close Button HTML Element
     * @type {HTMLElement}
     */
    this.windowHeader = null;
    /**
     * Records the loading state of the window
     * @type {Boolean}
     * @private
     */
    this.loaded = false;

    /**
     * Defines the DestroyOnHide property
     * @type {Boolean}
     */
    this.destroyOnHide = null;
    this.message = null;
    this.footer = null;
    this.buttons = [];
    this.footerHeight = null;
    this.headerHeight = null;
    this.positionFixed = false;
    MessagePanel.prototype.initObject.call(this, options);
};

MessagePanel.prototype = new Container();

/**
 * Defines the object's type
 * @type {String}
 */
MessagePanel.prototype.type = "MessagePanel";
MessagePanel.prototype.classPictureMap = {
    'Information': 'adam-message-panel-picture-information',
    'Error': 'adam-message-panel-picture-error',
    'Warning': 'adam-message-panel-picture-warning',
    'Confirm': 'adam-message-panel-picture-question'
};
/**
 * Initialize the object with the default values
 */
MessagePanel.prototype.initObject = function (options) {
    var defaults = {
        title: '',
        modal: true,
        closeButton: true,
        modalHandler: null,
        destroyOnHide: false,
        wtype: 'Warning',
        message: '',
        footerHeight: 40,
        headerHeight: 0,
        buttons: [],
        height: 100,
        width: 400
    };
    $.extend(true, defaults, options);
    this.setTitle(defaults.title)
        .setModal(defaults.modal)
        .setVisible(false)
        .setCloseButton(defaults.closeButton)
        .setDestroyOnHide(defaults.destroyOnHide)
        .setMessageType(defaults.wtype)
        .setMessage(defaults.message)
        .setFooterHeight(defaults.footerHeight)
        .setHeaderHeight(defaults.headerHeight)
        .setButtons(defaults.buttons)
        .setHeight(defaults.height)
        .setWidth(defaults.width);

    this.modalContainer = $('body');
};

/**
 * Sets the window's title
 * @param {String} text
 */
MessagePanel.prototype.setTitle = function (text) {
    this.title = text;
    if (this.titleLabelObject) {
        this.titleLabelObject.innerHTML = text;
    }
    return this;
};
/**
 * Creates the HTML Element fot the object
 * @return {*}
 */
MessagePanel.prototype.createHTML = function () {

    var marginProps, closeBtn, titleLabel, windowHeader, tabsContainer, i, footerDiv, pictureDiv, textDiv;
    Container.prototype.createHTML.call(this);
    marginProps = '-' + parseInt(this.height / 2, 10) + 'px 0 0 -' + parseInt(this.width / 2, 10) + 'px';
    //this.style.addClasses(['adam-message-panel']);
    this.style.addClasses(['adam-message-panel']);
    this.style.addProperties({
        'z-index': 1034,
        'left': '50%',
        'top': '50%'

        //'margin': marginProps

//        'height': 'auto',
//        'width': 'auto'
//        'height': '50px',
//        'width': '200px'
    });

    this.height -= 16;
    this.html.style.height = this.height + "px";
    this.html.tabIndex = "-1";

    windowHeader = this.createHTMLElement('div');
    windowHeader.className = 'adam-message-panel-header';
    titleLabel = this.createHTMLElement('label');
    titleLabel.className = 'adam-message-panel-title';
    titleLabel.innerHTML = this.title || "&nbsp;";
    titleLabel.title = titleLabel.innerHTML;
    if (this.closeButton) {
        closeBtn = this.createHTMLElement('span');
        closeBtn.className = 'adam-message-panel-close';
        windowHeader.appendChild(closeBtn);
        this.html.insertBefore(windowHeader, this.body);
        this.closeButtonObject = closeBtn;
    } else {
        this.html.insertBefore(windowHeader, this.body);
    }
    windowHeader.appendChild(titleLabel);

//    tabsContainer = this.createHTMLElement("ul");
//    tabsContainer.className = 'adam-tabs';
//    this.html.insertBefore(tabsContainer, this.body);
//    this.tabsContainer = tabsContainer;
//
////    for(i = 0; i < this.panels.length; i += 1) {
////        tabsContainer.appendChild(this.panels[i].getTab());
////    }
//
//    if(i <= 1) {
//        tabsContainer.style.display = 'none';
//    }
//
    this.windowHeader = windowHeader;
//    this.titleLabelObject = titleLabel;

    //this.html.appendChild(windowHeader);
    if (this.body) {
        this.body.className = 'adam-message-panel-body';
        this.body.style.textAlign = 'center';
        this.body.style.paddingTop = '10px';
        this.body.style.paddingBottom = '10px';
        pictureDiv = this.createHTMLElement('div');
        //pictureDiv.className = 'adam-message-panel-picture-information';
        pictureDiv.className = this.classPictureMap[this.wtype];
        this.body.appendChild(pictureDiv);
        textDiv = this.createHTMLElement('div');
        textDiv.className = 'adam-message-panel-text';
        textDiv.innerHTML = this.getMessage() || "&nbsp;";
        //textDiv.style.display = 'inline-block';
        //textDiv.style.width = '82%';
        this.body.appendChild(textDiv);

        //this.body.style.height = (this.height - 22 - (i > 1 ? 22 : 0)) + 'px';
        //this.body.innerHTML = this.getMessage() || "&nbsp;";
    }
    this.generateButtons(this.wtype);

    if (this.footer) {
        this.html.appendChild(this.footer);
    } else {
        footerDiv = this.createHTMLElement('div');
        footerDiv.className = 'adam-message-panel-footer';
        this.html.appendChild(footerDiv);
        this.footer = footerDiv;
    }


    for (i = 0; i < this.buttons.length; i += 1) {
        this.footer.appendChild(this.buttons[i].getHTML());
    }
    this.body.style.bottom = (this.footerHeight + 8) + 'px';
    this.footer.style.height = this.footerHeight + 'px';
    this.footer.style.textAlign = 'right';
    //this.footer.style.position = 'absolute';
    this.footer.style.bottom = '0px';
    //this.addButtons();
    return this.html;
};

/**
 * Shows the Message panel
 */
MessagePanel.prototype.show = function (params) {
    if (!this.loaded) {
        this.load(params);
    }
    if (this.modal) {
        this.modalObject.show();
        if (this.modalObject.html) {
            this.modalObject.html.style.zIndex = '1034';
        }
    }

    this.setHeight($(this.body).innerHeight());
    document.body.appendChild(this.html);
    this.setVisible(true);
    this.fixPositions();
};
/**
 * Sets the window's modal property
 * @param {Boolean} value
 */
MessagePanel.prototype.setModal = function (value) {
    if (value) {
        this.modalObject = new Modal({
            clickHandler: this.modalHandler
        });
    } else {
        this.modalObject = null;
    }
    this.modal = value;
    return this;
};
/**
 * Opens/Creates the windows object
 * @private
 */
MessagePanel.prototype.load = function (params) {
    var titleLabel;
    if (!this.html) {
        this.createHTML();
        this.attachListeners();
        this.loaded = true;
    }
};
/**
 * Sets the destroy on hide property
 * @param {Boolean} value
 * @return {*}
 */
MessagePanel.prototype.setDestroyOnHide = function (value) {
    this.destroyOnHide = value;
    return this;
};

/**
 * Sets the close Button property
 * @param {Boolean} value
 * @return {*}
 */
MessagePanel.prototype.setCloseButton = function (value) {
    this.closeButton = value;
    return this;
};
/**
 * Sets the window listeners
 */
MessagePanel.prototype.attachListeners = function () {
    var self = this,
        i,
        btn,
        focushandler,
        that = this;
    $(this.html).draggable({
        cursor: "move",
        scroll: false,
        containment: "document"
    }).on('keydown keyup keypress', function (e) {
        e.stopPropagation();
    });

    if (this.closeButton && this.closeButtonObject) {
        $(this.closeButtonObject).click(function (e) {
            e.stopPropagation();
            self.hide();
        });
    }
    for (i = 0; i < this.buttons.length; i += 1) {
        this.buttons[i].attachListeners();
    }
    $('input').blur();
    $('a').blur();

    $(this.html).attr('tabindex', -1).focus();

    setTimeout(function () {
        $(document).on('focusin', focushandler);
    }, 0);

    focushandler = function (e) {
        if (!$(e.target).parents().andSelf().is('#' + that.id)) {
            $(that.html).focus();
        }
    };

};
MessagePanel.prototype.setMessage = function (msg) {
    this.message = msg;
    return this;
};

MessagePanel.prototype.getMessage = function (msg) {
    return this.message;
};
MessagePanel.prototype.setMessageType = function (type) {
    this.wtype = type;
    return this;
};

MessagePanel.prototype.getMessageType = function (type) {
    return this.wtype;
};

/**
 * Hides the window
 * @param {Boolean} [destroy]
 */
MessagePanel.prototype.hide = function (destroy) {
    if (this.modal) {
        this.modalObject.hide();
    }
    document.body.removeChild(this.html);
    this.setVisible(false);
    if (destroy || this.destroyOnHide) {
        this.close();
    }
};
MessagePanel.prototype.generateButtons = function (type) {

    var btns = [],
        that = this;
    if (this.buttons.length === 0) {
        switch (type) {
        case 'Information':
        case 'Error':
        case 'Warning':
        case 'Confirm':
            this.addButton({
                jtype: 'normal',
                caption: 'OK',
                handler: function () {
                    //console.log(this);
                    //alert('handler');
                    that.close();
                    //wAlert.close();
                    //fAlert.submit();
                }
            });
            break;
        }
        //this.setButtons(btns);
    }


    return this;

};

/**
 * Sets the buttons
 * @param {Array} buttons
 * @return {*}
 */
MessagePanel.prototype.setButtons = function (buttons) {
    var i;
    for (i = 0; i < buttons.length; i += 1) {
        this.addButton(buttons[i], this);
    }
    return this;
};

MessagePanel.prototype.addButton = function (button) {
    var newButton;
    if (button && button.family && button.family === 'Button') {
        newButton = button;
        newButton.setParent(this);
    } else {
        newButton = new Button(button, this);
    }
    if (newButton) {
        this.buttons.push(newButton);
    }
};
MessagePanel.prototype.setHeight = function (height) {
    var bodyHeight;
    //Container.prototype.setHeight.call(this, height);
    bodyHeight = this.height - this.footerHeight - this.headerHeight;
    //console.log(bodyHeight);
    this.setBodyHeight(bodyHeight);
    return this;
};

MessagePanel.prototype.setFooterHeight = function (width) {
    this.footerHeight = width;
    return this;
};

MessagePanel.prototype.setHeaderHeight = function (width) {
    this.headerHeight = width;
    return this;
};
/**
 * Close the window and destroy the object
 */
MessagePanel.prototype.close = function () {
    if (this.visible) {
        this.hide();
    }
    if (this.dispose) {
        this.dispose();
    }
};
MessagePanel.prototype.fixPositions = function () {
    if (!this.positionFixed) {
        var width = $(this.html).width(),
            height = $(this.html).height(),
            position = $(this.html).offset(),
            x,
            y;
        x = position.top - height / 2;
        y = position.left - width / 2;
        this.html.style.top = x + 'px';
        this.html.style.left = y + 'px';
        this.positionFixed = true;
    }

    return this;
};
/*global FieldOption, Field, Element, OptionTextField, $, document, OptionSelectField,
 getRelativePosition, OptionCheckBoxField, OptionDateField, replaceExpression, editorWindow,
 translate, MultipleItemPanel, PROJECT_MODULE, CriteriaField, PMSE_DECIMAL_SEPARATOR, TextAreaUpdaterItem, OptionNumberField
 */

/**
 * @class UpdaterField
 * Creates an object that can in order to illustrate a group of fields,
 * checkboxes or select items in the HTML it can be inside a form
 *
 *             //i.e.
 *             var updater_field = new UpdaterField({
 *                 //message that the label will display
 *                  label: "This is a label",
 *                  //name that the field has managed
 *                  name: 'the_name',
 *                  //if the field will be submited
 *                  submit: true,
 *                  //proxy to drive the all options sended from to server
 *                  proxy: proxy
 *                  //width of the field object not the text
 *                  fieldWidth: 470,
 *                  //height of the field object not the text
 *                  fieldHeight: 260
 *              });
 *
 * @extends Field
 *
 * @param {Object} options configuration options for the field object
 * @param {Object} parent
 * @constructor
 */
var UpdaterField = function (options, parent) {
    Field.call(this, options, parent);
    this.fields = [];
    this.options = [];
    this.fieldHeight = null;
    this.visualObject = null;
    this.language = {};
    this._variables = [];
    this._datePanel = null;
    this._variablesList = null;
    this._attachedListeners = false;
    this._decimalSeparator = null;
    this._numberGroupingSeparator = null;
    UpdaterField.prototype.initObject.call(this, options);
};

UpdaterField.prototype = new Field();

/**
 * Type of all updater field instances
 * @property {String}
 */
UpdaterField.prototype.type = 'UpdaterField';

/**
 * Initializer of the object will all the given configuration options
 * @param {Object} options
 */
UpdaterField.prototype.initObject = function (options) {
    var defaults = {
        fields: [],
        fieldHeight: null,
        language: {
            LBL_ERROR_ON_FIELDS: 'Please, correct the fields with errors'
        },
        hasCheckbox : false,
        decimalSeparator: ".",
        numberGroupingSeparator: ","
    };
    $.extend(true, defaults, options);
    this.language = defaults.language;
    this.setFields(defaults.fields);
    this.hasCheckbox = defaults.hasCheckbox;
    this._decimalSeparator = defaults.decimalSeparator;
    this._numberGroupingSeparator = defaults.numberGroupingSeparator;
    //this.hasCheckbox
        //.setFieldHeight(defaults.fieldHeight);
};

/**
 * Sets all option fiels into updater field container
 * @param {Array} items
 * @chainable
 */
UpdaterField.prototype.setFields = function (items) {
    var i, aItems = [], newItem;
    for (i = 0; i < items.length; i += 1) {
        if (items[i].type === 'FieldUpdater') {
            items[i].setParent(this);
            aItems.push(items[i]);
        } else {
            aItems.push(newItem);
        }
    }
    this.fields = aItems;
    return this;
};


//UpdaterField.prototype.setFieldHeight = function (value) {
//    this.fieldHeight = value;
//    return this;
//};

/**
 * Gets an object with all option fields values (label, name, type and values), to send the server
 * @return {Object}
 */
UpdaterField.prototype.getObjectValue = function () {
    var f, auxValue = [];

    for (f = 0; f < this.options.length; f += 1) {
        if (!this.options[f].isDisabled()) {
            auxValue.push(this.options[f].getData());
        }
    }
    this.value = JSON.stringify(auxValue);
    return Field.prototype.getObjectValue.call(this);
};

UpdaterField.prototype._parseSettings = function (settings) {
    var map = {
        value: "name",
        text: "label",
        type: "fieldType",
        len: "maxLength",
        optionItem: "options",
        required: "required"
    }, parsedSettings = {}, key;
    for (key in settings) {
        if (settings.hasOwnProperty(key) && map[key]) {
            parsedSettings[map[key]] = settings[key];
        }
    }
    return parsedSettings;
};

/**
 * Sets child option fiels into updater container
 * @param {Array} settings
 * @chainable
 */
UpdaterField.prototype.setOptions = function (settings) {
    var i,
        options = [],
        newOption,
        aUsers = [],
        customUsers = {};
    this.list = settings;
    for (i = 0; i < settings.length; i += 1) {
        /*CREATE INPUT FIELD*/
        settings[i] = this._parseSettings(settings[i]);
        settings[i].parent = this;
        settings[i].allowDisabling = this.hasCheckbox;
        settings[i].disabled = this.hasCheckbox;
        switch (settings[i].fieldType) {
        case 'TextField':
            newOption =  new TextUpdaterItem(settings[i]);
            break;
        case 'TextArea':
            newOption =  new TextAreaUpdaterItem(settings[i]);
            break;
        case 'Date':
        case 'Datetime':
            newOption =  new DateUpdaterItem(settings[i]);
            break;
        case 'DropDown':
            aUsers = [];
            if (settings[i].options instanceof Array) {
                if (settings[i].value === 'assigned_user_id') {
                    aUsers = [
                        {'text': translate('LBL_PMSE_FORM_OPTION_CURRENT_USER'), 'value': 'currentuser'},
                        {'text': translate('LBL_PMSE_FORM_OPTION_RECORD_OWNER'), 'value': 'owner'},
                        {'text': translate('LBL_PMSE_FORM_OPTION_SUPERVISOR'), 'value': 'supervisor'}
                    ];
                    customUsers = aUsers.concat(settings[i].options);
                    settings[i].options = customUsers;
                }
            } else {
                if (settings[i].options) {
                    $.each(settings[i].options, function (key, value) {
                        aUsers.push({value: key, text: value});
                    });
                }
                settings[i].options = aUsers;

            }
            newOption =  new DropdownUpdaterItem(settings[i]);
            break;
        case 'Checkbox':
            newOption =  new CheckboxUpdaterItem(settings[i]);
            break;
        case 'Integer':
        case 'Currency':
        case 'Decimal':
        case 'Float':
            //newOption =  new OptionNumberField(settings[i], this);
            newOption =  new NumberUpdaterItem(settings[i]);
            break;
        default:
            newOption =  new TextUpdaterItem(settings[i]);
            break;
        }

        options.push(newOption);
    }
    this.options = options;
    this.setOptionsHTML();
    return this;
};

/**
 * Sets html content for each type of option field
 * @chainable
 */
UpdaterField.prototype.setOptionsHTML = function () {
    var i, insert;
    if (this.html) {
        this.visualObject.innerHTML = '';
        for (i = 0; i < this.options.length; i += 1) {
            insert = this.options[i].getHTML();
            if (i % 2 === 0) {
                insert.className += ' updater-inverse';
            }
            this.visualObject.appendChild(insert);
        }
    }
    return this;
};

UpdaterField.prototype.closePanels = function () {
    if (this._datePanel) {
        this._datePanel.close();
    }
    if (this._variablesList) {
        this._variablesList.close();
    }
    return this;
};

UpdaterField.prototype.attachListeners = function () {
    var that = this;
    if (this.html && !this._attachedListeners) {
        jQuery(this.visualObject).on('scroll', function () {
            jQuery(this.parent.body).trigger('scroll');
        });
        jQuery(this.parent.body).on('scroll', function () {
            that.closePanels();
        });
        this._attachedListeners = true;
    }
    return this;
};

/**
 * Creates the basic html node structure for the given object using its
 * previously defined properties
 * @return {HTMLElement}
 */
UpdaterField.prototype.createHTML = function () {
    var fieldLabel, required = '', criteriaContainer, insert, i, style;
    Field.prototype.createHTML.call(this);

    if (this.required) {
        required = '<i>*</i> ';
    }

    fieldLabel = this.createHTMLElement('span');
    fieldLabel.className = 'adam-form-label';
    fieldLabel.innerHTML = this.label + ': ' + required;
    fieldLabel.style.width = this.parent.labelWidth;
    fieldLabel.style.verticalAlign = 'top';
    this.html.appendChild(fieldLabel);

    criteriaContainer = this.createHTMLElement('div');
    criteriaContainer.className = 'adam-item-updater table';
    criteriaContainer.id = this.id;

    if (this.fieldWidth || this.fieldHeight) {
        style = document.createAttribute('style');
        if (this.fieldWidth) {
            style.value += 'width: ' + this.fieldWidth + 'px; ';
        }
        if (this.fieldHeight) {
            style.value += 'height: ' + this.fieldHeight + 'px; ';
        }
        style.value += 'display: inline-block; margin: 0; overflow: auto; padding: 3px;';
        criteriaContainer.setAttributeNode(style);
    }

    for (i = 0; i < this.options.length; i += 1) {
        insert = this.options[i].getHTML();
        //console.log( i % 2, 'aa');
        if (i % 2 === 0) {
            insert.className = insert.className + ' updater-inverse';
        }
        criteriaContainer.appendChild(insert);
    }

    this.html.appendChild(criteriaContainer);

    if (this.errorTooltip) {
        this.html.appendChild(this.errorTooltip.getHTML());
    }
    if (this.helpTooltip) {
        this.html.appendChild(this.helpTooltip.getHTML());
    }

    this.visualObject = criteriaContainer;

    return this.html;
};

/**
 * Sets values of every option field into an updater Field container,
 * determining the option field type
 * @param {Array} value
 * @chainable
 */
UpdaterField.prototype.setValue = function (value) {
    this.value = value;
    if (this.options && this.options.length > 0) {
        try {
            var fields, i, j;
            fields = JSON.parse(value);
            if (fields && fields.length > 0) {
                for (i = 0; i < fields.length; i += 1) {
                    for (j = 0; j < this.options.length; j += 1) {
                        if (fields[i].field === this.options[j].getName()) {
                            this.options[j].enable();    
                            /*if (this.hasCheckbox) {
                                this.options[j].checkboxControl.checked = true;
                            }*/
                            //this.options[j].control.disabled = false;
                            this.options[j].setValue(fields[i].value); //this.options[j].value = fields[i].value;
                            //this.options[j].value = fields[i].value;
//                            if (this.options[j].fieldType === 'date') {
//                                $(this.options[j].textControl)
//                                    .datepicker("option", {disabled: false});
//                            } else if (this.options[j].fieldType === 'datetime') {
//                                $(this.options[j].textControl)
//                                    .datetimepicker("option", {disabled: false});
//                            }
                            /*if (this.options[j].type === 'OptionCheckBoxField') {
                                //this.options[j].control.checked = ((fields[i].value === 'on') ? true : false);
                                this.options[j].control.checked = fields[i].value;
                            }*/
                            /*if (this.options[j].type === 'OptionDateField' || this.options[j].type === 'OptionNumberCriteriaField') {
                                //for (k = 0; k < fields[i].value)
                                this.options[j].addCriteriaItems(fields[i].value);
                                this.options[j].timerCriteria.enable();
                                this.options[j].disabled = false;

                            }*/
                            //this.options[j].control.value = fields[i].value;
                            //
                            break;
                        }
                    }
                }
            }
        } catch (e) {}
    }
    return this;
};

/**
 * Determines whether a field is valid checking if required
 * and the value corresponds to the type of data the shows an visual warning
 * @return {Boolean}
 */
UpdaterField.prototype.isValid = function () {
    var i, valid = true, current, field;
    for (i = 0; i < this.options.length; i += 1) {
        field = this.options[i];
        //valid = valid && field.isValid();
        if (field.isRequired()) {
            switch (field.type) {
            case 'CheckboxUpdaterItem':
                valid = field.getValue();
                break;
            case 'DateUpdaterItem':
            case 'NumberUpdaterItem':
                valid = !!field.getValue().length;
                break;
            default:
                if (field.getValue() === '') {
                    valid = false;
                }
                break;
            }

        }
        //TODO: create validation for expressions built with expressionControl.
        /*if (field.type === 'DateUpdaterItem' && !field.timerCriteria.isValid()) {
            valid = false;
        }*/
        if (field._parent.hasCheckbox && field.isDisabled()) {
            valid = true;
        }

        if (!valid) {
            break;
        }
    }

    if (valid) {
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-on');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-off');
        valid = valid && Field.prototype.isValid.call(this);
    } else {
        this.visualObject.scrollTop += getRelativePosition(field.getHTML(), this.visualObject).top;
        this.errorTooltip.setMessage(this.language.LBL_ERROR_ON_FIELDS);
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-off');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-on');
    }
    return valid;
};

/**
 * Obtains and creates the variable string according to the format established
 * for handling variables in sugar
 * @param {String} module
 */
UpdaterField.prototype._onValueGenerationHandler = function (module) {
    var  that = this;
    return function () {
        var newExpression, field = that.currentField, control, i, currentValue = field.getValue(), aux, aux2,
            panel, list;

        control = field._control;
        if (this instanceof ExpressionControl) {
            panel = arguments[0];
            newExpression = panel.getValueObject();
        } else {
            panel = arguments[0];
            list = arguments[1];
            newExpression = "{::" + module + "::" + arguments[2].name  + "::}";
            i = control.selectionStart;
            i = i || 0;
            aux = currentValue.substr(0, i);
            aux2 = currentValue.substr(i);
            newExpression = aux + newExpression + aux2;
        }
        
        field.setValue(newExpression);
        if (!(panel instanceof ExpressionControl)) {
            panel.close();  
        }
    //Previous version
        /*var input, currentValue, i, newExpression = "{::" + module + "::" + value + "::}", aux, aux2, field = that.currentField;
        if (this.parent.belongsTo.tagName.toLowerCase() === "input") {
            input = $(field.control).get(0);
            currentValue = input.value;
            i = input.selectionStart;
        } else if (this.parent.belongsTo.tagName.toLowerCase() === "textarea") {
            input = $(field.control).get(0);
            currentValue = input.value;
            i = input.selectionStart;
        } else if (this.parent.belongsTo.tagName.toLowerCase() === "div") {
            input = $('#plain_email_body').get(0);
            currentValue = input.value;
            i = input.selectionStart;
        } else {
            input = $(this.parent.belongsTo).data("textNode");
            currentValue = input.nodeValue;
            i = editorWindow.getSelection().anchorOffset;
        }
        //var input = $('#email_subject').get(0), i = input.selectionStart, aux, aux2, newExpression = "{::" + module + "::" + value + "::}";
        if (i) {
            if (currentValue.charAt(i - 1) === "{") {
                aux = currentValue.substr(i - 1);
                aux2 = replaceExpression(aux, newExpression);
                aux = aux2 === aux ? aux.replace(/\{/, newExpression) : aux2;
            } else if (i > 1 && currentValue.charAt(i - 1) === ":" && currentValue.charAt(i - 2) === "{") {
                aux = currentValue.substr(i - 2);
                aux2 = replaceExpression(aux, newExpression);
                aux = aux2 === aux ? aux.replace(/\{\:/, newExpression) : aux2;
                i -= 1;
            } else if (i > 2 && currentValue.charAt(i - 1) === ":" && currentValue.charAt(i - 2) === ":" && currentValue.charAt(i - 3) === "{") {
                aux = currentValue.substr(i - 3);
                aux2 = replaceExpression(aux, newExpression);
                aux = aux2 === aux ? aux.replace(/\{\:\:/, newExpression) : aux2;
                i -= 2;
            }
            if (aux2) {
                value = currentValue.substr(0, i - 1) + aux;
            } else {
                value = currentValue.substr(0, i) + newExpression + currentValue.substr(i);
            }
        } else {
            i = 0;
            value = newExpression + currentValue;
        }
        if (this.parent.belongsTo.tagName.toLowerCase() === 'input' || this.parent.belongsTo.tagName.toLowerCase() === 'div' || this.parent.belongsTo.tagName.toLowerCase() === 'textarea') {
            input.value = value;
            //input.selectionStart = input.selectionEnd = i + newExpression.length;
        } else {
            input.nodeValue = value;
            //editorWindow.getSelection().anchorOffset = 8;
        }
        that.multiplePanel.close();*/
    };
};

/**
 * Displays and create the control panel with filled with the possibilities
 * of the sugar variables, change the panel z-index to show correctly,
 * finally add a windows close event for close the control panel
 * @param {Object} field
 */
UpdaterField.prototype.openPanelOnItem = function (field) {
    var that = this, settings, inputPos, textSize, subjectInput, i, 
        variablesDataSource = project.getMetadata("targetModuleFieldsDataSource"), currentFilters, list, targetPanel,
        currentOwner, fieldType = field.getFieldType();

    if (!(field instanceof DateUpdaterItem || field instanceof NumberUpdaterItem)) {
        if (!this._variablesList) {
            this._variablesList = new FieldPanel({
                className: "updateritem-panel",
                //height: "auto",
                items: [
                    {
                        type: "list",
                        bodyHeight: 100,
                        collapsed: false,
                        itemsContent: "{{label}}",
                        fieldToFilter: "type",
                        title: translate('LBL_PMSE_UPDATERFIELD_VARIABLES_LIST_TITLE').replace(/%MODULE%/g, PROJECT_MODULE)
                    }
                ],
                onItemValueAction: this._onValueGenerationHandler(PROJECT_MODULE),
                onOpen: function () {
                    jQuery(that.currentField.html).addClass("opened");
                },
                onClose: function () {
                    jQuery(that.currentField.html).removeClass("opened");
                }
            });    
        }
        if (this._datePanel && this._datePanel.isOpen()) {
            this._datePanel.close();
        }
        targetPanel = this._variablesList;
        list = this._variablesList.getItems()[0];
        currentFilters = list.getFilter();
        //We check if the variables list has the same filter than the one we need right now, 
        //if it do then we don't need to apply the data filtering for a new criteria
        if (!(currentFilters.length === 1 && currentFilters.indexOf(field._fieldType) > 0)) {
            list.setDataItems(this._variables, "fieldType", field._fieldType);
        }
        this.currentField = field;
    } else {
        if (!this._datePanel) {
            this._datePanel = new ExpressionControl({
                className: "updateritem-panel",
                onChange: this._onValueGenerationHandler(PROJECT_MODULE),
                appendTo: (this.parent && this.parent.parent && this.parent.parent.html) || null,
                decimalSeparator: this._decimalSeparator,
                numberGroupingSeparator: this._numberGroupingSeparator,
                onOpen: function () {
                    jQuery(that.currentField.html).addClass("opened");
                },
                onClose: function () {
                    jQuery(that.currentField.html).removeClass("opened");
                }
            });
        }
        //Check if the panel is already configured for the current field's type
        //in order to do it, we verify if the current field class is the same that the previous field's.
        if (!this.currentField || (this.currentField.constructor !== field.constructor)) {
            if (field instanceof DateUpdaterItem) {
                this._datePanel.setOperators({
                    arithmetic: ["+", "-"]
                }).setConstantPanel({
                    date: true, 
                    timespan: true
                });
            } else {
                this._datePanel.setOperators({
                    arithmetic: true
                }).setConstantPanel({
                    basic: {
                        number: true
                    }
                });
            }
            this._datePanel.setVariablePanel({
                data: [{
                    name: PROJECT_MODULE,
                    value: PROJECT_MODULE,
                    items: this._variables
                }],
                dataFormat: "hierarchical",
                typeField: "fieldType",
                typeFilter: field._fieldType,
                textField: "label",
                valueField: "name",
                dataChildRoot: "items",
                moduleTextField: "name",
                moduleValueField: "value"
            });
        }
        this.currentField = field;
        this._datePanel.setValue(field.getValue());
        if (this._variablesList && this._variablesList.isOpen()) {
            this._variablesList.close();
        }
        targetPanel = this._datePanel;
    }

    /*if (!this.multiplePanel) {
        this.multiplePanel = new ExpressionControl({
            onChange: this._onValueGenerationHandler(PROJECT_MODULE),
            matchParentWidth: false,
            expressionVisualizer: false,
            width: 200
        });

        if (field.fieldType !== 'date' && field.fieldType !== 'datetime') {


            this.multiplePanel.addSubpanel({
                title: translate('LBL_PMSE_ADAM_UI_TITLE_MODULE_FIELDS', 'pmse_Project', translate('LBL_PMSE_LABEL_TARGETMODULE')),
                collapsable: true,
                items: this.panelList,
                //onOpen: this.getOnOpenHandler(PROJECT_MODULE),
                onItemSelect: this.getAddVariableHandler(PROJECT_MODULE)
            }, "list");
            document.body.appendChild(this.multiplePanel.getHTML());
        }
    } else {
        //this.multiplePanel.close();
    }*/


    subjectInput = field._control;
    currentOwner = targetPanel.getOwner();
    if (currentOwner !== subjectInput) {
        targetPanel.close(); 
        targetPanel.setOwner(subjectInput);
        targetPanel.open();
    } else {
        if (targetPanel.isOpen()) {
            targetPanel.close();
        } else {
            targetPanel.open();
        }
    }
    
    /*this.multiplePanel.open();
    if (this.multiplePanel.subpanels[0]) {
        this.multiplePanel.subpanels[0].open();
    }
    this.multiplePanel.getHTML().style.zIndex = '1034';

    $('.adam-window-close').on('click', function (e) {
        if (that.multiplePanel) {
            that.multiplePanel.close();
        }
    });
    $('.adam-panel-body').scroll(function(){
        if (that.multiplePanel) {
            that.multiplePanel.close();
        }
    });*/
    
    return this;
};
UpdaterField.prototype.setVariables = function (variables) {
    this._variables = variables;
    return this;
};

//UpdaterItem
    var UpdaterItem = function (settings) {
        Element.call(this, settings);
        this._parent = null;
        this._name = null;
        this._label = null;
        this._required = null;
        this._dom = {};
        this._activationControl = null;
        this._control = null;
        this._disabled = null;
        this._value = null;
        this._fieldType = null;
        this._configButton = null;
        this._attachedListeners = false;
        this._dirty = false;
        this._allowDisabling = true;
        UpdaterItem.prototype.init.call(this, settings);
    };

    UpdaterItem.prototype = new Element();
    UpdaterItem.prototype.constructor = UpdaterItem;
    UpdaterItem.prototype.type = "UpdaterItem";

    UpdaterItem.prototype.init = function(settings) {
        var defaults = {
            parent: null,
            name: this.id,
            label: "[updater item]",
            required: false,
            disabled: true,
            allowDisabling: true,
            value: "",
            fieldType: null
        };

        jQuery.extend(true, defaults, settings);

        this.setParent(defaults.parent)
            .setName(defaults.name)
            .setLabel(defaults.label)
            .setRequired(defaults.required)
            .setValue(defaults.value)
            .setFieldType(defaults.fieldType);

        if (defaults.disabled) {
            this.disable();
        } else {
            this.enable();
        }
        if (defaults.allowDisabling) {
            this.allowDisabling();
        } else {
            this.disallowDisabling();
        }
    };

    UpdaterItem.prototype.allowDisabling = function () {
        this._allowDisabling = true;
        if (this._activationControl) {
            this._activationControl.style.display = "";
        }
        return this;
    };

    UpdaterItem.prototype.disallowDisabling = function () {
        this._allowDisabling = false;
        if (this._activationControl) {
            this._activationControl.style.display = "none";
        }
    };

    UpdaterItem.prototype.setParent = function (parent) {
        if (!(parent === null || parent instanceof UpdaterField)) {
            throw new Error("setParent(): The parameter must be an instance of UpdaterField or null.");
        }
        this._parent = parent;
        return this;
    };

    UpdaterItem.prototype.setName = function (name) {
        if (!(typeof name === 'string' && name)) {
            throw new Error("setName(): The parameter must be a non empty string.");
        }
        this._name = name;
        return this;
    };

    UpdaterItem.prototype.getName = function () {
        return this._name;
    };

    UpdaterItem.prototype.setLabel = function (label) {
        if (typeof label !== 'string') {
            throw new Error("setLabel(): The parameter must be a string.");
        }
        this._label = label;
        if (this._dom.labelText) {
            this._dom.labelText.textContent = label;
        }
        return this;
    };

    UpdaterItem.prototype.setRequired = function (required) {
        var requireContent = "*";
        this._required = !!required;
        if (this._dom.requiredContainer) {
            if (!this._required) {
                requireContent = "";
            }
            this._dom.requiredContainer.textContent = requireContent;
        }
        return this;
    };

    UpdaterItem.prototype.isRequired = function () {
        return this._required;
    };

    UpdaterItem.prototype.isValid = function () {
        return !!(this._required && this._value);
    };

    UpdaterItem.prototype.clear = function () {
        if (this._control) {
            this._control.value = "";
        }
        this._value = "";
        return this;
    };

    UpdaterItem.prototype.disable = function () {
        if (this._activationControl) {
            this._activationControl.checked = false;
            this._disableControl();
        }
        this.clear();
        this._disabled = true;
        return this;
    };

    UpdaterItem.prototype.enable = function () {
        if (this._activationControl) {
            this._activationControl.checked = true;
            this._enableControl();
        }
        this._disabled = false;
        return this;
    };

    UpdaterItem.prototype.isDisabled = function () {
        return this._disabled;
    };

    UpdaterItem.prototype._setValueToControl = function (value) {
        this._control.value = value;
        return this;
    };

    UpdaterItem.prototype._getValueFromControl = function () {
        return this._control.value;
    };

    UpdaterItem.prototype.setValue = function (value) {
        if (typeof value !== 'string') {
            throw new Error("setValue(): The parameter must be a string.");
        }
        if (this._control) {
            this._setValueToControl(value);
            this._value = this._getValueFromControl();    
        } else {
            this._value = value;
        }
        return this;
    };

    UpdaterItem.prototype.getValue = function () {
        return this._value;
    };

    UpdaterItem.prototype.setFieldType = function (fieldType) {
        if (!(fieldType === null || typeof fieldType === "string")) {
            throw new Error("setFieldType(): The parameter must be a string or null.");
        }
        this._fieldType = fieldType;
        return this;
    };

    UpdaterItem.prototype.getFieldType = function () {
        return this._fieldType;
    };

    UpdaterItem.prototype._createControl = function () {
        if (!this._control) {
            throw new Error("_createControl(): This method must be called from anUpdaterItem's subclass.");
        }
        jQuery(this._control).addClass("updateritem-control");
        return this._control;
    };

    UpdaterItem.prototype._createConfigButton = function () {
        var button = this.createHTMLElement("a");
        button.href = "#";
        button.className = "adam-itemupdater-cfg icon-cog";
        this._configButton = button;
        return this._configButton;
    };

    UpdaterItem.prototype._disableControl = function () {
        this._control.disabled = true;
        return this;
    };

    UpdaterItem.prototype._enableControl = function () {
        this._control.disabled = false;
        return this;
    };

    UpdaterField.prototype.isDirty = function () {
        return this._dirty;
    };

    UpdaterItem.prototype._onChange = function () {
        var that = this;
        return function (e) {
            var currValue = that._value;
            that._value = that._getValueFromControl();
            if (that._value !== currValue) {
                that._dirty = true;
            }
        };
    };

    UpdaterItem.prototype.getData = function () {
        return {
            name: this._label,
            field: this._name,
            value: this._value,
            type: this._fieldType
        };
    };

    UpdaterItem.prototype.attachListeners = function () {
        var that = this;
        if (this.html && !this._attachedListeners) {
            jQuery(this._activationControl).on("change", function (e) {
                if (e.target.checked) {
                    that.enable();
                } else {
                    that.disable();
                }
            });
            jQuery(this._configButton).on("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (that._parent && !that._disabled) {
                    that._parent.openPanelOnItem(that);
                }
            });
            jQuery(this._control).on("change", this._onChange());
        }
        return this;
    };

    UpdaterItem.prototype.createHTML = function () {
        var label, 
            controlContainer, 
            activationControl,
            labelContent,
            labelText, 
            requiredContainer, 
            messageContainer,
            configButton,
            messageContainer;

        if (!this.html) {
            Element.prototype.createHTML.call(this);
            jQuery(this.html).addClass("updaterfield-item");
            this.style.removeProperties(['width', 'height', 'position', 'top', 'left', 'z-index']);

            label = this.createHTMLElement('label');
            label.className = 'adam-itemupdater-label';

            controlContainer = this.createHTMLElement("div");
            controlContainer.className = "adam-itemupdater-controlcontainer";

            activationControl = this.createHTMLElement("input");
            activationControl.type = "checkbox";
            activationControl.className = "adam-itemupdater-activation";

            labelContent = this.createHTMLElement("span");
            labelContent.className = "adam-itemupdater-labelcontent";

            labelText = this.createHTMLElement("span");
            labelText.className = "adam-itemupdater-labeltext";

            requiredContainer = this.createHTMLElement("span");
            requiredContainer.className = "adam-itemupdater-required required noshadow";

            messageContainer = this.createHTMLElement("div");
            messageContainer.className = "adam-itemupdater-message";

            labelContent.appendChild(labelText);
            labelContent.appendChild(requiredContainer);

            label.appendChild(activationControl);
            label.appendChild(labelContent);

            controlContainer.appendChild(this._createControl());
            this._createConfigButton();
            if (this._configButton) {
                controlContainer.appendChild(this._configButton);    
            }
            
            this._dom.labelText = labelText;
            this._dom.requiredContainer = requiredContainer;

            this._activationControl = activationControl;
            this.html.appendChild(label);
            this.html.appendChild(controlContainer);
            this.html.appendChild(messageContainer);

            this.setLabel(this._label)
                .setRequired(this._required);
            if (this._disabled) {
                this.disable();
            } else {
                this.enable();
            }
            if (this._allowDisabling) {
                this.allowDisabling();
            } else {
                this.disallowDisabling();
            }
            this.attachListeners();
            this.setValue(this._value);
        }
        return this.html;
    };
//TextUpdaterItem
    var TextUpdaterItem = function (settings) {
        UpdaterItem.call(this, settings);
        this._maxLength = null;
        TextUpdaterItem.prototype.init.call(this, settings);
    };

    TextUpdaterItem.prototype = new UpdaterItem();
    TextUpdaterItem.prototype.constructor = TextUpdaterItem;
    TextUpdaterItem.prototype.type = "TextUpdaterItem";

    TextUpdaterItem.prototype.init = function (settings) {
        var defaults = {
            maxLength: 0
        };

        jQuery.extend(true, defaults, settings);

        this.setMaxLength(defaults.maxLength);
    };

    TextUpdaterItem.prototype.setMaxLength = function (maxLength) {
        if (typeof maxLength === 'string' && /\d+/.test(maxLength)) {
            maxLength = parseInt(maxLength, 10);
        }
        if (typeof maxLength !== 'number') {
            throw new Error("setMaxLength(): The parameter must be a number.");
        }
        this._maxLength = maxLength;
        if (this._control) {
            if (maxLength) {
                this._control.maxLength = maxLength;
            } else {
                this._control.removeAttribute("maxlength");
            }
            
        }
        return this;
    };

    TextUpdaterItem.prototype._createControl = function () {
        var control = this.createHTMLElement("input");
        control.type = "text";
        this._control = control;
        this.setMaxLength(this._maxLength);
        return UpdaterItem.prototype._createControl.call(this);
    };
//DateUpdaterItem
    var DateUpdaterItem = function (settings) {
        UpdaterItem.call(this, settings);
        DateUpdaterItem.prototype.init.call(this, settings);
    };

    DateUpdaterItem.prototype = new UpdaterItem();
    DateUpdaterItem.prototype.constructor = DateUpdaterItem;
    DateUpdaterItem.prototype.type = "DateUpdaterItem";

    DateUpdaterItem.prototype.init = function (settings) {
        var defaults = {
            value: "[]"
        };

        jQuery.extend(true, defaults, settings);

        this.setValue(defaults.value);
    };

    DateUpdaterItem.prototype._setValueToControl = function (value) {
        var friendlyValue = "", i;
        value.forEach(function(value, index, arr) {
            friendlyValue += " " + value.expLabel;
        });
        this._control.value = friendlyValue;
        return this;
    };

    DateUpdaterItem.prototype.setValue = function (value) {
        if (typeof value === 'string') {
            value = value || "[]";
            value = JSON.parse(value);
        }
        if (this._control) {
            this._setValueToControl(value);   
        }
        this._value = value;
        return this;
    };

    DateUpdaterItem.prototype.clear = function () {
        UpdaterItem.prototype.clear.call(this);
        this._value = "[]";
        return this;
    };

    DateUpdaterItem.prototype._createControl = function () {
        var control = this.createHTMLElement("input");
        control.type = "text";
        control.readOnly = true;
        this._control = control;
        return UpdaterItem.prototype._createControl.call(this);
    };

    DateUpdaterItem.prototype._createConfigButton = function () {
        return null;
    };

    DateUpdaterItem.prototype.attachListeners = function () {
        var that = this;
        if (this.html && !this._attachedListeners) {
            UpdaterItem.prototype.attachListeners.call(this);
            jQuery(this._control).on("focus", function () {
                if (that._parent && !this._disabled) {
                    that._parent.openPanelOnItem(that);
                }
            });
            this._attachedListeners = true;
        }
    };
//CheckboxUpdaterItem
    var CheckboxUpdaterItem = function (settings) {
        UpdaterItem.call(this, settings);
    };

    CheckboxUpdaterItem.prototype = new UpdaterItem();
    CheckboxUpdaterItem.prototype.constructor = CheckboxUpdaterItem;
    CheckboxUpdaterItem.prototype.type = "CheckboxUpdaterItem";

    CheckboxUpdaterItem.prototype.setValue = function (value) {
        if (this._control) {
            this._setValueToControl(value);
            this._value = this._getValueFromControl();
        } else {
            this._value = !!value;
        }
        return this;
    };

    CheckboxUpdaterItem.prototype._createControl = function () {
        var control = this.createHTMLElement('input');
        control.type = "checkbox";
        this._control = control;
        return UpdaterItem.prototype._createControl.call(this);
    };

    CheckboxUpdaterItem.prototype._createConfigButton = function () {
        return null;
    };

    CheckboxUpdaterItem.prototype.clear = function () {
        if (this._control) {
            this._control.checked = false;
        }
        this._value = false;
        return this;
    };

    CheckboxUpdaterItem.prototype._setValueToControl = function (value) {
        this._control.checked = !!value;
        return this;
    };

    CheckboxUpdaterItem.prototype._getValueFromControl = function () {
        return this._control.checked;
    };

    CheckboxUpdaterItem.prototype._onChange = function () {
        var that = this;
        return function (e) {
            var currValue = that._value;
            that._value = that._getValueFromControl();
            if (that._value !== currValue) {
                that._dirty = true;
            }
        };
    };
//TextAreaUpdaterItem
    var TextAreaUpdaterItem = function (settings) {
        TextUpdaterItem.call(this, settings);
    };

    TextAreaUpdaterItem.prototype = new TextUpdaterItem();
    TextAreaUpdaterItem.prototype.constructor = TextAreaUpdaterItem;
    TextAreaUpdaterItem.prototype.type = "TextAreaUpdaterItem";

    TextAreaUpdaterItem.prototype._createControl = function () {
        var control = this.createHTMLElement('textarea');
        this._control = control;
        return UpdaterItem.prototype._createControl.call(this);
    };
//NumberUpdaterItem
    var NumberUpdaterItem = function (settings) {
        UpdaterItem.call(this, settings);
        NumberUpdaterItem.prototype.init.call(this, settings);
    };

    NumberUpdaterItem.prototype = new UpdaterItem();
    NumberUpdaterItem.prototype.constructor = NumberUpdaterItem;
    NumberUpdaterItem.prototype.type = "NumberUpdaterItem";

    NumberUpdaterItem.prototype.init = function (settings) {
        var defaults = {
            value: "[]"
        };
        jQuery.extend(true, defaults, settings);
        this.setValue(defaults.value);
    };

    NumberUpdaterItem.prototype._setValueToControl = function (value) {
        var friendlyValue = "", i;
        value.forEach(function(value, index, arr) {
            friendlyValue += " " + value.expLabel;
        });
        this._control.value = friendlyValue;
        return this;
    };


    NumberUpdaterItem.prototype.setValue = function (value) {
        if (typeof value === 'string') {
            value = value || "[]";
            value = JSON.parse(value);
        }
        if (this._control) {
            this._setValueToControl(value);   
        }
        this._value = value;
        return this;
    };

    NumberUpdaterItem.prototype._createControl = function () {
        var control = this.createHTMLElement("input");
        control.type = "text";
        control.readOnly = true;
        this._control = control;
        return UpdaterItem.prototype._createControl.call(this);
    };
//DropdownUpdaterItem
    var DropdownUpdaterItem = function (settings) {
        UpdaterItem.call(this, settings);
        this._options = [];
        this._massiveAction = false;
        this._initialized = false;
        DropdownUpdaterItem.prototype.init.call(this, settings);
    };

    DropdownUpdaterItem.prototype = new UpdaterItem();
    DropdownUpdaterItem.prototype.constructor = DropdownUpdaterItem;
    DropdownUpdaterItem.prototype.type = "DropdownUpdaterItem";

    DropdownUpdaterItem.prototype.init = function (settings) {
        var defaults = {
            options: [],
            value: ""
        };

        jQuery.extend(true, defaults, settings);

        this.setOptions(defaults.options)
            .setValue(defaults.value);

        this._initialized = true;
    };

    DropdownUpdaterItem.prototype._existsValueInOptions = function (value) {
        var i;
        for (i = 0; i < this._options.length; i += 1) {
            if (this._options[i].value === value) {
                return true;
            }
        }
        return false;
    };

    DropdownUpdaterItem.prototype._getFirstAvailabelValue = function () {
        return (this._options[0] && this._options[0].value) || "";
    };

    DropdownUpdaterItem.prototype.setValue = function (value) {
        if (this._options) {
            if (!(typeof value === 'string' || typeof value === 'number')) {
                throw new Error("setValue(): The parameter must be a string.");
            }
            if (isInDOM(this._control)) {
                this._setValueToControl(value);
                this._value = this._getValueFromControl();    
            } else {
                if (this._existsValueInOptions(value)) {
                    this._value = value;
                } else {
                    this._value = this._getFirstAvailabelValue();
                }
            }
        }
        return this;
    };

    DropdownUpdaterItem.prototype._paintItem = function (option) {
        var optionHTML;
        optionHTML = this.createHTMLElement('option');
        optionHTML.textContent = optionHTML.label = option.text;
        optionHTML.value = optionHTML.value;
        this._control.appendChild(optionHTML);
        return this;
    };

    DropdownUpdaterItem.prototype._paintItems = function () {
        var i;
        if (this._control) {
            jQuery(this._control).empty();
            for (i = 0; i < this._options.length; i += 1) {
                this._paintItem(this._options[i]);
            }
        }
        return this;
    };

    DropdownUpdaterItem.prototype.addOption = function (option) {
        var newOption;
        if (typeof option === 'string' || typeof option === 'number') {
            newOption = {
                text: option,
                value: option
            };
        } else {
            newOption = {
                text: option.text || option.value,
                value: option.value || option.text
            };
        }
        this._options.push(newOption);
        if (!this._massiveAction && this.html) {
            this._paintItem(newOption);
        }
        return this;
    };

    DropdownUpdaterItem.prototype.clearOptions = function () {
        this._options = [];
        if (this._control) {
            jQuery(this._control).empty();
        }
        return this;
    };

    DropdownUpdaterItem.prototype.setOptions = function (options) {
        var i;
        if (!jQuery.isArray(options)) {
            throw new Error("setOptions(): The parameter must be an array.");
        }
        this._massiveAction = true;
        this.clearOptions();
        for (i = 0; i < options.length; i += 1) {
            this.addOption(options[i]);
        }
        this._massiveAction = false;
        this._paintItems();
        if (this._initialized) {
            this.setValue(this._value);
        }
        return this;
    };

    DropdownUpdaterItem.prototype._createConfigButton = function () {
        return null;
    };

    DropdownUpdaterItem.prototype._createControl = function () {
        if (!this._control) {
            this._control = this.createHTMLElement('select');
        }
        return UpdaterItem.prototype._createControl.call(this);
    };

    DropdownUpdaterItem.prototype.createHTML = function () {
        if (!this.html) {
            UpdaterItem.prototype.createHTML.call(this);
            this._paintItems();
            this.setValue(this._value);
        }
        return this.html;
    };

/**
 * @class Form
 * Handles form panels
 * @extend Panel
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var NotePanel = function (options) {
    Panel.call(this, options);

    /**
     * Defines if the form has a proxy
     * @type {Boolean}
     */
    this.proxyEnabled = null;

    /**
     * Defines the form's url
     * @type {String}
     */
    this.url = null;

    /**
     * Defines the form's proxy object
     * @type {Proxy}
     */
    this.proxy = null;
    /**
     * Defines the form loading state
     * @type {Boolean}
     */
    this.loaded = false;
    /**
     * Defines the form's data
     * @type {Object}
     */
    this.data = null;
    /**
     * Defines the callback functions
     * @type {Object}
     */
    this.callback = {};
    /**
     * Defines the dirty form state
     * @type {Boolean}
     */
    this.dirty = false;

    this.buttons = [];

    this.footerAlign = null;

    this.labelWidth = null;

    this.footerHeight = null;

    this.headerHeight = null;

    this.closeContainerOnSubmit = null;

    this.parent = null;

    NotePanel.prototype.initObject.call(this, options);
};

NotePanel.prototype = new Panel();

/**
 * Defines the object's type
 * @type {String}
 */
NotePanel.prototype.type = 'NotePanel';

/**
 * Initializes the object with the default values
 */
NotePanel.prototype.initObject = function (options) {
    var defaults = {
        url: null,
        data: null,
        proxyEnabled: true,
        callback: {},
        buttons: [],
        footerAlign: 'center',
        labelWidth: '30%',
        footerHeight: 10,
        headerHeight: 0,
        closeContainerOnSubmit: false,
        logType: 'message',
        caseId: null,
        caseIndex: null

    };
    $.extend(true, defaults, options);
    this.setUrl(defaults.url)
        .setCallback(defaults.callback);
    this.caseId = defaults.caseId;
    this.caseIndex = defaults.caseIndex;
//        .setLabelWidth(defaults.labelWidth)
//        .setFooterAlign(defaults.footerAlign);
//        .setLogType(defaults.logType);

};

/**
 * Sets the form's url
 * @param {String} url
 * @return {*}
 */
NotePanel.prototype.setUrl = function (url) {
    this.url = url;
    return this;
};

/**
 * Sets the Proxy Enabled property
 * @param {Boolean} value
 * @return {*}
 */
//NotePanel.prototype.setProxyEnabled = function (value) {
//    this.proxyEnabled = value;
//    return this;
//};

/**
 * Defines the proxy object
 * @param {Proxy} proxy
 * @return {*}
 */
//NotePanel.prototype.setProxy = function (proxy) {
//    if (proxy && proxy.family && proxy.family === 'Proxy') {
//        this.proxy = proxy;
//        this.url = proxy.url;
//        this.proxyEnabled = true;
//    } else {
//        if (this.proxyEnabled) {
//            if (proxy) {
//                if (!proxy.url) {
//                    proxy.url = this.url;
//                }
//                this.proxy = new Proxy(proxy);
//            } else {
//                if (this.url) {
//                    this.proxy = new Proxy({url: this.url});
//                }
//            }
//        }
//    }
//    return this;
//};

/**
 * Defines the form's data object
 * @param {Object} data
 * @return {*}
 */
//NotePanel.prototype.setData = function (data) {
//    this.data = data;
//    if (this.loaded) {
//        this.applyData();
//    }
//    return this;
//};

/**
 * Sets the form's callback object
 * @param {Object} cb
 * @return {*}
 */
NotePanel.prototype.setCallback = function (cb) {
    this.callback = cb;
    return this;
};

//NotePanel.prototype.setFooterAlign = function (position) {
//    this.footerAlign = position;
//    return this;
//};
//
//NotePanel.prototype.setLabelWidth = function (width) {
//    this.labelWidth = width;
//    return this;
//};

//NotePanel.prototype.setFooterHeight = function (width) {
//    this.footerHeight = width;
//    return this;
//};
//
//NotePanel.prototype.setHeaderHeight = function (height) {
//    this.headerHeight = height;
//    return this;
//};

//NotePanel.prototype.setCloseContainerOnSubmit = function (value) {
//    this.closeContainerOnSubmit = value;
//    return this;
//};
//NotePanel.prototype.setLogType = function (type) {
//    this.logType = type;
//    return this;
//};
/**
 * Loads the form
 */
NotePanel.prototype.load = function () {
    if (!this.loaded) {
        if (this.proxy) {
            this.data = this.proxy.getData();
        }
        if (this.callback.loaded) {
            this.callback.loaded(this.data, this.proxy !== null);
        }
        //this.applyData();
        this.attachListeners();
        this.loaded = true;
    }
};

/**
 * Reloads the form
 */
//
//HistoryPanel.prototype.reload = function () {
//    this.loaded = false;
//    this.load();
//};

/**
 * Applies the data to the form
 */
//HistoryPanel.prototype.applyData = function (dontLoad) {
//    var propertyName, i, related;
//    if (this.data) {
//        if (this.data.related) {
//            for (i = 0; i < this.items.length; i += 1) {
//                if (this.items[i].getType() === 'ComboboxField' && this.items[i].related) {
//                    related = this.items[i].related;
//                    if (this.data.related[related]) {
//                        this.items[i].setOptions(this.data.related[related]);
//                    }
//                }
//            }
//        }
//        for (propertyName in this.data) {
//            for (i = 0; i < this.items.length; i += 1) {
//                if (this.items[i].name === propertyName) {
//                    this.items[i].setValue(this.data[propertyName]);
//                    break;
//                }
//            }
//        }
//    }
//    if (this.callback.loaded && !dontLoad) {
//        this.callback.loaded(this.data, this.proxy !== null);
//    }
//};

/**
 * Add Fields Items
 * @param {Object/Field}item
 */
NotePanel.prototype.addLog = function (options) {
    var html,
        newItem,
        buttonAnchor,
        proxy,
    //that = this;

        newItem = new LogField(options);

    newItem.setParent(this);
    newItem.logId = options.logId;
    html = newItem.createHTML();

    buttonAnchor = this.createHTMLElement('a');
    buttonAnchor.id = 'deleteNoteBtn';
    buttonAnchor.innerHTML = 'Delete';

    newItem.durationSection.appendChild(buttonAnchor);
    newItem.deleteControl = buttonAnchor;

    $(buttonAnchor).click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        proxy = new SugarProxy({
            //url: SUGAR_URL + '/rest/v10/Log/',
            url: 'pmse_Inbox/delete_notes/'+newItem.logId,
//              restClient: restClient,
//                 uid : root.caseId,
            callback: null
        });
        proxy.removeData (null, {
            success: function (data) {
                $(newItem.html).remove();
                App.alert.dismiss('upload');
            }
        });


        //console.log(newItem);

    });

    this.body.appendChild(html);
    this.items.push(newItem);

    if (options.callback) options.callback.success();




    return this;
};


/**
 * Sets the items
 * @param {Array} items
 * @return {*}
 */
//HistoryPanel.prototype.setItems = function (items) {
//    var i;
//    for (i = 0; i < items.length; i += 1) {
//        this.addItem(items[i]);
//    }
//    return this;
//};


/**
 * Returns the data
 * @return {Object}
 */
NotePanel.prototype.getData = function () {
    var i, result = {};
    for (i = 0; i < this.items.length; i += 1) {
        $.extend(result, this.items[i].getObjectValue());
    }
    return result;
};

/**
 * Sets the dirty form property
 * @param {Boolean} value
 * @return {*}
 // */
NotePanel.prototype.setDirty = function (value) {
    this.dirty = value;
    return this;
};



NotePanel.prototype.attachListeners = function () {
    var i, root = this, proxy, data;


    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].attachListeners();
    }
//    for (i = 0; i < this.buttons.length; i += 1) {
//        this.buttons[i].attachListeners();
//    }
    //$(this.footer).draggable( "option", "disabled", true);
    $(this.body).mousedown(function (e) {
        e.stopPropagation();
    });
    $(this.addNoteBtn).click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        //alert('hello add note btn');
//        console.log('hello add note btn');
//        console.log(root.items[0].value);
        var pictureUrl = App.api.buildFileURL({
            module: 'Users',
            id: App.user.id,
            field: 'picture'
        });
        var f = new Date();
        if (root.items[0].value && root.items[0].value.trim()!=='') {

//            console.log(root.items[0].value);
            data = {
                not_content: root.items[0].value,
                cas_id: root.caseId,
                cas_index:root.caseIndex,
                not_user_id: 1
            };

            proxy = new SugarProxy({
                //url: SUGAR_URL + '/rest/v10/Log/',
                url: 'pmse_Inbox/save_notes/',
//              restClient: restClient,
//                 uid : root.caseId,
                callback: null
            });
            proxy.createData(data, {
                success: function (result) {
                   var newLog = {
                        name: 'log' ,
                        label: root.items[0].value,
                        user: App.user.attributes.full_name,
                        picture : pictureUrl,
                        duration : '5 Second',
                        startDate: Date.parse(result.date_entered).toString('MMMM d, yyyy HH:mm'),
                        deleteBtn : true,
                        logId  : result.id
                    };
                    root.addLog(newLog);
                    root.items[0].setValue('');
                    App.alert.dismiss('upload');
                }
            });

        }

    });

};



NotePanel.prototype.setHeight = function (height) {
    var bodyHeight;
    Panel.prototype.setHeight.call(this, height);
    bodyHeight = this.height - this.footerHeight - this.headerHeight;
    this.setBodyHeight(bodyHeight);
    return this;
};

NotePanel.prototype.createHTML = function () {
    var i, footerHeight, html, buttonAnchor, labelSpan;
    Panel.prototype.createHTML.call(this);
    this.footer.style.textAlign = this.footerAlign;
    for (i = 0; i < this.items.length; i += 1) {
        this.items[i].setParent(this);
        html = this.items[i].getHTML();

        buttonAnchor = this.createHTMLElement('a');
        buttonAnchor.href = '#';
        buttonAnchor.className = 'adam-button';
        buttonAnchor.id = 'noteBtn';

        labelSpan = this.createHTMLElement('span');
        labelSpan.className = 'adam-button-label';
        labelSpan.innerHTML = 'Add Note';
        buttonAnchor.appendChild(labelSpan);

        html.appendChild(buttonAnchor);
        this.addNoteBtn = buttonAnchor;

        html.removeChild(html.firstChild);

        this.body.appendChild(html);

    }


    this.body.style.bottom = '8px';
    //this.footer.style.height = this.footerHeight + 'px';
    return this.html;
};

NotePanel.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

NotePanel.prototype.getLogField = function (id) {
    var field = null, i;
    for (i = 0; i < this.items.length; i += 1) {
        if (this.items[i].id === id) {
            field = this.items[i];
            return field;
        }
    }
    return field;
};
/**
 * @class LabelField
 * Handles the Label fields
 * @extend Field
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 * @param {Form} parent
 */
var ReassignField = function (options, parent) {
    Field.call(this, options, parent);
    this.submit = false;
    this.items = [];
    this.comboId = null;
    ReassignField.prototype.initObject.call(this, options);
    $.extend(true, this.defaults, options);
};
ReassignField.prototype = new Field();

/**
 * Defines the object's type
 * @type {String}
 */
ReassignField.prototype.type = 'ReassignField';

ReassignField.prototype.initObject = function (options) {
    var defaults = {
        marginLeft : 50,
        timeTextSize: 11,
        picture : '/img/default_user.png',
        user: '',
        message: 'default message',
        items : [],
        startDate: '3 July 2013',
        duration: null,
        completed: false,
        options: [],
        comboId: 'comboboxID'
    };
    this.act_name = options.act_name;
    this.cas_delegate_date = options.cas_delegate_date;
    this.cas_due_date = options.cas_due_date;
    this.cas_index = options.cas_index;
    this.defaultValue = options.defaultValue;
    this.act_expected_time = options.act_expected_time;
    $.extend(true, defaults, options);
    this.setMarginLeft(defaults.marginLeft)
        .setPicture(defaults.picture)
        .setTask(defaults.task)
        .setTimeTextSize(defaults.timeTextSize)
        .setStartDate(defaults.startDate)
        .setMessage(defaults.message)
        .setDuration(defaults.duration)
        .setItems(defaults.items)
        .setCompleted(defaults.completed)
        .setOptions(defaults.options)
        .setComboId(defaults.comboId);
};



ReassignField.prototype.setMarginLeft = function (marginLeft) {
    this.marginLeft = marginLeft;
    return this;
};
ReassignField.prototype.setPicture = function (picture) {
    this.picture = picture;
    return this;
};
ReassignField.prototype.setTask = function (task) {
    this.task = task;
    return this;
};
ReassignField.prototype.setTimeTextSize = function (size) {
    this.timeTextSize = size;
    return this;
};
ReassignField.prototype.setStartDate = function (date) {
    this.startDate = date;
    return this;
};
ReassignField.prototype.setMessage = function (msg) {
    this.message = msg;
    return this;
};
ReassignField.prototype.setDuration = function (time) {
    this.duration = time;
    return this;
};
ReassignField.prototype.setItems = function (items) {
    this.items = items;
    return this;
};
ReassignField.prototype.setCompleted = function (val) {
    this.completed = val;
    return this;
};
ReassignField.prototype.setOptions = function (options) {
    this.options = options;
    return this;
};
ReassignField.prototype.setComboId = function (id) {
    this.comboId = id;
    return this;
};

/**
 *
 * @param table
 * @param {Array} headers Array of JSON, each contains a property called 'text' used
 * to create the header of the table
 */
ReassignField.prototype.createTableHeaders = function () {
    var i, detailDiv, newsItem, table;
    table = this.createHTMLElement('div');
    table.style.fontSize = "12px";
    table.style.display = 'table';
    table.style.width = '97%';

    for (i = 0; i < this.parent.columns.length; i += 1){
        detailDiv = this.createHTMLElement('div');
        detailDiv.style.width = '20%';
        detailDiv.style.display = 'table-cell';
        newsItem = this.createHTMLElement('p');
        newsItem.innerHTML = '<strong>' + this.parent.columns[i] + '</strong> ';
        detailDiv.appendChild(newsItem);
        table.appendChild(detailDiv);
    }
    this.html.appendChild(table);
    this.parent.hasHeaders = true;

};

/**
 * Creates the HTML Element of the field
 */
ReassignField.prototype.createHTML = function () {
    var fieldLabel, logPicture, newsItem, datetime, detailDiv, selectDiv, selectInput, i, table;
    Field.prototype.createHTML.call(this);

    if (!this.parent.hasHeaders) {
        this.createTableHeaders();
    }

    table = this.createHTMLElement('div');
    table.style.fontSize = "12px";
    table.style.display = 'table';
    table.style.width = '97%';

    detailDiv = this.createHTMLElement('div');
    detailDiv.style.width = '20%';
    detailDiv.style.display = 'table-cell';
    detailDiv.style.marginLeft = '5px';
    newsItem = this.createHTMLElement('p');
    newsItem.innerHTML =  this.act_name;
    detailDiv.appendChild(newsItem);
    table.appendChild(detailDiv);

    detailDiv = this.createHTMLElement('div');
    detailDiv.style.width = '20%';
    detailDiv.style.display = 'table-cell';
    detailDiv.style.marginLeft = '5px';
    newsItem = this.createHTMLElement('p');
    newsItem.innerHTML = this.cas_delegate_date;
    detailDiv.appendChild(newsItem);
    table.appendChild(detailDiv);

    detailDiv = this.createHTMLElement('div');
    detailDiv.style.width = '20%';
    detailDiv.style.display = 'table-cell';
    detailDiv.style.marginLeft = '5px';
    newsItem = this.createHTMLElement('p');
    newsItem.innerHTML = this.act_expected_time;
    detailDiv.appendChild(newsItem);
    table.appendChild(detailDiv);

    detailDiv = this.createHTMLElement('div');
    detailDiv.style.width = '20%';
    detailDiv.style.display = 'table-cell';
    detailDiv.style.marginLeft = '5px';
    newsItem = this.createHTMLElement('p');
    newsItem.innerHTML = this.cas_due_date;
    detailDiv.appendChild(newsItem);
    table.appendChild(detailDiv);


    selectDiv = this.createHTMLElement('div');
    selectDiv.style.width = '20%';
//    selectDiv.style.paddingLeft = '15px';
    selectDiv.style.display = 'table-cell';
    detailDiv.style.marginLeft = '5px';
    //durationDiv.style.height = '100%';
//    selectDiv.style.color = '#707070';
    selectDiv.style.fontSize = this.timeTextSize + "px";
    //durationDiv.innerHTML =  '<p> ' + this.duration + '</p>';


    selectInput = this.createHTMLElement('select');
    selectInput.id = this.comboId;
    for (i = 0; i < this.options.length; i += 1) {
        selectInput.appendChild(this.generateOption(this.options[i]));
    }
    this.control = selectInput;
//    selectInput.id = this.name;
    selectDiv.appendChild(selectInput);
    table.appendChild(selectDiv);

    this.html.appendChild(table);

    return this.html;
};

ReassignField.prototype.generateOption = function (item) {
    var out, selected = '', value, text;
    out = this.createHTMLElement('option');
    if (typeof item === 'object') {
        value = item.value;
        text = item.text;
    } else {
        value = item;
    }
    out.selected = this.defaultValue === value;
    out.value = value;
    out.label = text || value;
    out.appendChild(document.createTextNode(text || value));
    return out;
};


ReassignField.prototype.attachListeners = function () {
    var id, logPanel, logBefore, logMidle, that;
    that = this;

};
ReassignField.prototype.createDifList = function (type) {
    var logDiv, log, c = '', i, related;
    logDiv = this.createHTMLElement('div');
    logDiv.style.width = '45%';
    logDiv.style.position = 'relative';

    logDiv.style.cssFloat = 'left';
    logDiv.style.backgroundColor = (type === 'before') ? '#fdd' : '#cfc';
    for (i = 0; i < this.items.length; i += 1) {
        related = this.items[i];
        log = this.createHTMLElement('p');
        c = (type === 'before') ? '-' : '+';
        c += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        c += related.field + ': ';
        c += (type === 'before') ? related.before : related.after;
        log.innerHTML = c;
        $(logDiv).append(log);

    }
    return logDiv;
};

ReassignField.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

ReassignField.prototype.getObjectValue = function () {
    var response = {};
    response['cas_id'] = this.comboId;
    response['cas_user_id'] = this.control.value;
    response['cas_index'] = this.cas_index;
    response['old_cas_user_id'] = this.defaultValue;
    return response;
};
/*globals Panel, $, Proxy, TextField, ComboboxField, HiddenField, EmailPickerField, ItemMatrixField, MultipleItemField,
    CriteriaField, ItemUpdaterField, ExpressionField, TextareaField, CheckboxField, Button, RadiobuttonField */
/**
 * @class ReassignForm
 * Handles form panels
 * @extend Panel
 *
 * @constructor
 * Creates a new instance of the object
 * @param {Object} options
 */
var ReassignForm = function (options) {
    Form.call(this, options);


    ReassignForm.prototype.initObject.call(this, options);
};

ReassignForm.prototype = new Form();

/**
 * Defines the object's type
 * @type {String}
 */
ReassignForm.prototype.type = 'ReassignForm';

/**
 * Initializes the object with the default values
 */
ReassignForm.prototype.initObject = function (options) {
    var defaults = {
        columns: []
    };
    this.hasHeaders = false;
    $.extend(true, defaults, options);
    this.setColumns(defaults.columns);
};

ReassignForm.prototype.setColumns = function (columns) {
  this.columns = columns;
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
	/*this._fieldToFilter = null;*/
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
		fieldToFilter: null
	};

	jQuery.extend(true, defaults, settings);

	this._proxy = new SugarProxy();
	this._autoload = defaults.autoload;

	this.setItemsContent(defaults.itemsContent)
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

ListPanel.prototype.setOnLoadHandler = function (handler) {
	if (!(handler === null || typeof handler === 'function')) {
		throw new Error("onLoadHandler(): The parameter must be a functoin or null.");
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
	var filteredData = [], i, validationFunction = false;

	if (jQuery.isArray(filter) && filter.length) {
		validationFunction = function (data) {
			var i = 0;
			return filter.indexOf(data[fieldToFilter]) >= 0;
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

var MultipleItemField = function (settings, parent) {
	Field.call(this, settings, parent);
	this._panel = null;
	this._onValueAction = null;
	this._panelAppended = false;
	this._panelSemaphore = false;
	this._proxy = new SugarProxy();
};

MultipleItemField.prototype = new Field();
MultipleItemField.prototype.constructor = MultipleItemField;
MultipleItemField.prototype.type = "MultipleItemField";
/**
 * The function which processes the text for the items to be added to the field.
 * @abstract
 * @return {Function|null} The function which must return a string or an HTML Element to be used as the text for the 
 * items to be added.
 */
MultipleItemField.prototype._onItemSetText = function () {
	return function () {
		return "[MultipleItemField Item]";
	};
};

MultipleItemField.prototype._createItemData = function (rawData) {
	return rawData;
};

MultipleItemField.prototype._createItem = function (data, usableItem) {
	var newItem;

	if(usableItem instanceof SingleItem) {
		newItem = usableItem;
	} else {
		newItem = new SingleItem();
	}
	newItem.setFullData(this._createItemData(data));
	newItem.setText(this._onItemSetText());
	return newItem;
};

MultipleItemField.prototype.addItem = function (item, noFocus) {
	this.controlObject.addItem(this._createItem(item), null, noFocus);
	return this;
};

MultipleItemField.prototype._setValueToControl = function (value) {
	var i;
	value = value || [];
	value = typeof value ===  'string' ? JSON.parse(value) : value;
	if (!jQuery.isArray(value)) {
		throw new Error("setValue(): The parameter is incorrectly formatted.");
	}
	for (i = 0; i < value.length; i += 1) {
		this.addItem(value[i], true);
	}
	return this;
};

MultipleItemField.prototype._onChange = function () {
	var that = this;
	return function	() {
		var newValue = that._getValueFromControls(), currentValue = that.value;
		if(newValue !== currentValue) {
			that.value = newValue;
			that.onChange(that.value, currentValue);
		}
	};
};

MultipleItemField.prototype.isPanelOpen = function () {
	return this._panel.isOpen();
};

MultipleItemField.prototype.openPanel = function () {
	var parent;
	if (!this.isPanelOpen()) {
		this._panel.open();
		this.controlObject.style.addClasses(['focused']);
		this._panel.style.addClasses(['focused']);
	}
	return this;
};

MultipleItemField.prototype.closePanel = function () {
	this._panel.close();
	this.controlObject.style.removeClasses(['focused']);
	this._panel.style.removeClasses(['focused']);
	return this;
};

MultipleItemField.prototype._getValueFromControls = function () {
	var value = this.controlObject.getData();
	return JSON.stringify(value);
};
/**
 * Valid the text input.
 * @abstract
 * @return {Function|null} The function must return true or false.
 */
MultipleItemField.prototype._isValidInput = function () {
	return null;
};
/**
 * Actions to perform before add an item by text input.
 * @abstract
 * @return {Function|null} The function to execute before the new item be added.
 */
MultipleItemField.prototype._onBeforeAddItemByInput = function () {
	return null;
};
/**
 * Action to perform when the panel fires a value action.
 * @abstract
 * @return {Function|null} The function to be executed when a panel's value action occurs.
 */
MultipleItemField.prototype._onPanelValueGeneration = function () {
	return null;
};

MultipleItemField.prototype.getObject = function () {
	var i, items = this.controlObject.getItems(), obj = [];
	for (i = 0; i < items.length; i += 1) {
		obj.push(items[i].getData());
	}
	return obj;
};

MultipleItemField.prototype._createPanel = function () {
	var that = this;
	if (this.html) {
		if(!this._panel) {
			throw new Error("_createPanel(): This method must be called from an overwritten _createdMethod() method in any subclasses after creatinf the panel.");
		} else if(!(this._panel instanceof FieldPanel)) {
			throw new Error("_createPanel(): The panel created must be an instance of FieldPanel.");
		}
		this._panel.setAppendTo(function () {
			var parent = (that.parent && that.parent.parent) || null;
			return parent ? parent.html : document.body;
		});
		this._panel.setOwner(this.controlObject).close();
		this._panel.setOnItemValueActionHandler(this._onPanelValueGeneration());
	}
	return this;
};

MultipleItemField.prototype.scrollTo = function () {
    var fieldsDiv = this.html.parentNode, 
    	scrollForControlObject = getRelativePosition(this.controlObject.html, fieldsDiv).top + $(this.controlObject.html).outerHeight() + fieldsDiv.scrollTop,
    	that = this;
    if (fieldsDiv.scrollTop + $(fieldsDiv).outerHeight() < scrollForControlObject) {
        jQuery(this.html.parentNode).animate({
        	scrollTop: scrollForControlObject
        }, function() {
        	that.openPanel();
        });
        return;
    }

    return this;
};

MultipleItemField.prototype._attachListeners = function () {
	var that = this;
	if(this.html) {
		jQuery(this._panel.getHTML()).on('mousedown', function (e) {
			e.stopPropagation();
			//that.controlObject.select();
			that._panelSemaphore = true;
		});

		$(this.parent && this.parent.body).on('scroll', function () {
			that.closePanel();
		});
	}
	return this;
};

MultipleItemField.prototype.evalRequired = function () {
	var response = true, value;
    if (this.required) {
        response = !!this.controlObject.getItems().length;
        if (!response) {
            this.controlObject.style.addClasses(['required']);//$(this.controlObject).addClass('required');
        } else {
            this.controlObject.style.removeClasses(['required']);//$(this.controlObject).removeClass('required');
        }
    }
    return response;
};

MultipleItemField.prototype.clear = function () {
	if (this.controlObject) {
		this.controlObject.clearItems();
	}
	this.value = this._getValueFromControls();
	this.isValid();
	return this;
};

MultipleItemField.prototype._createItemContainer = function () {
	var itemsContainer, that = this;
	if (!this.controlObject) {
		itemsContainer = new ItemContainer({
	    	className: "adam-field-control",
	    	onAddItem: this._onChange(),
	    	onRemoveItem: this._onChange(),
	    	width: this.fieldWidth || 200,
	    	textInputMode: ItemContainer.prototype.textInputMode.ALL,
	    	inputValidationFunction: this._isValidInput(),
	    	onBeforeAddItemByInput: this._onBeforeAddItemByInput(),
	    	onBlur: function() {
	    		if (!that._panelSemaphore) {
	    			that.closePanel();
	    		} /*else {
	    			this.select(this.getSelectedIndex());
	    		}*/
	    		that._panelSemaphore = false;
	    	},
	    	onFocus: function() {
	    		that.scrollTo();
	    		if(!that._panel.isOpen()) {
	    			that.openPanel();
	    		}
	    	}
	    });
	    this.controlObject = itemsContainer;
	    this._setValueToControl(this.value);
	}
	return this;
};

MultipleItemField.prototype.createHTML = function () {
	var fieldLabel, required = '', readAtt, that = this;
	if (!this.html) {
	    Field.prototype.createHTML.call(this);

	    if (this.required) {
	        required = '<i>*</i> ';
	    }

	    fieldLabel = this.createHTMLElement('span');
	    fieldLabel.className = 'adam-form-label';
	    fieldLabel.innerHTML = this.label + ': ' + required;
	    fieldLabel.style.width = (this.parent && this.parent.labelWidth) || "30%";
	    fieldLabel.style.verticalAlign = 'top';
	    this.html.appendChild(fieldLabel);

	    if (this.readOnly) {
	        //TODO: implement readOnly!!!!!
	    }
	    this._createItemContainer().html.appendChild(this.controlObject.getHTML());

	    this._createPanel();

	    if (this.errorTooltip) {
	        this.html.appendChild(this.errorTooltip.getHTML());
	    }
	    if (this.helpTooltip) {
	        this.html.appendChild(this.helpTooltip.getHTML());
	    }

	    this.labelObject = fieldLabel;
	    this._attachListeners();
	}
	return this.html;
};
var EmailPickerField = function (settings, parent) {
	MultipleItemField.call(this, settings, parent);
	this._teams = null;
	this._teamsPanel = null;
	/*this._fieldsPanel = null;*/
	this._suggestPanel = null;
	this._suggestTimer = null;
	this._delaySuggestTime = null;
	this._suggestionDataURL = null;
	this._suggestionDataRoot = null;
	this._suggestionItemName = null;
	this._suggestionItemAddress = null;
	this._relatedModulesFieldsDataURL = null;
	this._relatedModulesFieldsDataRoot = null;
	this._suggestionVisible = false;
	this._teamNameField = null;
	this._lastQuery = null;
	EmailPickerField.prototype.init.call(this, settings);
};

EmailPickerField.prototype = new MultipleItemField();
EmailPickerField.prototype.constructor = EmailPickerField;
EmailPickerField.prototype.type = 'EmailPickerField';

EmailPickerField.prototype.init = function (settings) {
	var defaults = {
		teams: [],
		delaySuggestTime: 500,
		suggestionDataURL: null,
		suggestionDataRoot: null,
		suggestionItemName: null,
		suggestionItemAddress: "email"/*,
		relatedModulesFieldsDataURL: null,
		relatedModulesFieldsDataRoot: null*/,
		teamNameField: "name"
	};

	jQuery.extend(true, defaults, settings);

	this._lastQuery = {};

	this.setTeamNameField(defaults.teamNameField)
		.setTeams(defaults.teams)
		.setSuggestionDataURL(defaults.suggestionDataURL)
		.setSuggestionDataRoot(defaults.suggestionDataRoot)
		.setDelaySuggestTime(defaults.delaySuggestTime)
		.setSuggestionItemName(defaults.suggestionItemName)
		.setSuggestionItemAddress(defaults.suggestionItemAddress)/*
		.setVariables(defaults.variables)
		.setRelatedModulesFieldsDataURL(defaults.relatedModulesFieldsDataURL)
		.setRelatedModulesFieldsDataRoot(defaults.relatedModulesFieldsDataRoot)*/;
};

/*EmailPickerField.prototype.setRelatedModulesFieldsDataURL = function (url) {
	if (!(url === null || typeof url === "string")) {
		throw new Error("setRelatedModulesFieldsDataURL(): The parameter must be a string or null.");
	}
	this._relatedModulesFieldsDataURL = url;
	return this;
};

EmailPickerField.prototype.setRelatedModulesFieldsDataRoot = function (root) {
	if (!(root === null || typeof root === 'string')) {
		 throw new Error("setRelatedModulesFieldsDataRoot(): the parameter must be a string or null.");
	}
	this._relatedModulesFieldsDataRoot = root;
	return this;
};*/

EmailPickerField.prototype.setTeamNameField = function(teamNameField) {
	if (typeof teamNameField !== 'string') {
		throw new Error("setTeamNameField(): The parameter must be a string.");
	}
	this._teamNameField = teamNameField;
	return this;
};

EmailPickerField.prototype.setSuggestionItemName = function(text) {
	if(!(text === null || typeof text === 'string')) {
		throw new Error("setSuggestionItemName(): The parameter must be a string or null.");
	}
	this._suggestionItemName = text;
	return this;
};

EmailPickerField.prototype.setSuggestionItemAddress = function(text) {
	if(!(text === null || (typeof text === 'string' && text !== ""))) {
		throw new Error("setSuggestionItemAddress(): The parameter must be a string different than an empty string.");
	}
	this._suggestionItemAddress = text;
	return this;
};

EmailPickerField.prototype.setSuggestionDataURL = function (url) {
	if (!(url === null || typeof url === "string")) {
		throw new Error("setSuggestionDataURL(): The parameter must be a string or null.");
	}
	this._suggestionDataURL = url;
	return this;
};

EmailPickerField.prototype.setSuggestionDataRoot = function(root) {
	if (!(root === null || typeof root === "string")) {
		throw new Error("setSuggestionDataRoot(): The parameter must be a string or root.");
	}
	this._suggestionDataRoot = root;
	return this;
};

EmailPickerField.prototype.setDelaySuggestTime = function (milliseconds) {
	if (typeof milliseconds !== "number") {
		throw new Error("setDelaySuggestTime(): The parameter must be a number.");
	}
	this._delaySuggestTime = milliseconds;
	return this;
};

EmailPickerField.prototype.setTeams = function (teams) {
	var i;
	if(!jQuery.isArray(teams)) {
		throw new Error("setItems(): The parameter must be an array.");
	}
	this._teams = teams;
	if(this._teamsPanel) {
		this._teamsPanel.setDataItems(this._teams);
		this._teamsPanel.setVisible(this._teams.length);
	}
	return this;
};

EmailPickerField.prototype._onItemSetText = function () {
	return function(itemObject, data) {
		return data.name || data.emailAddress || "";
	};
};

EmailPickerField.prototype._createItemData = function(data) {
	return {
		name: data.name || data.emailAddress || "",
		emailAddress: data.emailAddress || "",
		module: null
	};
};

EmailPickerField.prototype._onBeforeAddItemByInput = function () {
	var that = this;
	return function (itemContainer, singleItem, text, index) {
		return that._createItem({
			emailAddress: text
		}, singleItem);
	}
};

EmailPickerField.prototype._onPanelValueGeneration = function () {
	var that = this;
	return function (fieldPanel, fieldPanelItem, data) {
		var newEmailItemm;
		if(fieldPanelItem.type === "FieldPanelButton") {
			newEmailItem = that._createItem({
				name: data.text,
				emailAddress: data.value
			});
		} else {
			switch(fieldPanelItem.id) {
				case "list-teams":
					newEmailItem = that._createItem({
						emailAddress: "Team",
						name: data[that._teamNameField]
					});
					break;
				default:
					newEmailItem = that._createItem({
						emailAddress: data[that._suggestionItemAddress],
						name: data[that._suggestionItemName || that._suggestionItemAddress] 
					});
			}
		}
		that.controlObject.addItem(newEmailItem, that.controlObject.getSelectedIndex());
	};
};

EmailPickerField.prototype._suggestionItemContent = function() {
	var that = this;
	return function (item, data) {
		var name = that.createHTMLElement('strong'),
			address = that.createHTMLElement('small'),
			container = that.createHTMLElement('a');

		container.href = "#";
		container.className = "adam email-picker-suggest";
		if(that._suggestionItemName) {
			name.className = "adam email-picker-suggest-name";
			name.textContent = data[that._suggestionItemName];
			container.appendChild(name);
		}
		address.className = "adam email-picker-suggest-address";
		address.textContent = data[that._suggestionItemAddress];
		container.appendChild(address);

		return container;
	};
};

EmailPickerField.prototype._onLoadSuggestions = function () {
	var that = this;
	return function (listPanel, data) {
		var replacementText = {
			"%NUMBER%": listPanel.getItems().length,
			"%TEXT%": that._lastQuery.query
		};
		//listPanel.setTitle(listPanel.getItems().length + " suggestion(s) for \"" + that._lastQuery.query + "\"");
		listPanel.setTitle(translate("LBL_PMSE_EMAILPICKER_RESULTS_TITLE").replace(/%\w+%/g, function(wildcard) {
		   return replacementText[wildcard] || wildcard;
		}));
	};
};

EmailPickerField.prototype._createPanel = function () {
	var that = this;
	if (!this._teamsPanel) {
		this._teamsPanel = new ListPanel({
			id: "list-teams",
			title: translate('LBL_PMSE_EMAILPICKER_TEAMS'),
			itemsContent: function(item, data) {
				return data[that._teamNameField] || "";
			}
		});
		this.setTeams(this._teams);
	}
	if (!this._suggestPanel) {
		this._suggestPanel = new ListPanel({
			id: "list-suggest",
			title: translate('LBL_PMSE_EMAILPICKER_SUGGESTIONS'),
			itemsContent: this._suggestionItemContent(),
			visible: false,
			bodyHeight: 150,
			onLoad: this._onLoadSuggestions()
		});
	}
	/*if (!this._fieldsPanel) {
		this._fieldsPanel = new ListPanel({
			id: "list-fields",
			title: "Module Fields",
			bodyHeight: 200,
			onExpand: function (listPanel) {
				listPanel.setDataURL(that._relatedModulesFieldsDataURL)
					.setDataRoot(that._relatedModulesFieldsDataRoot)
					.load();
			}
		});
	}*/
	this._panel = new FieldPanel({
		items: [
			{
				type: 'button',
				value: "Current User",
				text: translate('LBL_PMSE_EMAILPICKER_CURRENT_USER')
			}, 
			{
				type: 'button',
				value: "Record Owner",
				text: translate('LBL_PMSE_EMAILPICKER_RECORD_OWNER')
			}, 
			{
				type: "button", 
				value: "Supervisor",
				text: translate('LBL_PMSE_EMAILPICKER_SUPERVISOR')
			},
			this._teamsPanel,/*
			this._fieldsPanel,*/
			this._suggestPanel
		]
	});
	MultipleItemField.prototype._createPanel.call(this);
	return this;
};

EmailPickerField.prototype._isValidInput = function () {
	var that = this;
	return function (itemContainer, text) {
		return /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/.test(text);
	};
};

EmailPickerField.prototype._loadSuggestions = function (c) {
	var that = this;
	return function	() {
		var url;
		clearInterval(that._timer);
		if(that._suggestionDataURL) {
			that._lastQuery = {
				query: c,
				dataRoot: that._suggestionDataRoot,
				dataURL: that._suggestionDataURL
			};
			url = that._suggestionDataURL.replace(/\{\$\d+\}/g, encodeURIComponent(c));
			that._suggestPanel.setDataURL(url)
				.setDataRoot(that._suggestionDataRoot);
			that._suggestPanel.load();
		}
	};
};

EmailPickerField.prototype._showSuggestionPanel = function () {
	var panelItems = this._panel.getItems(), i;

	if (!this._suggestionVisible) {
		for (i = 0; i < panelItems.length; i += 1) {
			if (panelItems[i] !== this._suggestPanel) {
				panelItems[i].setVisible(false);
			} else {
				panelItems[i].setVisible(true);
			}
		}	
	}
	this._suggestionVisible = true;
	return this;
};

EmailPickerField.prototype._hideSuggestionPanel = function () {
	var panelItems = this._panel.getItems(), i;

	if (this._suggestionVisible) {
		for (i = 0; i < panelItems.length; i += 1) {
			if (panelItems[i] !== this._suggestPanel) {
				panelItems[i].setVisible(true);
			} else {
				panelItems[i].setVisible(false);
			}
		}	
	}
	this._suggestionVisible = false;
	return this;
};

EmailPickerField.prototype._onInputChar = function () {
	var that = this;
	return function (itemContainer, theChar, completeText, keyCode) {
		var trimmedText = jQuery.trim(completeText);
		clearInterval(that._timer);
		if (trimmedText) {
			if (that._suggestionDataURL) {
				//Vefify if the current query is identical than the last one
				if (!(that._lastQuery.query === trimmedText && that._lastQuery.dataURL === that._suggestionDataURL 
					&& that._lastQuery.dataRoot === that._suggestionDataRoot)) {
					that._timer = setInterval(that._loadSuggestions(trimmedText), that._delaySuggestTime);
					that._suggestPanel.clearItems()
						._showLoadingMessage()
						.setTitle(translate("LBL_PMSE_EMAILPICKER_SUGGESTIONS"));
				}
				that._showSuggestionPanel();
				that.openPanel(true);
				that._suggestPanel.expand();
			}/* else {
				that.openPanel();
			}*/
		} else {
			that._hideSuggestionPanel();
		}
	};
};

EmailPickerField.prototype.openPanel = function (showSuggestionPanel) {
	if (!showSuggestionPanel) {
		this._hideSuggestionPanel();
	}
	return MultipleItemField.prototype.openPanel.call(this);
};

EmailPickerField.prototype.createHTML = function () {
	if(!this.html) {
		MultipleItemField.prototype.createHTML.call(this);
		this.controlObject.setOnInputCharHandler(this._onInputChar());
	}
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
var CriteriaField = function (settings, parent) {
	Field.call(this, settings, parent);
	this._panel = null;
	this._panelFlag = true;
	this._listenersAttached = false;
	this.fieldHeight = null;
	this._disabled = null;
	CriteriaField.prototype.init.call(this, settings);
};

CriteriaField.prototype = new Field();
CriteriaField.prototype.constructor = CriteriaField;
CriteriaField.prototype.type = "CriteriaField";

CriteriaField.prototype.init = function(settings) {
	var that = this, defaults = {
		operators: {},
		evaluation: false,
		variable: false,
		constant: true,
		fieldHeight: 88,
		fieldWidth: 200,
		disabled: false,
		dateFormat: "yyyy-mm-dd",
		decimalSeparator: ".",
		numberGroupingSeparator: ","
	};

	jQuery.extend(true, defaults, settings);

	this.controlObject = new ItemContainer({
		className: 'adam-field-control',
		//width: this.fieldWidth || 200,
		//height: 88,
		onFocus: function () {
			that.scrollTo();
    		if(!that._panel.isOpen() && !that._disabled) {
    			that.openPanel();
    		}
		},
		onBlur: function () {
			if (that._panelFlag) {
				that.closePanel();	
			}
			that._panelFlag = true;
		}
	});

	this._panel = new ExpressionControl({
		itemContainer: this.controlObject,
		owner: this.controlObject,
		dateFormat: defaults.dateFormat,
		operators: defaults.operators,
		evaluation: defaults.evaluation,
		variable: defaults.variable,
		constant: defaults.constant,
		decimalSeparator: defaults.decimalSeparator,
		numberGroupingSeparator: defaults.numberGroupingSeparator,
		onChange: this._onChange(),
		appendTo: function () {
			return (that.parent && that.parent.parent && that.parent.parent.html) || document.body;
		}
	});

	this.setEvaluations(defaults.evaluation)
		.setFieldWidth(defaults.fieldWidth)
		.setFieldHeight(defaults.fieldHeight)
		.setValue(defaults.value);

	if (defaults.disabled) {
		this.disable();
	} else {
		this.enable();
	}
};

CriteriaField.prototype.disable = function () {
	this._disabled = true;
	this.controlObject.disable();
	jQuery(this.labelObject).addClass('adam-form-label-disabled');
	return this;
};

CriteriaField.prototype.enable = function () {
	this._disabled = false;
	this.controlObject.enable();
	jQuery(this.labelObject).removeClass('adam-form-label-disabled');
	return this;	
};

CriteriaField.prototype.setFieldWidth = function (width) {
	if(!isNaN(width) && this.controlObject) {
        this.controlObject.setWidth(this.fieldWidth = width);
    }
    return this;
};

CriteriaField.prototype.setFieldHeight = function (height) {
	if(!isNaN(height)) {
        this.controlObject.setHeight(this.fieldHeight = height);
    }
    return this;
};

CriteriaField.prototype.getItems = function () {
	return this.controlObject.getItems();
};

CriteriaField.prototype.setOperators = function (settings) {
	this._panel.setOperators(settings);
	return this;
};

CriteriaField.prototype.setEvaluations = function (settings) {
	this._panel.setEvaluations(settings);
	return this;
};

CriteriaField.prototype.setVariablePanel = function (settings) {
	this._panel.setVariablePanel(settings);
	return this;
};

CriteriaField.prototype.setConstantPanel = function (settings) {
	this._panel.setConstantPanel(settings);
	return this;
};

CriteriaField.prototype.setModuleEvaluation = function (currentEval) {
	this._panel.setModuleEvaluation(currentEval);
	return this;
};

CriteriaField.prototype.setFormResponseEvaluation = function (currentEval) {
	this._panel.setFormResponseEvaluation(currentEval);
	return this;
};

CriteriaField.prototype.setBusinessRuleEvaluation = function (currentEval) {
	this._panel.setBusinessRuleEvaluation(currentEval);
	return this;
};

CriteriaField.prototype.setUserEvaluation = function (currentEval) {
	this._panel.setUserEvaluation(currentEval);
	return this;
};

CriteriaField.prototype.clear = function () {
	this.controlObject.clearItems();
	return this;
};

CriteriaField.prototype._onChange = function () {
	var that = this;
	return function (panel, newValue, oldValue) {
		that.value = newValue;
		that.onChange(newValue, oldValue);
	};
};

CriteriaField.prototype.setValue = function (value) {
	if (this.controlObject) {
		Field.prototype.setValue.call(this, value);
	}
	return this;
};

CriteriaField.prototype._setValueToControl = function (value) {
	var i;
	value = value || [];
	value = typeof value ===  'string' ? JSON.parse(value) : value;
	if (!jQuery.isArray(value)) {
		throw new Error("setValue(): The parameter is incorrectly formatted.");
	}
	for (i = 0; i < value.length; i += 1) {
		this.controlObject.addItem(this._panel._createItem(value[i]), null, true);
	}
	return this;
};

CriteriaField.prototype.closePanel = function () {
	if (this._panel.isPanelOpen()) {
		this._panel.close();
	}
	this.controlObject.style.removeClasses(['focused']);
	this._panel.style.removeClasses(['focused']);
	return this;
};

CriteriaField.prototype.openPanel = function() {
	if (!this._panel.isPanelOpen()) {
		this._panel.open();
		this.controlObject.style.addClasses(['focused']);
		this._panel.style.addClasses(['focused']);
	}
	return this;
};

CriteriaField.prototype.scrollTo = function () {
    var fieldsDiv = this.html.parentNode, 
    	scrollForControlObject = getRelativePosition(this.controlObject.html, fieldsDiv).top + $(this.controlObject.html).outerHeight() + fieldsDiv.scrollTop,
    	that = this;
    if (fieldsDiv.scrollTop + $(fieldsDiv).outerHeight() < scrollForControlObject) {
        jQuery(this.html.parentNode).animate({
        	scrollTop: scrollForControlObject
        }, function() {
        	that.openPanel();
        });
        return;
    }

    return this;
};

CriteriaField.prototype.evalRequired = function () {
	var valid = true;
	if (this.required) {
		valid = !!this.controlObject.getItems().length;
		if (!valid) {
            $(this.controlObject).style.addClasses(['required']);
        } else {
            $(this.controlObject).style.removeClasses(['required']);
        }
	}

	return this;
};

CriteriaField.prototype.isValid = function () {
	var valid = this._panel.isValid();

	if (valid) {
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-on');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-off');
        valid = valid && Field.prototype.isValid.call(this);
    } else {
        this.errorTooltip.setMessage("Invalid epxression syntax.");
        $(this.errorTooltip.html).removeClass('adam-tooltip-error-off');
        $(this.errorTooltip.html).addClass('adam-tooltip-error-on');
    }

    if (valid) {
        return valid && Field.prototype.isValid.call(this);
    }
    return valid;
};

CriteriaField.prototype._attachListeners = function() {
	var that = this;
	if (this.html && !this._listenersAttached) {
		jQuery(this._panel.getHTML()).on('mousedown', function (e) {
			e.stopPropagation();
			that._panelFlag = false;
		});
		if (this.parent) {
			$(this.parent.body).on('scroll', function () {
				that.closePanel();
			});
		}
		this._attachListeners = true;
	}
	return this;
};

CriteriaField.prototype.createHTML = function() {
	var fieldLabel, required = '', readAtt, that = this;
	if (!this.html) {
	    Field.prototype.createHTML.call(this);

	    if (this.required) {
	        required = '<i>*</i> ';
	    }

	    fieldLabel = this.createHTMLElement('span');
	    fieldLabel.className = 'adam-form-label';
	    fieldLabel.innerHTML = this.label + ': ' + required;
	    fieldLabel.style.width = (this.parent && this.parent.labelWidth) || "30%";
	    fieldLabel.style.verticalAlign = 'top';
	    this.html.appendChild(fieldLabel);

	    if (this.readOnly) {
	        //TODO: implement readOnly
	    }
	    this.html.appendChild(this.controlObject.getHTML());

	    if (this.errorTooltip) {
	        this.html.appendChild(this.errorTooltip.getHTML());
	    }
	    if (this.helpTooltip) {
	        this.html.appendChild(this.helpTooltip.getHTML());
	    }

	    this.labelObject = fieldLabel;
	    this._attachListeners();
	}
	return this.html;
};
//@ sourceURL=pmse.ui.js
