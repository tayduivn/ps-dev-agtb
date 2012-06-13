(function(app) {

    app.view.fields.BoolField = app.view.Field.extend({

        unformat:function(value){
            value = this.$(this.fieldTag)[0].checked ? "1" : "0";
            return value;
        },

        format:function(value){
            value = (value=="1") ? true : false;
            return value;
        }
    });

})(SUGAR.App);