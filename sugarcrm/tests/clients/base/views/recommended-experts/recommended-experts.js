describe("Recommended Experts view", function() {
    var app,
        view,
        sandbox,
        moduleName = 'Contacts',
        callStub;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        // need to get through initialize =(
        sandbox.stub(app.controller.context,'get').withArgs('model').returns({id: '1'});
        callStub = sandbox.stub(app.api,'call'); // $.typeahead

        view = SugarTest.createView("base", moduleName, "recommended-experts", null, null);
    });

    afterEach(function() {
        view.dispose();
        sandbox.restore();
        view = null;
    });

    describe("Render", function() {
        var renderStub;

        beforeEach(function() {
            renderStub = sinon.stub(view, 'render');
            callStub.yieldsTo('success', [200, {}]);

            sandbox.stub(view,'$', function(dummy) {
                return {
                    val: function() {
                        return 'Job Title';
                    }
                };
            });

        });

        afterEach(function() {
            renderStub.restore();
        });

        it("Should render if it is not disposed", function() {
            view.getRecommendations(); // will try to call render
            expect(renderStub.called).toBeTruthy();
        });
        it("Should not render if it is disposed", function() {
            sandbox.stub(app.view.Component.prototype, '_dispose');
            view.dispose();
            view.getRecommendations(); // will try to call render
            expect(renderStub.called).toBeFalsy();
        });
    });
});
