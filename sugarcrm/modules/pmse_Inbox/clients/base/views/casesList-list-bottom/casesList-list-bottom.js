({
    extendsFrom: "ListBottomView",

    /**
     * Assign proper label for 'show more' link.
     * Label should be "More recipients...".
     */
    setShowMoreLabel: function() {
        this.showMoreLabel = app.lang.get('LBL_SHOW_MORE_CASES', this.module);
    }
})