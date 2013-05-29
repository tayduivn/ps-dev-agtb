describe("Sidebar Toggle", function () {
    var field, layout, app, sinonSandbox;

    beforeEach(function () {
        app = SugarTest.app;
        var def = {
            "components": [
                {"layout": {"span": 4}},
                {"layout": {"span": 8}}
            ]};
        sinonSandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'sidebartoggle');
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', null, "default", def, null);
        field = SugarTest.createField("base", null, "sidebartoggle", "record", def);
        sinonSandbox.stub(app.view.layouts.BaseDefaultLayout.prototype, 'processDef');
    });
    afterEach(function () {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });
    it("should broadcast sidebarRendered event on _render", function() {
        var contextOn = sinonSandbox.stub(app.controller.context, 'trigger');
        field._render();
        expect(contextOn).toHaveBeenCalledWith('sidebarRendered');
    });
    it("should listen for toggleSidebarArrows event", function() {
        var updateArrowsStub = sinonSandbox.stub(field, 'updateArrows');
        field.bindDataChange();
        app.controller.context.trigger('toggleSidebarArrows');
        expect(updateArrowsStub).toHaveBeenCalled();
    });
    it("should listen for openSidebarArrows event", function() {
        var sidebarArrowsOpenStub = sinonSandbox.stub(field, 'sidebarArrowsOpen');
        field.bindDataChange();
        app.controller.context.trigger('openSidebarArrows');
        expect(sidebarArrowsOpenStub).toHaveBeenCalled();
    });
    it("should toggle and fire toggleSidebar event when user clicks toggle arrows", function() {
        var contextOn = sinonSandbox.stub(field.context, 'trigger');
        field.toggle();
        expect(contextOn).toHaveBeenCalledWith('toggleSidebar');
    });
    it("should update arrows with direction (open)", function() {
        // Stub the addClass/removeClass jQuery methods on $'s prototype
        var removeClassStub = sinonSandbox.stub($.fn, 'removeClass', function(){return $.fn;});
        var addClassStub = sinonSandbox.stub($.fn, 'addClass', function() {return $.fn;});
        field.updateArrowsWithDirection('open');
        expect(removeClassStub).toHaveBeenCalledWith('icon-double-angle-left');
        expect(addClassStub).toHaveBeenCalledWith('icon-double-angle-right');
    });
    it("should update arrows with direction (close)", function() {
        // Stub the addClass/removeClass jQuery methods on $'s prototype
        var removeClassStub = sinonSandbox.stub($.fn, 'removeClass', function(){return $.fn;});
        var addClassStub = sinonSandbox.stub($.fn, 'addClass', function() {return $.fn;});
        field.updateArrowsWithDirection('close');
        expect(removeClassStub).toHaveBeenCalledWith('icon-double-angle-right');
        expect(addClassStub).toHaveBeenCalledWith('icon-double-angle-left');
    });
});
