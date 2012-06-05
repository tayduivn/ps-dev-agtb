(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view: "pickerlist" }
        ]
    };

    app.view.layouts.PickerlistLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);
            var pickerList = this.getComponent('pickerlist');
            pickerList.setTemplateOption("partials", {'list.item': app.template.get("pickerlist.item")});
            //pickerList.setTemplateOption("data", {items:[{name:"zzz"},{name:"yyy"},{name:"mmm"}]});
            pickerList.setData({items:[{name:"zzz"},{name:"yyy"},{name:"mmm"}],
                modelId:this.context.get('modelId'),
                module:this.context.get('module')
            });
        }
    });

})(SUGAR.App);