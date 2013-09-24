describe("sugar7.extensions.bwc", function() {
    var app, module, id, action, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        app = SugarTest.app;
        module = "Foo";
        action = "EditView";
        id = '12345';
    });
    afterEach(function() {
        sinonSandbox.restore();
        module = null;
        action = null;
        id = null;
    });
    it("should have a login method", function() {
        var stub = sinon.stub(app.api, 'call');
        app.bwc.login('path/to/foo');
        expect(stub.called).toBe(true);
        expect(stub.args[0][0]).toEqual('create');
        expect(stub.args[0][1].match(/oauth2.bwc.login/)).not.toEqual(null);
        stub.restore();
    });
    it("should build a bwc route given module, action, id", function() {
        var expected, actual;
        expected = "bwc/index.php?module=" + module + "&action=" + action + "&record=" +id;
        actual = app.bwc.buildRoute(module, id, action);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module (no action or id provided)", function() {
        var actual, expected;
        expected = "bwc/index.php?module=" + module + "&action=index";
        actual = app.bwc.buildRoute(module);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module and id (no action provided)", function() {
        var actual, expected;
        expected = "bwc/index.php?module=" + module + "&action=DetailView&record=" + id;
        actual = app.bwc.buildRoute(module, id);
        expect(actual).toEqual(expected);
    });
    it("should build bwc for module and action (no id) respecting caller's choices unless DetailView", function() {
        var actual, expected;
        // action could be a list view or whatever and we should respect wishes in this case
        // module=Quotes&action=ListView
        // module=Quotes&action=EditView (which goes to Create)
        expected = "bwc/index.php?module=" + module + "&action=" + action;
        actual = app.bwc.buildRoute(module, null, action);
        expect(actual).toEqual(expected);

        // But! If they're asking for action DetailView, with no id, we DO force
        // to action=index since detail with no id just doesn't make sense
        expected = "bwc/index.php?module=" + module + "&action=index";
        actual = app.bwc.buildRoute(module, null, 'DetailView');
        expect(actual).toEqual(expected);
    });

    describe('_createRelatedRecordUrlParams', function() {
        var parentModel, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            }),
            relateFieldStub = sinonSandbox.stub(app.data, 'getRelateFields', function() {
                return [{
                    name: 'product_template_name',
                    rname: 'name',
                    id_name: 'product_template_id',
                    populate_list: {
                        account_id: 'account_id',
                        account_name: 'account_name',
                        assigned_user_name: 'user_name'
                    }
                }];
            });
        });

        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields in URL when creating a new BWC record', function() {
            var params = app.bwc._createRelatedRecordUrlParams(parentModel, "test");
            expect(params['product_template_id']).toBe(parentModel.get('id'));
            expect(params['product_template_name']).toBe(parentModel.get('name'));
            expect(params['account_id']).toBe(parentModel.get('account_id'));
            expect(params['account_name']).toBe(parentModel.get('account_name'));
            expect(params['user_name']).toBe(parentModel.get('assigned_user_name'));
        });

    });

    describe("getLegacyMetadata", function() {
        var metadataConverterStub, bwcModuleStub;

        beforeEach(function() {
            metadataConverterStub = sinon.stub(app.bwc, "_getLegacyMetadataConverter");
            bwcModuleStub = sinon.stub(app.metadata, "getModule");
        });

        afterEach(function() {
            metadataConverterStub.restore();
            bwcModuleStub.restore();
        });

        describe("undefined behaviours", function() {
            it("should return undefined when requested vardef type isn't known", function() {
                metadataConverterStub.returns(false);
                bwcModuleStub.returns({isBwcEnabled: true});
                expect(app.bwc.getLegacyMetadata("foo", "bar")).toBeUndefined();
            });

            it("should return undefined when module isn't in BWC mode", function() {
                metadataConverterStub.returns(true);
                bwcModuleStub.returns({isBwcEnabled: false});
                expect(app.bwc.getLegacyMetadata("foo", "bar")).toBeUndefined();
            });
        });

        describe("well-defined behaviours", function() {
            var apiStub;

            beforeEach(function() {
                apiStub = sinon.stub(app.api, "call");
            });

            afterEach(function() {
                apiStub.restore();
            });

            it("should fetch the right url", function() {
                metadataConverterStub.returns(true);
                bwcModuleStub.returns({isBwcEnabled: true});

                app.bwc.getLegacyMetadata("foo", "bar");
                var call = apiStub.getCall(0);

                expect(apiStub).toHaveBeenCalledOnce();
                expect(call.args[1]).toContain("module=foo");
                expect(call.args[1]).toContain("type=bar");
            });

            it("should process the correct JSON", function() {
                var stub = sinon.stub();
                metadataConverterStub.returns(stub);
                bwcModuleStub.returns({isBwcEnabled: true});

                app.bwc.getLegacyMetadata("foo", "bar");
                expect(apiStub).toHaveBeenCalledOnce();
                apiStub.getCall(0).args[3].success({foo: {id: {}}});
                expect(stub).toHaveBeenCalledOnce();
                expect(stub.getCall(0).args[0]).toEqual({id: {}});
            });
        });
    });

    describe("legacy metadata converters", function() {
        describe("listviewdefs", function() {
            var converter;

            beforeEach(function() {
                converter = app.bwc._getLegacyMetadataConverter("listviewdefs");
            });

            it("should normalize the name property", function() {
                var original = {
                    nAmE: {},
                    email: {}
                };

                var expected = {
                    panels: [{
                        label: 'LBL_PANEL_DEFAULT',
                        fields: [
                            {
                                name: 'name',
                                'default': false,
                                enabled: true
                            },
                            {
                                name: 'email',
                                'default': false,
                                enabled: true
                            }
                        ]
                    }]
                };

                expect(converter(original)).toEqual(expected);
            });

            it("should normalize the default property", function() {
                var original = {
                    name: {
                        'default': 0
                    },
                    email: {
                        'default': 1
                    }
                };

                var expected = {
                    panels: [{
                        label: 'LBL_PANEL_DEFAULT',
                        fields: [
                            {
                                name: 'name',
                                'default': false,
                                enabled: true
                            },
                            {
                                name: 'email',
                                'default': true,
                                enabled: true
                            }
                        ]
                    }]
                };

                expect(converter(original)).toEqual(expected);
            });

            it("should copy other properties to the new def", function() {
                var original = {
                    name: {
                        asdf: 0,
                        testfoo: 'bar'
                    }
                };

                var expected = {
                    panels: [{
                        label: 'LBL_PANEL_DEFAULT',
                        fields: [
                            {
                                name: 'name',
                                'default': false,
                                enabled: true,
                                asdf: 0,
                                testfoo: 'bar'
                            }
                        ]
                    }]
                };

                expect(converter(original)).toEqual(expected);
            });
        });
    });
});
