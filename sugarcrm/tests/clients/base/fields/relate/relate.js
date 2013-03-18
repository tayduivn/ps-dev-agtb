describe("Base.Field.Relate", function() {

    var app, field, oRouter, buildRouteStub;

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

        // Workaround because router not defined yet
        oRouter = SugarTest.app.router;
        SugarTest.app.router = {buildRoute: function(){}};
        buildRouteStub = sinon.stub(SugarTest.app.router, 'buildRoute', function(module, id, action, params) {
            return module+'/'+id;
        });
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        buildRouteStub.restore();
        SugarTest.app.router = oRouter;
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

    describe("bwc _render", function() {
        var fieldRenderStu, getModuleStub, bwcBuildRouteStub;
        beforeEach(function() {
            fieldRenderStub = sinon.stub(app.view.Field.prototype, '_render');
            bwcBuildRouteStub = sinon.stub(app.bwc, 'buildRoute');
        });
        afterEach(function() {
            fieldRenderStub.restore();
            bwcBuildRouteStub.restore();
        });
        it("should build bwc route if bwcLink true", function() {
            var getModuleStub = sinon.stub(app.metadata, 'getModule', function() {
                return {isBwcEnabled: false};
            });
            field.bwcLink = true;
            field.format();
            expect(bwcBuildRouteStub).toHaveBeenCalled();
            getModuleStub.restore();
        });
        it("should fallback to checking isBwcEnabled if the bwcLink property is unset", function() {
            var getModuleStub = sinon.stub(app.metadata, 'getModule', function() {
                return {isBwcEnabled: true};
            });
            field.bwcLink = false;
            field.format();
            expect(bwcBuildRouteStub).toHaveBeenCalled();
            getModuleStub.restore();
        });
        it("should NOT build bwc route if bwcLink explictly set to false (even if isBwcEnabled is true)", function() {
            var getModuleStub = sinon.stub(app.metadata, 'getModule', function() {
                return {isBwcEnabled: true};
            });
            field.def.bwcLink = false;
            field.format();
            expect(bwcBuildRouteStub).not.toHaveBeenCalled();
            expect(buildRouteStub).toHaveBeenCalled();
            getModuleStub.restore();
        });
    });
});
