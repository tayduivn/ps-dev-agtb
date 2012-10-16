describe("sugarviews", function() {
    var view;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'baseedit');

        view = SugarTest.createView("base","Contacts", "edit", null, null);
    });

    describe("baseedit",function() {
        it('should execute handleValidationError method and show error message when error:validation fires', function() {
            var errors = {last_name:{required:true}},
                stub = sinon.spy(view, 'handleValidationError');

            view.model.trigger("error:validation", errors);

            expect(view.handleValidationError.calledOnce);

            stub.restore();
        });
    })
});