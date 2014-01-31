// layouts modals
function _render_content(view, app) {
    view.$('[rel=popover]').popover();

    view.$('.modal').tooltip({
      selector: '[rel=tooltip]'
    });
    view.$('#dp1').datepicker({
      format: 'mm-dd-yyyy'
    });
    view.$('#dp3').datepicker();
    view.$('#tp1').timepicker();
}
