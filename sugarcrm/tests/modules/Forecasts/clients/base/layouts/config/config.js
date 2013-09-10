/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

describe("Forecasts.Layout.Config", function() {
    var app, layout, layoutProtoInitStub, aclStub, codeBlockSpy, moduleName = 'Forecasts';

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

        layout = SugarTest.createLayout('base', 'Forecasts', 'config', null, null, true);
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
