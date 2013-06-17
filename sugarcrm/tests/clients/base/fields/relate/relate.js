describe("Base.Field.Relate", function () {

    var app, field, oRouter, buildRouteStub, fieldDef;

    beforeEach(function () {
        app = SugarTest.app;

        fieldDef = {
            "name": "account_name",
            "rname": "name",
            "id_name": "account_id",
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
            "required": true,
            "importable": "required"
        };
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        SugarTest.app.router = oRouter;
        delete Handlebars.templates;
        fieldDef = null;
    });

    describe("SetValue", function () {
        beforeEach(function () {
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
        });

        it("should set value correctly", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.setValue({id: expected_id, value: expected_name});
            var actual_id = field.model.get(field.def.id_name),
                actual_name = field.model.get(field.def.name);

            //Relate takes care of its unformating
            //unformat is overriden to return the unformated value off the model
            expect(field.unformat('test')).toEqual(actual_id);
            expect(field.unformat('test')).toEqual(expected_id);

            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
        });

        afterEach(function () {
            field.model = null;
            field = null;
        });
    });

    describe("bwc _render", function () {
        var fieldRenderStub, getModuleStub, bwcBuildRouteStub;
        beforeEach(function () {
            var fieldDef = {
                "name": "account_name",
                "rname": "name",
                "id_name": "account_id",
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
                "required": true,
                "importable": "required"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});

            fieldRenderStub = sinon.stub(app.view.Field.prototype, '_render');
            bwcBuildRouteStub = sinon.stub(app.bwc, 'buildRoute');

            // Workaround because router not defined yet
            oRouter = SugarTest.app.router;
            SugarTest.app.router = {
                buildRoute: function () {
                }
            };
            buildRouteStub = sinon.stub(SugarTest.app.router, 'buildRoute', function (module, id, action, params) {
                return module + '/' + id;
            });
        });
        afterEach(function () {
            buildRouteStub.restore();
            fieldRenderStub.restore();
            bwcBuildRouteStub.restore();
            field.model = null;
            field = null;
        });
        it("should build bwc route if bwcLink true", function () {
            getModuleStub = sinon.stub(app.metadata, 'getModule', function () {
                return {isBwcEnabled: false};
            });
            field.bwcLink = true;
            field.format();
            expect(bwcBuildRouteStub).toHaveBeenCalled();
            getModuleStub.restore();
        });
        it("should fallback to checking isBwcEnabled if the bwcLink property is unset", function () {
            getModuleStub = sinon.stub(app.metadata, 'getModule', function () {
                return {isBwcEnabled: true};
            });
            field.bwcLink = false;
            field.format();
            expect(bwcBuildRouteStub).toHaveBeenCalled();
            getModuleStub.restore();
        });
        it("should NOT build bwc route if bwcLink explictly set to false (even if isBwcEnabled is true)", function () {
            getModuleStub = sinon.stub(app.metadata, 'getModule', function () {
                return {isBwcEnabled: true};
            });
            field.def.bwcLink = false;
            field.format();
            expect(bwcBuildRouteStub).not.toHaveBeenCalled();
            expect(buildRouteStub).toHaveBeenCalled();
            getModuleStub.restore();
        });
    });

    describe("Populate related fields", function () {

        it("should warn the wrong metadata fields that populates unmatched fields", function () {
            var metadataStub, loggerStub;

            metadataStub = sinon.stub(app.metadata, 'getModule', function () {
                return {
                    fields: {
                        'field1': false
                    }
                }
            });
            loggerStub = sinon.stub(app.logger, 'error');
            fieldDef.populate_list = {
                "field1": "foo",
                "billing_office": "boo"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            expect(loggerStub).toHaveBeenCalled();

            field.model = null;
            field = null;
            metadataStub.restore();
            loggerStub.restore();
        });

        it("should populate related variables when the user confirms the changes", function () {
            fieldDef.populate_list = {
                "billing_office": "primary_address_1"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.model.fields = {
                'primary_address_1': {
                    label: ''
                }
            };

            //Before confirmed the dialog
            var expected_id = '0987',
                expected_name = 'blahblah',
                expected_primary_address_1 = 'should be undefined';

            field.setValue({
                id: expected_id,
                value: expected_name,
                boo: 'should not be in',
                billing_office: expected_primary_address_1
            });
            var actual_id = field.model.get(field.def.id_name),
                actual_name = field.model.get(field.def.name),
                actual_primary_address_1 = field.model.get("primary_address_1");
            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
            expect(actual_primary_address_1).toBeUndefined();
            expect(field.model.get("boo")).toBeUndefined();

            //After the user confirms the dialog
            var confirmStub = sinon.stub(app.alert, 'show', function (msg, param) {
                param.onConfirm();
            });
            expected_primary_address_1 = '1234 blahblah st.';

            field.setValue({
                id: expected_id,
                value: expected_name,
                boo: 'should not be in',
                billing_office: expected_primary_address_1
            });
            actual_id = field.model.get(field.def.id_name);
            actual_name = field.model.get(field.def.name);
            actual_primary_address_1 = field.model.get("primary_address_1");
            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
            expect(actual_primary_address_1).toEqual(expected_primary_address_1);
            expect(field.model.get("boo")).toBeUndefined();

            field.model = null;
            field = null;
            confirmStub.restore();
        });
        it("should not populate related variables which does NOT have acl controls", function () {
            fieldDef.populate_list = {
                "billing_office": "primary_address_1",
                "billing_phone": "primary_phone_number"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.model.fields = {
                'primary_address_1': {
                    label: ''
                },
                'primary_phone_number': {
                    label: ''
                }
            };
            var aclMapping = {
                    primary_address_1: false,
                    primary_phone_number: true
                },
                confirmStub = sinon.stub(app.alert, 'show', function (msg, param) {
                    param.onConfirm();
                }),
                aclStub = sinon.stub(app.acl, 'hasAccessToModel', function (action, model, field) {
                    return aclMapping[field];
                });
            var expected_id = '0987',
                expected_name = 'blahblah',
                expected_primary_address_1 = 'should be undefined',
                expected_primary_phone_number = '999)111-2222';

            field.setValue({
                id: expected_id,
                value: expected_name,
                boo: 'should not be in',
                billing_office: expected_primary_address_1,
                billing_phone: expected_primary_phone_number
            });
            var actual_id = field.model.get(field.def.id_name),
                actual_name = field.model.get(field.def.name),
                actual_primary_address_1 = field.model.get("primary_address_1"),
                actual_primary_phone_number = field.model.get("primary_phone_number");
            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
            expect(actual_primary_address_1).toBeUndefined();
            expect(field.model.get("boo")).toBeUndefined();
            expect(actual_primary_phone_number).toBe(expected_primary_phone_number);

            field.model = null;
            field = null;
            confirmStub.restore();
            aclStub.restore();
        });
    });

    describe("alert message", function () {
        var alertShowStub;
        beforeEach(function () {
            fieldDef.populate_list = {
                "populate_field1": "populate_field_dist1",
                "populate_field2": "populate_field_dist2"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.model.fields = {
                'populate_field_dist1': {
                    label: ''
                },
                'populate_field_dist2': {
                    label: ''
                }
            };
            alertShowStub = sinon.stub(app.alert, 'show', function (level) {
                return;
            });
        });

        it("should call app.alert.show() if auto_populate is not defined", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.setValue({
                id: expected_id,
                value: expected_name,
                populate_field1: 'new value 1',
                populate_field2: 'new value 2'
            });
            expect(app.alert.show).toHaveBeenCalled();
        });

        it("should call app.alert.show() if auto_populate is not true", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.def.auto_populate = false;

            field.setValue({
                id: expected_id,
                value: expected_name,
                populate_field1: 'new value 1',
                populate_field2: 'new value 2'
            });
            expect(app.alert.show).toHaveBeenCalled();
        });

        it("should not call app.alert.show() if auto_populate is true", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.def.auto_populate = true;

            field.setValue({
                id: expected_id,
                value: expected_name,
                populate_field1: 'new value 1',
                populate_field2: 'new value 2'
            });
            expect(app.alert.show).not.toHaveBeenCalled();
        });

        afterEach(function () {
            field.model = null;
            field = null;
            alertShowStub.restore();
        });
    });
});
