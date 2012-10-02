describe("sugarviews", function() {
    var view, app;
    beforeEach(function() {
        app = SUGAR.App;

        var bean = app.data.createBean("Contacts", {
            first_name: "Foo",
            last_name: "Bar"
        });

        view = SugarTest.createView("base","Contacts", "edit", null, bean);
    });

    describe("editview",function() {
        it('should execute handleValidationError method and show error message when error:validation fires', function() {
            var errors, stub;

            stub = sinon.spy(view, 'handleValidationError');
            errors = {last_name:{required:true}};
            view.model.trigger("error:validation", errors);

            expect(view.handleValidationError.calledOnce);
            view.handleValidationError.restore();
        });
    })
});