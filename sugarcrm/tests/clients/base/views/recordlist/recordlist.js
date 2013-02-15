describe("Base.View.RecordList", function () {
    var view, recordlistview, layout, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("list", 'view', 'base');
        SugarTest.loadHandlebarsTemplate("recordlist", 'view', 'base');
        SugarTest.testMetadata.addViewDefinition("list", {
            "favorite": true,
            "selection": {
                "type": "multi",
                "actions": []
            },
            "rowactions": {
                "actions": []
            },
            "panels":[
                {
                    "name":"panel_header",
                    "header":true,
                    "fields":["name", "case_number", "type", "created_by", "date_entered", "date_modified", "modified_user_id"]
                }
            ]
        }, "Cases");
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        recordlistview = SugarTest.createView("base", "Cases", "recordlist", null, null);
        layout = SugarTest.createLayout("base", "Cases", "list", null, null);
        _.extend(view, recordlistview);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
        recordlistview = null;
    });

    describe('adding actions to list view', function () {

        it('should have added favorite field', function () {
            view.render();
            expect(view._leftActions[0].fields[1]).toEqual({type:'favorite'});
        });

        it('should have added row actions', function () {
            view.render();
            expect(view._leftActions[0].fields[2]).toEqual({
                type:'editablelistbutton',
                label:'LBL_CANCEL_BUTTON_LABEL',
                name:'inline-cancel',
                css_class:'btn-link btn-invisible inline-cancel'
            });
            expect(view._rowActions[0].fields[1]).toEqual({
                type:'editablelistbutton',
                label:'LBL_SAVE_BUTTON_LABEL',
                name:'inline-save',
                css_class:'btn-primary'
            });
            expect(view._rowActions[0].cell_css_class).toEqual('overflow-visible');
        });
    });
});