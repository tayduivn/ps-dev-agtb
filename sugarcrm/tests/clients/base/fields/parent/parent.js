describe("Base.Field.Parent", function() {

    var app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        var fieldDef = {
            "name": "parent_name",
            "rname": "name",
            "vname": "LBL_ACCOUNT_NAME",
            "type": "relate",
            "link": "accounts",
            "table": "accounts",
            "join_name": "accounts",
            "isnull": "true",
            "module": "Accounts",
            "dbType": "varchar",
            "len": 100,
            "source": "non-db",
            "unified_search": true,
            "comment": "The name of the account represented by the account_id field",
            "required": true, "importable": "required"
        };
        sinonSandbox = sinon.sandbox.create();

        SugarTest.loadComponent("base", "field", "relate");
        field = SugarTest.createField("base","parent_name", "parent", "edit", fieldDef);
        field.model = new Backbone.Model({parent_type: "Contacts", parent_id: "111-222-33333", parent_name: "blob"});

        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }
    });
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        delete Handlebars.templates;
        field.model = null;
        field = null;
    });

    it("should set value correctly", function() {
        var expected_id = '0987',
            expected_name = 'blahblah',
            expected_module = 'Accounts';

        field.setValue({id: expected_id, value: expected_name, module: expected_module});
        var actual_id = field.model.get('parent_id'),
            actual_name = field.model.get('parent_name'),
            actual_module = field.model.get('parent_type');
        expect(actual_id).toEqual(expected_id);
        expect(actual_name).toEqual(expected_name);
        expect(actual_module).toEqual(expected_module);
    });
    it("should deal get related module for parent", function() {
        var actual_id = field.model.get('parent_id'),
            actual_module = field.model.get('parent_type'),
            _relatedModuleSpy = sinon.spy(field, "getSearchModule"),
            _relateIdSpy = sinon.spy(field, "_getRelateId");

        field.format();
        expect(_relatedModuleSpy).toHaveBeenCalled();
        expect(_relateIdSpy).toHaveBeenCalled();
        expect(field.href).toEqual("#"+actual_module+"/"+actual_id);

        _relatedModuleSpy.restore();
        _relateIdSpy.restore();
    });
});
