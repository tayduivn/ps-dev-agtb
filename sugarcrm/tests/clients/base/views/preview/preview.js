describe("Preview View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('buttondropdown', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate("record", 'view', 'base');
        SugarTest.loadComponent('base', 'view', "record");
        SugarTest.loadComponent('base', 'view', "preview");
        SugarTest.testMetadata.addViewDefinition("record", {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name"]
            }, {
                "name": "panel_body",
                "label": "LBL_PANEL_2",
                "columns": 1,
                "labels": true,
                "labelsOnTop": false,
                "placeholders":true,
                "fields": ["description","case_number","type"]
            }, {
                "name": "panel_hidden",
                "hide": true,
                "labelsOnTop": false,
                "placeholders": true,
                "fields": ["created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, "Cases");
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        sinonSandbox = sinon.sandbox.create();
        view = SugarTest.createView("base", "Cases", "preview", null, null);
        app = SUGAR.App;
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("_trimPreviewMetadata", function(){
        it("should not modify metadata passed in", function(){

        });
        it("should trim metadata from empty fields", function(){

        });
        it("should remove favorites field from metadata", function(){

        });
    });

});
