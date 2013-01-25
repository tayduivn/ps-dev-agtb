describe("Preview View", function() {

    var preview, app, meta;

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
                "fields": ["name", {"name":"favorite", "type":"favorite"}]
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
        preview = SugarTest.createView("base", "Cases", "preview", null, null);
        app = SUGAR.App;
        meta = app.metadata.getView('Cases', 'record');
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        preview = null;
        meta = null;
    });

    describe("_previewifyMetadata", function(){
        it("should not modify metadata passed in", function(){
            var trimmed = preview._previewifyMetadata(meta);
            expect(trimmed).toNotBe(meta);
            expect(trimmed).toNotEqual(meta);
        });
        it("should convert header to regular panel", function(){
            expect(meta.panels[0].header).toEqual(true);
            var trimmed = preview._previewifyMetadata(meta);
            expect(meta.panels[0].header).toEqual(true);
            expect(trimmed.panels[0].header).toEqual(false);
            var headers = _.filter(trimmed.panels, function(panel){
                return panel.header == true;
            });
            expect(headers).toEqual([]);
        });
        it("should remove favorites field from metadata", function(){
            var fav = _.find(meta.panels[0].fields, function(field){
                return field.type === "favorite";
            });
            expect(fav).toBeTruthy();
            var trimmed = preview._previewifyMetadata(meta);
            fav = _.find(trimmed.panels[0].fields, function(field){
                return field.type === "favorite";
            });
            expect(fav).toBeUndefined();
        });
        it("should detect if at least one of the panels is hidden", function(){
            expect(preview.hiddenPanelExists).toBe(false);
            preview._previewifyMetadata(meta);
            expect(preview.hiddenPanelExists).toBe(true);
            meta.panels[2].hide = false;
            preview._previewifyMetadata(meta);
            expect(preview.hiddenPanelExists).toBe(false);
        });

    });
    describe("renderPreview", function(){
        it("should trigger 'preview:open' and 'list:preview:decorate' events", function(){
            var dummyModel = app.data.createBean("Cases", {"id":"testid", "_module": "Cases"});
            var dummyCollection = {};
            dummyCollection.models = [dummyModel];
            var openPreviewFired = false;
            var listPreviewDecorateFired = false;
            var triggerStub = sinon.stub(preview.context,"trigger", function(event, model){
                expect(event).not.toBeEmpty();
                if(event == "preview:open"){
                    openPreviewFired = true;
                } else if(event == "list:preview:decorate"){
                    listPreviewDecorateFired = true;
                    expect(model.get("id")).toEqual("testid");
                }
            });
            preview.renderPreview(dummyModel, dummyCollection);
            expect(openPreviewFired).toBe(true);
            expect(listPreviewDecorateFired).toBe(true);
            triggerStub.restore();
        });
        it("should be called on 'preview:render' event", function(){
            var dummyModel = app.data.createBean("Cases", {"id":"testid", "_module": "Cases"});
            var dummyCollection = {};
            dummyCollection.models = [dummyModel];
            var renderPreviewStub = sinon.stub(preview,"renderPreview", function(model, collection){
               expect(model).toEqual(dummyModel);
               expect(collection).toEqual(dummyCollection);
            });
            preview.context.trigger("preview:render", dummyModel, dummyCollection, false);
            expect(renderPreviewStub).toHaveBeenCalled();
            renderPreviewStub.restore();
        });
    });

});
