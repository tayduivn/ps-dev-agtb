(function(app) {

    app.view.views.ListPickerView = app.view.View.extend({
        events: {
            'click .picker-list-row': 'onRowClick',
            'click .menu-cancel': 'onClickMenuCancel'
        },

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
            this.data = {};
        },

        _renderSelf: function() {
            app.view.View.prototype._renderSelf.call(this, this.data);
        },

        setData: function(data){
            this.data = data;
        },

        onRowClick: function(e){

            if ($(e.target).hasClass('picker-list-row')) {
                var en = $(e.target).attr("entity-name");
                var model = this.context.get('model');

                var route = app.nomad.buildLinkRoute(this.data.module, this.data.modelId, en, this.data.action) + "?depth=2";
                app.router.navigate(route, {trigger: true});
            }
        },

        onClickMenuCancel: function(e){
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);