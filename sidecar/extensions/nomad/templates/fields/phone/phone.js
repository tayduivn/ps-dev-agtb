(function(app) {

    app.view.fields.PhoneField = app.view.Field.extend({
        events: {
            "click .call": function () {
                app.nomad.callPhone(this.value);
            },
            "click .sms": function () {
                app.nomad.sendSms(this.value);
            }
        }

    });

})(SUGAR.App);