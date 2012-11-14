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

        it("should format an attachment array", function() {
            model.set('testfile', [
                {name:'filename1.jpg', 'uri': '/path/to/rest'},
                {name:'filename2.jpg', 'uri': '/path/to/rest'},
                {name:'filename3.jpg', 'uri': '/path/to/rest'}
            ]);
            var expectedValue = [
                {name:'filename1.jpg', 'url': '/path/to/rest'},
                {name:'filename2.jpg', 'url': '/path/to/rest'},
                {name:'filename3.jpg', 'url': '/path/to/rest'}
            ];
            field._render();
            expect(field.attachments).toEqual(expectedValue);
        });


        it("should format an attachment array", function() {
            model.set('testfile', 'filename1.jpg');
            var expectedValue = [
                {name:'filename1.jpg', 'url': '/path/to/rest'}
            ];
            field._render();
            expect(field.attachments[0].name).toEqual(expectedValue[0].name);
            expect(field.attachments[0].url).not.toEqual(expectedValue[0].url);
        });
    });
});
