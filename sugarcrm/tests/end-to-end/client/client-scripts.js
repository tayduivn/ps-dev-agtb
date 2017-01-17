module.exports = {

    /**
     * Scroll inside divs element to specific selector
     *
     * @param elementSelector
     */
    scrollToSelector: function (elementSelector) {
        $(elementSelector).get(0).scrollIntoView(false);
    },

};
