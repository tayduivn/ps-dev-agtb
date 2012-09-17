describe("Relate field", function() {

    var app;

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
        this.field = SugarTest.createField("base","account_name", "iframe", "detail", fieldDef);
        this.field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        this.field.model = null;
        this.field = null;
    });

    describe("relate", function() {
        xit("should have options template", function() {
            expect(this.field.optionsTemplateC).toEqual(Handlebars.templates["f.relate.options"]);
        });
    });
});
