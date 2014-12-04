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
