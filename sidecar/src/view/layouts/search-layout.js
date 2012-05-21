(function(app) {

    var _meta = {"type": "simple",
        "components": [
            {"view": "subnav"},
            {"layout": {"type": "columns", "components": [
                {"layout": {"type": "leftside", "components": [
                    {"view": "results"}
                ]}},
                {"layout": {"type": "rightside", "components": [
                    {"view": "preview"}
                ]}}
            ]}}
        ]
    };

    app.view.layouts.SearchLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);
        }
    });

})(SUGAR.App);
