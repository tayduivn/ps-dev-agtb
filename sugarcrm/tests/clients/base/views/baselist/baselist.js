describe("sugarviews", function() {
    var view, layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("baselist", 'view', 'base');
        SugarTest.testMetadata.addViewDefinition("baselist", {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, "Cases");
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view = SugarTest.createView("base", "Cases", "baselist", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("baselist",function() {
        it('should open an alert message on sort', function() {
            view.render();
            var ajaxStub = sinon.stub(app.api, 'call');
            var alertStub = sinon.stub(app.alert, 'show');
            view.$('[data-fieldname=case_number]').click();
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            ajaxStub.restore();
        });
    })
});