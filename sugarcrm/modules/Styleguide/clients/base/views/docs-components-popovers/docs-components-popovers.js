({
    className: 'container-fluid',

    // components popovers
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('[rel=popover]').popover();
        this.$('[rel=popoverHover]').popover({trigger: 'hover'});
        this.$('[rel=popoverTop]').popover({placement: 'top'});
        this.$('[rel=popoverBottom]').popover({placement: 'bottom'});
    }
})
