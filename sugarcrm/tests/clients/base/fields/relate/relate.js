describe("Base.Field.Relate", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        var fieldDef = {
            "name": "account_name",
            "rname": "name", "id_name": "account_id",
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
        field = SugarTest.createField("base","account_name", "relate", "edit", fieldDef);
        field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field = null;
    });

    it("should set value correctly", function() {
        var expected_id = '0987',
            expected_name = 'blahblah';

        field.setValue({id: expected_id, value: expected_name});
        var actual_id = field.model.get(field.def.id_name),
            actual_name = field.model.get(field.def.name);
        expect(actual_id).toEqual(expected_id);
        expect(actual_name).toEqual(expected_name);
    });
});
