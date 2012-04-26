describe("sugarfields", function() {
    beforeEach(function() {
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
        var controller = SugarFieldTest.loadSugarField('relate/relate');
        this.field = SugarFieldTest.createField("account_name", "relate");
        var model = new Backbone.Model({account_id: "1234", account_name: "bob"});
        this.field = _.extend(this.field, controller);
        this.field.fieldDef = fieldDef;
        this.field.model = model;

    });

    describe("relate", function() {
        it("should set options template", function() {
            var templateGetStub = sinon.stub(this.field.app.template, 'get', function(templateKey) {
                return Handlebars.compile("String");
            });
            this.field.setOptionsTemplate();
            expect(this.field.optionsTemplateC).toBeDefined();
            expect(this.field.optionsTemplateC(this.field)).toEqual("String");

            templateGetStub.restore();
        });
    });
});