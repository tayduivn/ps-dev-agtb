describe("image field", function() {

    var app, field, model;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","testimage", "image", "detail", {});
        model = field.model;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        model = null;
        field = null;
    });

    describe("image", function() {

        it("should format value", function() {
            expect(field.format("")).toEqual("");
            expect(field.format("filename3.jpg")).not.toEqual("");
            expect(field.format("filename3.jpg")).not.toEqual("filename3.jpg");
        });

        it("make an api call to delete the image", function() {
            var deleteSpy = sinon.spy(field, "delete");
            $("<a></a>").addClass("delete").appendTo(field.$el);
            field.undelegateEvents();
            field.delegateEvents();

            field.$(".delete").trigger("click");
            expect(deleteSpy).toHaveBeenCalled();
            deleteSpy.restore();
        });

    });
});
