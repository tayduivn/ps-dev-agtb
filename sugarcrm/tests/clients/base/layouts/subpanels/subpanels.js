describe("Subpanels layout", function() {
    var layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'subpanels');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Cases', 'subpanels');
    });

    afterEach(function() {
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('_addComponentsFromDef', function() {
        var hiddenPanelsStub, relationshipStub, layoutPrototypeStub;

        beforeEach(function() {
            //Mock sidecar calls
            hiddenPanelsStub = sinon.stub(app.metadata, "getHiddenSubpanels", function(){
                return {0: "bugs", 1: "contacts"};
            });
            relationshipStub = sinon.stub(app.data, "getRelatedModule", function(module, linkName){
                return linkName;  //return linkName as module name for test
            });
            layoutPrototypeStub = sinon.stub(app.view.Layout.prototype, "_addComponentsFromDef", $.noop());
        });

        afterEach(function() {
            hiddenPanelsStub.restore();
            relationshipStub.restore();
            layoutPrototypeStub.restore();
        });

        it('Should not add subpanel components for modules that are hidden in subpanels', function() {
            var components = [
                {context: {link: "bugs"}, layout: "subpanel"},  //Should be hidden
                {context: {link: "cases"}, layout: "subpanel"},
                {context: {link: "accounts"}, layout: "subpanel"}
            ];
            var hiddenComponent = [
                {context: {link: "bugs"}, layout: "subpanel"}
            ];
            var filteredComponents = [
                {context: {link: "cases"}, layout: "subpanel"},
                {context: {link: "accounts"}, layout: "subpanel"}
            ];
            layout._addComponentsFromDef(components);
            expect(layoutPrototypeStub.called).toBe(true);
            expect(layoutPrototypeStub.args[0][0]).toEqual(filteredComponents);

            hiddenPanelsStub.reset();
            relationshipStub.reset();
            layoutPrototypeStub.reset();

            layout._addComponentsFromDef(filteredComponents);
            expect(layoutPrototypeStub.called).toBe(true);
            expect(layoutPrototypeStub.args[0][0]).toEqual(filteredComponents);

            hiddenPanelsStub.reset();
            relationshipStub.reset();
            layoutPrototypeStub.reset();

            layout._addComponentsFromDef(hiddenComponent);
            expect(layoutPrototypeStub.called).toBe(true);
            expect(layoutPrototypeStub.args[0][0]).toEqual([]);
        });

    });

});
