describe("Subpanels layout", function() {
    var layout, app, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'subpanels');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Cases', 'subpanels');
    });

    afterEach(function() {
        sinonSandbox.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('_addComponentsFromDef', function() {
        var hiddenPanelsStub, relationshipStub, layoutPrototypeStub;

        beforeEach(function() {
            //Mock sidecar calls
            hiddenPanelsStub = sinonSandbox.stub(app.metadata, "getHiddenSubpanels", function(){
                return {0: "bugs", 1: "contacts"};
            });
            relationshipStub = sinonSandbox.stub(app.data, "getRelatedModule", function(module, linkName){
                return linkName;  //return linkName as module name for test
            });
            layoutPrototypeStub = sinonSandbox.stub(app.view.Layout.prototype, "_addComponentsFromDef", $.noop());
        });

        afterEach(function() {
            sinonSandbox.restore();
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
            function reset() {
                hiddenPanelsStub.reset();
                relationshipStub.reset();
                layoutPrototypeStub.reset();
            }
            var returnedComponents = layout._pruneHiddenComponents(components);
            expect(returnedComponents).toEqual(filteredComponents);
            reset();
            returnedComponents = layout._pruneHiddenComponents(filteredComponents);
            expect(returnedComponents).toEqual(filteredComponents);
            reset();
            returnedComponents = layout._pruneHiddenComponents(hiddenComponent);
            expect(returnedComponents).toEqual([]);
        });

        it('Should prune subpanels for which user has no access to', function() {
            layout.model = {
                fields: {
                    'good': { module: 'GoodLink'},
                    'bad': { module: 'BadLink'},
                }
            }
            layout.aclToCheck = 'view';
            var hasAccessStub = sinonSandbox.stub(app.acl, 'hasAccess', function(acl, link) {
                return link === 'good' ? true : false;
            });
            var components = [
                {context: {link: 'good'}},
                {context: {link: 'bad'}}
            ];
            var actual = layout._pruneNoAccessComponents(components);
            expect(actual.length).toEqual(1);
            expect(actual[0].context.link).toEqual('good');
            layout.model = null;//so we don't try to dispose bogus
        });

        it('Should hide hidden subpanels and also hide ACL forbidden subpanels', function() {
            layout.model = {
                fields: {
                    'cases': { module: 'contacts'}, // ACL Forbidden
                    'bugs': { module: 'bugs'},
                    'accounts': { module: 'accounts'}
                }
            }
            layout.aclToCheck = 'view';
            var hasAccessStub = sinonSandbox.stub(app.acl, 'hasAccess', function(acl, link) {
                return link === 'cases' ? false : true;
            });
            var components = [
                {context: {link: "bugs"}, layout: "subpanel"},  //Should be hidden
                {context: {link: "cases"}, layout: "subpanel"}, //Should be ACL forbidden
                {context: {link: "accounts"}, layout: "subpanel"}
            ];
            var hiddenComponent = [
                {context: {link: "bugs"}, layout: "subpanel"}
            ];
            var aclForbiddenComponent = [
                {context: {link: "contacts"}, layout: "subpanel"}
            ];
            var filteredComponents = [
                {context: {link: "accounts"}, layout: "subpanel"}
            ];
            function reset() {
                hiddenPanelsStub.reset();
                relationshipStub.reset();
                layoutPrototypeStub.reset();
            }
            layout._addComponentsFromDef(components);
            expect(layoutPrototypeStub.called).toBe(true);
            expect(layoutPrototypeStub.args[0][0]).toEqual(filteredComponents);
            layout.model = null;//so we don't try to dispose bogus
        });

    });
});
