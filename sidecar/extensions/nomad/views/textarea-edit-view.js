(function (app) {

    app.view.views.TextareaEditView = app.view.View.extend({

        editElTag: "textarea",

        events: {
            "click #saveTextarea": "save",
            "click #cancelTextarea": "cancel"
        },

        setValue: function (value) {
            this.$(this.editElTag).val(value);
        },

        getValue: function () {
            return this.$(this.editElTag).val();
        },

        save: function () {
            this.trigger("textarea:edit:saved", this.getValue());
            this.cancel();
        },

        cancel: function () {
            this.$el.remove();
        }

    });

})(SUGAR.App);