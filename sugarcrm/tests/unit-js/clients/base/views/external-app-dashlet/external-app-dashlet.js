/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe('Base.View.ExternalAppDashlet', function() {
    var view;
    var options;
    var app;
    var context;
    var layout;
    var module;

    beforeEach(function() {
        var meta = {};

        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        module = 'Contacts';
        window.singleSpa = {
            start: sinon.collection.stub(),
            mountRootParcel: sinon.collection.stub()
        };

        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadComponent('base', 'view', 'external-app');

        options = {
            context: context,
            meta: {
                srn: 'some-srn',
                env: {
                    testKey: 'test val'
                }
            },
            module: module,
            layout: {
                cid: 'w92'
            }
        };

        layout = SugarTest.createLayout('base', module, 'dashboard', meta);
        view = SugarTest.createView('base', null, 'external-app-dashlet', options.meta, options.context, false, layout);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_getAvailableServices', function() {
                return [{
                    view: {
                        name: 'svc1'
                    }
                }, {
                    view: {
                        name: 'svc2'
                    }
                }];
            });
        });

        it('should set services', function() {
            view.initialize(options);

            expect(view.services.length).toBe(2);
        });

        describe('if not in config mode', function() {
            beforeEach(function() {
                options.meta.config = false;
            });

            it('should check _checkCatalogAccess', function() {
                sinon.collection.stub(view, '_checkCatalogAccess', function() {
                    return true;
                });

                view.initialize(options);

                expect(view.allowApp).toBeTruthy();
            });

            it('should set errorCode if check _checkCatalogAccess is false', function() {
                sinon.collection.stub(view, '_checkCatalogAccess', function() {
                    return false;
                });

                view.initialize(options);

                expect(view.errorCode).toBe('CAT-404');
            });
        });
    });

    describe('initDashlet()', function() {
        var srcField;
        var services;

        beforeEach(function() {
            srcField = {
                name: 'src',
                type: 'enum'
            };
            view.dashletConfig = {
                panels: [{
                    fields: [srcField]
                }]
            };
            services = [{
                view: {
                    name: 'test1',
                    src: 'https://test1'
                }
            }, {
                view: {
                    name: 'test2',
                    src: 'https://test2'
                }
            }];
            view.services = services;
            sinon.collection.spy(view.settings, 'on');
            sinon.collection.stub(view, 'setAppUrlTitle', function() {});
            view.meta.config = true;

            view.initDashlet();
        });

        afterEach(function() {
            srcField = null;
        });

        it('should set options on the src field with services', function() {
            expect(srcField.options).toEqual({
                'https://test1': 'test1',
                'https://test2': 'test2'
            });
        });

        it('should build services object with services', function() {
            expect(view.servicesObj).toEqual({
                'https://test1': {
                    name: 'test1',
                    src: 'https://test1'
                },
                'https://test2': {
                    name: 'test2',
                    src: 'https://test2'
                }
            });
        });

        it('should set a change event listener on settings', function() {
            expect(view.settings.on).toHaveBeenCalledWith('change:src');
        });

        it('should call setAppUrlTitle when settings src changes', function() {
            view.settings.set('src', 'test');

            expect(view.setAppUrlTitle).toHaveBeenCalled();
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.view.View.prototype.render, 'call', function() {});
            sinon.collection.stub(view, 'setAppUrlTitle', function() {});
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, 'displayError', function() {});
        });

        describe('in config mode', function() {
            beforeEach(function() {
                view.meta.config = true;
                view.render();
            });

            it('should call app.view.View.prototype.render.call', function() {
                expect(app.view.View.prototype.render.call).toHaveBeenCalled();
            });

            it('should call setAppUrlTitle', function() {
                expect(view.setAppUrlTitle).toHaveBeenCalled();
            });
        });

        describe('not in config mode', function() {
            beforeEach(function() {
                view.meta.config = false;
            });

            it('should call _super if allowApp is true', function() {
                view.allowApp = true;
                view.render();

                expect(view._super).toHaveBeenCalled();
            });

            it('should call displayError if allowApp is false', function() {
                view.allowApp = false;
                view.render();

                expect(view.displayError).toHaveBeenCalled();
            });
        });
    });

    describe('setAppUrlTitle()', function() {
        var servicesObj;

        beforeEach(function() {
            view.settings.set({
                src: 'https://test1'
            });
            servicesObj = {
                'https://test1': {
                    name: 'test1 name',
                    src: 'https://test1'
                },
                'https://test2': {
                    name: 'test2 name',
                    src: 'https://test2'
                }
            };
            view.servicesObj = servicesObj;
            sinon.collection.stub(view, '_render', function() {});

            view.setAppUrlTitle();
        });

        afterEach(function() {
            servicesObj = null;
        });

        it('should set the settings label with currentService name', function() {
            expect(view.settings.get('label')).toBe('test1 name');
        });

        it('should call _render', function() {
            expect(view._render).toHaveBeenCalled();
        });
    });

    describe('loadData()', function() {
        var completeFn;

        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, '_onSugarAppLoad', function() {});
            completeFn = sinon.collection.stub();
        });

        afterEach(function() {
            completeFn = null;
        });

        it('should call _super loadData if no complete fn is passed in', function() {
            view.loadData();

            expect(view._super).toHaveBeenCalledWith('loadData');
        });

        it('should call _onSugarAppLoad if complete fn is passed in and no parcelApp', function() {
            view.loadData({
                complete: completeFn
            });

            expect(view._onSugarAppLoad).toHaveBeenCalled();
        });

        it('should call complete fn if passed in', function() {
            view.loadData({
                complete: completeFn
            });

            expect(completeFn).toHaveBeenCalled();
        });
    });

    describe('_getAvailableServices()', function() {
        var options;

        beforeEach(function() {
            options = {
                module: 'Contacts'
            };
            sinon.collection.stub(app.metadata, 'getLayout', function() {
                return {
                    components: []
                };
            });
        });

        afterEach(function() {
            options = null;
        });

        it('should call getLayout with list-dashlet', function() {
            sinon.collection.stub(app.controller.context, 'get', function() {
                return 'records';
            });

            view._getAvailableServices(options);

            expect(app.metadata.getLayout).toHaveBeenCalledWith('Contacts', 'list-dashlet');
        });

        it('should call getLayout with record-dashlet', function() {
            sinon.collection.stub(app.controller.context, 'get', function() {
                return 'record';
            });

            view._getAvailableServices(options);

            expect(app.metadata.getLayout).toHaveBeenCalledWith('Contacts', 'record-dashlet');
        });
    });

    describe('_checkCatalogAccess()', function() {
        var options;
        var result;

        beforeEach(function() {
            options = {
                meta: {
                    src: 'test1'
                }
            };
        });

        afterEach(function() {
            options = null;
            result = null;
        });

        it('should return true if this service exists', function() {
            view.services = [{
                view: {
                    src: 'test1'
                }
            }];

            result = view._checkCatalogAccess(options);

            expect(result).toBeTruthy();
        });

        it('should return true if this service exists', function() {
            view.services = [];

            result = view._checkCatalogAccess(options);

            expect(result).toBeFalsy();
        });
    });
});
