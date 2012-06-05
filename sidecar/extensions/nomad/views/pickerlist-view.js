(function(app) {

    app.view.views.PickerlistView = app.view.View.extend({
        events: {
            'click .picker-list-row': 'onRowClick',
            'click .menu-cancel': 'onClickMenuCancel'
        },
        initialize: function(options) {
            // Default list item partial
            options.templateOptions = {
                partials: {
                    'list.item': app.template.get("pickerlist.item")
                }
            };
            app.view.View.prototype.initialize.call(this, options);
            this.data = {};
        },
        _renderSelf:function(){
            this._renderWithContext(this.data,this.options.templateOptions);
        },
        setData:function(data){
            this.data = data;
        },
        onRowClick:function(){

        },
        onClickMenuCancel:function(e){
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);