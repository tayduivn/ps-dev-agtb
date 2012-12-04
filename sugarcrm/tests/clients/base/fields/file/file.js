describe("file field", function() {

    var app, field, model;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","testfile", "file", "detail", {});
        model = field.model;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        model = null;
        field = null;
    });

    describe("file", function() {

        it("should format an array", function() {
            var inputValue = [
                {name:'filename1.jpg', 'uri': '/path/to/rest'},
                {name:'filename2.jpg', 'uri': '/path/to/rest'},
                {name:'filename3.jpg', 'uri': '/path/to/rest'}
            ];
            var expectedValue = [
                {name:'filename1.jpg', 'url': '/path/to/rest'},
                {name:'filename2.jpg', 'url': '/path/to/rest'},
                {name:'filename3.jpg', 'url': '/path/to/rest'}
            ];
            var formattedValue = field.format(inputValue);
            expect(formattedValue).toEqual(expectedValue);
        });


        it("should format a string", function() {
            var inputValue = 'filename1.jpg';
            var expectedValue = [
                {name:'filename1.jpg', 'url': '/path/to/rest'}
            ];
            var formattedValue = field.format(inputValue);
            expect(formattedValue[0].name).toEqual(expectedValue[0].name);
            expect(formattedValue[0].url).not.toEqual(expectedValue[0].url);
        });
    });
});
