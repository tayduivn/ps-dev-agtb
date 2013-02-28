describe("iframe", function() {
    var app, field;

    beforeEach(function() {
        var def = {
            'default': "http://www.sugarcrm.com/{ONE}"
        };
        app = SugarTest.app;
        field = SugarTest.createField("base","iframe", "iframe", "detail", def);
        field.model = {get : function(key){
            var values = {
                "ONE":"1",
                "TWO":"2"
            };
            return values[key];
        }};
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("iframe", function() {
        it("should add 'http' scheme to URL if http or https scheme is missing", function() {
            expect(field.format("http://www.google.com")).toEqual("http://www.google.com");
            expect(field.format("https://www.google.com")).toEqual("https://www.google.com");
            expect(field.format("www.google.com")).toEqual("http://www.google.com");
        });
        it("should unformat 'http://' to an empty string", function() {
            expect(field.unformat("http://")).toEqual("");
            expect(field.unformat("http://www.google.com")).toEqual("http://www.google.com");
        });
        it("should insert field values into generated URLs", function(){
            field.def.gen = "1";
            expect(field.format("http://{ONE}/{TWO}")).toEqual("http://1/2");
        });
        it("should not modify non-generated URLs", function(){
            expect(field.format("http://{ONE}/{TWO}")).toEqual("http://{ONE}/{TWO}");
        });
        it("should handle default values in format", function(){
            field.def.gen = "1";
            expect(field.format("")).toEqual("http://www.sugarcrm.com/1");
        });
    });
});
