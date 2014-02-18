({
    className: 'container-fluid',

    // layouts modals
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('[rel=popover]').popover();

        this.$('.modal').tooltip({
          selector: '[rel=tooltip]'
        });
        this.$('#dp1').datepicker({
          format: 'mm-dd-yyyy'
        });
        this.$('#dp3').datepicker();
        this.$('#tp1').timepicker();
    }
})
