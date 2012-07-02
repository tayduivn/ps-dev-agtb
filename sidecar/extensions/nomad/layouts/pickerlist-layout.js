(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view: "list-picker" }
        ]
    };

    app.view.layouts.PickerlistLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);
            var pickerList = this.getComponent('list-picker');

            pickerList.setData({items:app.nomad.getLinks(this.model),
                modelId:this.context.get('modelId'),
                module:this.context.get('module'),
                context:this.context,
                action:this.context.get('action')
            });
        }
    });

})(SUGAR.App);