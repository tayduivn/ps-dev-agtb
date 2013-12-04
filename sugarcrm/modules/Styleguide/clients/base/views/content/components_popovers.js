// components popovers
function _render_content(view, app) {
    this.$('[rel=popover]').popover();
    this.$('[rel=popoverHover]').popover({trigger: 'hover'});
    this.$('[rel=popoverTop]').popover({placement: 'top'});
    this.$('[rel=popoverBottom]').popover({placement: 'bottom'});
}
