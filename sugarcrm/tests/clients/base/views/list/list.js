describe("Base.View.List", function() {
    var view, layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("list", 'view', 'base');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, "Cases");
        SugarTest.testMetadata.set();
        //SugarTest.app.data.declareModels();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("list",function() {
        it('should open an alert message on sort', function() {
            view.render();
            var ajaxStub = sinon.stub(view.collection, 'fetch');
            var alertStub = sinon.stub(app.alert, 'show');
            view.setOrderBy({target:'[data-fieldname=case_number]'});
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            ajaxStub.restore();
        });
    });
});
