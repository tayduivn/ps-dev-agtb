({
    _renderField: function(field) {
        field.testmodel = this.context.testmodel;

        field.events = {
            "click" : "testFunc"
        };

        field.testFunc = function() {
            debugger;
        };

        field.initialize = function (options) {
            debugger;
            app.view.Field.prototype.initialize.call(this, options);
        };

        app.view.View.prototype._renderField.call(this, field);

    }
})