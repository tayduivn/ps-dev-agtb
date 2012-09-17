describe("date field", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","date", "date", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("date", function() {
        it("should format the value", function() {
            var myUser = SUGAR.App.user, jsDate, unformatedValue;
            myUser.set('datepref','m/d/Y');
            jsDate = new Date("March 13, 2012")
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue)).toEqual('03/13/2012');
        });
    });
});
