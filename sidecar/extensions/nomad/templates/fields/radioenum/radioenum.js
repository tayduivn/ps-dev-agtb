(function(app) {

    app.view.fields.RadioenumField = app.view.Field.extend({

        events: {
            "click .btn-group .btn": "onClick"
        },

        onClick: function (e) {
            this.model.set(this.name, $(e.currentTarget).val());
        }

    });

})(SUGAR.App);