describe("Base.View.RecordList", function () {
    var view, layout, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
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
        view = SugarTest.createView("base", "Cases", "recordlist", null, null);
        layout = SugarTest.createLayout("base", "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function () {
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('adding actions to list view', function () {

        it('should return my_favorite field when calling getFieldNames', function () {
            var fields = view.getFieldNames();
            expect(_.indexOf(fields, 'my_favorite')).toBeGreaterThan(-1);
        });

        it('should have added favorite field', function () {
            view.render();
            expect(view.leftColumns[0].fields[1]).toEqual({type:'favorite'});
        });

        it('should have added favorite field', function () {
            view.dispose();

            SugarTest.testMetadata.updateModuleMetadata("Cases", {
                favoritesEnabled: false
            });
            var nofavoriteview = SugarTest.createView("base", "Cases", "recordlist", null, null);
            nofavoriteview.render();
            var actualFavoriteField = _.where(nofavoriteview.leftColumns[0].fields, {type: 'favorite'});
            expect(actualFavoriteField.length).toBe(0);
            nofavoriteview.dispose();
        });

        it('should have added row actions', function () {
            view.render();
            expect(view.leftColumns[0].fields[2]).toEqual({
                type:'editablelistbutton',
                label:'LBL_CANCEL_BUTTON_LABEL',
                name:'inline-cancel',
                css_class:'btn-link btn-invisible inline-cancel'
            });
            expect(view.rightColumns[0].fields[1]).toEqual({
                type:'editablelistbutton',
                label:'LBL_SAVE_BUTTON_LABEL',
                name:'inline-save',
                css_class:'btn-primary'
            });
            expect(view.rightColumns[0].css_class).toEqual('overflow-visible');
        });
    });
});
