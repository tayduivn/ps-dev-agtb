describe("Handlebars Helpers", function() {

    var app = SUGAR.App;

    // TODO: Create test for each helper

    describe("getFieldValue", function() {

        it("should return value for an existing field", function() {
            var bean = new app.Bean({ foo: "bar"});
            expect(Handlebars.helpers.getFieldValue(bean, "foo")).toEqual("bar");
        });

        it("should return empty string for a non-existing field", function() {
            var bean = new app.Bean();
            expect(Handlebars.helpers.getFieldValue(bean, "foo")).toEqual("");
        });

        it("should return default string for a non-existing field", function() {
            var bean = new app.Bean();
            expect(Handlebars.helpers.getFieldValue(bean, "foo", "bar")).toEqual("bar");
        });

    });

    describe("sugarField", function() {

    });

    describe("buildRoute", function() {

    });

    describe("in", function() {

    });

    describe("eachOptions", function() {

    });

    describe("eq", function() {

    });

});