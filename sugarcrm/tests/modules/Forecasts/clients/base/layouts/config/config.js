/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe("Forecasts.Layout.Config", function() {
    var app, layout, layoutProtoInitStub, aclStub, codeBlockSpy, moduleName = 'Forecasts';
    var context;
    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        aclStub = sinon.stub(app.user, 'getAcls', function () {
            return {
                Forecasts: {}
            };
        });
        app.data.reset();
        app.data.declareModel(moduleName, SugarTest.app.metadata.getModule(moduleName));

        app.user.set({'id': 'test_userid', full_name: 'Selected User', type: 'admin'});

        context = app.context.getContext();
        context.set({
                module: 'Forecasts',
                layout: 'config',
                skipFetch: true
            });
        context.prepare();

        layout = SugarTest.createLayout('base', 'Forecasts', 'config',null, context, true);
        layoutProtoInitStub = sinon.stub(app.view.Layout.prototype, 'initialize', function() {});
        codeBlockSpy = sinon.spy(layout, 'codeBlockForecasts', function() {});
    });

    afterEach(function() {
        aclStub.restore();
        layoutProtoInitStub.restore();
        codeBlockSpy.restore();
        layout = undefined;
        app = undefined;
    });

    describe("initialize()", function() {
        describe("user has no access to module", function() {
            beforeEach(function() {
                aclStub.restore();
                aclStub = sinon.stub(app.user, 'getAcls', function () {
                    return {
                        Forecasts: {
                            access: 'no'
                        }
                    };
                });
                layout.initialize();
            });

            it("should not call Layout.initialize", function() {
                expect(layoutProtoInitStub).not.toHaveBeenCalled();
            });
            it("should call codeBlockForecasts", function() {
                expect(codeBlockSpy).toHaveBeenCalled();
            });
        });

        describe("user has access to module - admin: no, developer: no", function() {
            beforeEach(function() {
                aclStub.restore();
                aclStub = sinon.stub(app.user, 'getAcls', function () {
                    return {
                        Forecasts: {
                            developer: 'no'
                        }
                    };
                });
                app.user.set({type: 'user'});
                layout.initialize();
            });

            it("should not call Layout.initialize", function() {
                expect(layoutProtoInitStub).not.toHaveBeenCalled();
            });
            it("should call codeBlockForecasts", function() {
                expect(codeBlockSpy).toHaveBeenCalled();
            });
        });

        describe("user has access to module - admin: yes, developer: no", function() {
            beforeEach(function() {
                aclStub.restore();
                aclStub = sinon.stub(app.user, 'getAcls', function () {
                    return {
                        Forecasts: {
                            developer: 'no'
                        }
                    };
                });
                layout.initialize();
            });

            it("should call Layout.initialize", function() {
                expect(layoutProtoInitStub).toHaveBeenCalled();
            });
            it("should not call codeBlockForecasts", function() {
                expect(codeBlockSpy).not.toHaveBeenCalled();
            });
        });

        describe("user has access to module - admin: no, developer: yes", function() {
            beforeEach(function() {
                aclStub.restore();
                aclStub = sinon.stub(app.user, 'getAcls', function () {
                    return {
                        Forecasts: {
                            admin: 'no'
                        }
                    };
                });
                app.user.set({type: 'user'});
                layout.initialize();
            });

            it("should call Layout.initialize", function() {
                expect(layoutProtoInitStub).toHaveBeenCalled();
            });
            it("should not call codeBlockForecasts", function() {
                expect(codeBlockSpy).not.toHaveBeenCalled();
            });
        });

        describe("user has access to module - admin: yes, developer: yes", function() {
            beforeEach(function() {
                aclStub.restore();
                aclStub = sinon.stub(app.user, 'getAcls', function () {
                    return {
                        Forecasts: {
                        }
                    };
                });
                layout.initialize();
            });

            it("should call Layout.initialize", function() {
                expect(layoutProtoInitStub).toHaveBeenCalled();
            });
            it("should not call codeBlockForecasts", function() {
                expect(codeBlockSpy).not.toHaveBeenCalled();
            });
        });
    });
})
