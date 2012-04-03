(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    app.layout.ListView = app.layout.View.extend({
        bind: function(context) {
            var collection = context.get("collection");
            _.each(collection.models, function(model) {
                var tr = this.$el.find('tr[name="' + model.module + '_' + model.get("id") + '"]');
                _.each(model.attributes, function(value, field) {
                    var el = tr.find('input[name="' + field + '"],span[name="' + field + '"]');
                    if (el.length > 0) {
                        //Bind input to the model
                        el.on("change", function(ev) {
                            model.set(field, el.val());
                        });
                        //And bind the model to the input
                        model.on("change:" + field, function(model, value) {
                            if (el[0].tagName.toLowerCase() == "input")
                                el.val(value); else
                                el.html(value);
                        });
                    }
                }, this);
            }, this);
        }
    });

})(SUGAR.App);