(function(app) {

    app.view.views.PickerlistView = app.view.View.extend({
        events: {
            'click .picker-list-row': 'onRowClick',
            'click .menu-cancel': 'onClickMenuCancel'
        },
        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
            this.data = {};
        },
        _renderSelf:function(){
            this._renderWithContext(this.data,this.options.templateOptions);
        },
        setData:function(data){
            this.data = data;
        },
        onRowClick:function(e){
            //e.preventDefault();
        },
        onClickMenuCancel:function(e){
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);