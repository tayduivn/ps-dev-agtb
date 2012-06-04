(function(app) {

    var _meta = {
        "type": "columns",
        "components": [
            {
                "layout": {
                    "type": "leftside",
                    "components": [
                        {
                            "view": "profile"
                        }
                    ]
                }
            }
            /*
            ,{
                "layout": {
                    "type": "rightside",
                    "components": [
                        {
                            "view": "preview"
                        }
                    ]
                }
            }
            */
        ]
    };
    app.view.layouts.ProfileLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);
        }
    });

})(SUGAR.App);
