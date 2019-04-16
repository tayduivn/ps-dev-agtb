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
describe('Base.Layout.TabbedLayout', function() {
    var app;
    var layout;
    var initOptions;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Accounts', 'tabbed-layout', {});
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
        app = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, 'updateLayoutConfig');
        });

        it('should set meta.notabs if components are 1 or less', function() {
            initOptions = {
                meta: {
                    components: [{
                        name: 'test1',
                        type: 'base'
                    }, {
                        name: 'test2',
                        type: 'base'
                    }]
                }
            };
            layout.initialize(initOptions);

            expect(layout.meta.notabs).toBeFalsy();
        });

        it('should set meta.notabs if components are 1 or less', function() {
            initOptions = {
                meta: {
                    components: [{
                        name: 'test1',
                        type: 'base'
                    }]
                }
            };
            layout.initialize(initOptions);

            expect(layout.meta.notabs).toBeTruthy();
        });

        it('should call updateLayoutConfig', function() {
            initOptions = {
                meta: {
                    components: [{
                        name: 'test1',
                        type: 'base'
                    }]
                }
            };
            layout.initialize(initOptions);

            expect(layout.updateLayoutConfig).toHaveBeenCalled();
        });
    });

    describe('addComponent()', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, '_super');
            sinon.collection.stub(layout, '_addTabNavControls');
        });

        afterEach(function() {
            layout._components = [];
        });

        it('should not call _addTabNavControls if notabs is true', function() {
            layout._components = [{
                name: 'test1',
                type: 'base'
            }, {
                name: 'test2',
                type: 'base'
            }, {
                name: 'test3',
                type: 'base'
            }, {
                name: 'test4',
                type: 'base'
            }];
            layout.meta.components = layout._components;
            layout.meta.notabs = true;
            layout.addComponent({}, {});

            expect(layout._addTabNavControls).not.toHaveBeenCalled();
        });

        it('should not call _addTabNavControls if _components doesnt equal meta.components', function() {
            layout._components = [{
                name: 'test1',
                type: 'base'
            }, {
                name: 'test2',
                type: 'base'
            }, {
                name: 'test3',
                type: 'base'
            }];
            layout.meta.components = [{
                name: 'test1',
                type: 'base'
            }, {
                name: 'test2',
                type: 'base'
            }, {
                name: 'test3',
                type: 'base'
            }, {
                name: 'test4',
                type: 'base'
            }];
            layout.meta.notabs = false;
            layout.addComponent({}, {});

            expect(layout._addTabNavControls).not.toHaveBeenCalled();
        });

        it('should not call _addTabNavControls if _components length is <= maxTabs', function() {
            layout._components = [{
                name: 'test1',
                type: 'base'
            }, {
                name: 'test2',
                type: 'base'
            }, {
                name: 'test3',
                type: 'base'
            }];
            layout.meta.components = layout._components;
            layout.meta.notabs = false;
            layout.maxTabs = 3;
            layout.addComponent({}, {});

            expect(layout._addTabNavControls).not.toHaveBeenCalled();
        });

        it('should call _addTabNavControls when conditions are right', function() {
            layout._components = [{
                name: 'test1',
                type: 'base'
            }, {
                name: 'test2',
                type: 'base'
            }, {
                name: 'test3',
                type: 'base'
            }, {
                name: 'test4',
                type: 'base'
            }];
            layout.meta.components = layout._components;
            layout.meta.notabs = false;
            layout.maxTabs = 3;
            layout.addComponent({}, {});

            expect(layout._addTabNavControls).toHaveBeenCalled();
        });
    });

    describe('_addTabNavControls()', function() {
        var appendStub;

        beforeEach(function() {
            appendStub = sinon.collection.stub();
            sinon.collection.stub(layout, '$', function() {
                return {
                    append: appendStub
                };
            });
        });

        afterEach(function() {
            appendStub = null;
        });

        it('should append a list element', function() {
            layout._addTabNavControls();

            expect(appendStub).toHaveBeenCalled();
        });
    });

    describe('_placeComponent()', function() {
        var appendStub;

        beforeEach(function() {
            appendStub = sinon.collection.stub();
            sinon.collection.stub(layout, '$', function() {
                return {
                    append: appendStub
                };
            });
        });

        afterEach(function() {
            appendStub = null;
        });

        it('should append a list element', function() {
            layout._addTabNavControls();

            expect(appendStub).toHaveBeenCalled();
        });
    });

    describe('removeComponent()', function() {
        var appendStub;
        var comp;
        var def;

        beforeEach(function() {
            appendStub = sinon.collection.stub();
            sinon.collection.stub(layout, '$', function() {
                return {
                    append: appendStub
                };
            });
            sinon.collection.stub(app.lang, 'get', function() {
                return 'test';
            });
            comp = {
                el: '<div class="test"></div>'
            };
        });

        afterEach(function() {
            appendStub = null;
            comp = null;
            def = null;
        });

        it('should work off def.layout', function() {
            def = {
                layout: {
                    label: 'testLabel'
                }
            };
            layout._placeComponent(comp, def);

            expect(app.lang.get).toHaveBeenCalledWith('testLabel');
        });

        it('should work off def.view', function() {
            def = {
                view: {
                    label: 'testLabel'
                }
            };
            layout._placeComponent(comp, def);

            expect(app.lang.get).toHaveBeenCalledWith('testLabel');
        });

        it('should get label key from compDef.label', function() {
            def = {
                layout: {
                    label: 'testLabel'
                }
            };
            layout._placeComponent(comp, def);

            expect(app.lang.get).toHaveBeenCalledWith('testLabel');
        });

        it('should get label key from compDef.name', function() {
            def = {
                layout: {
                    name: 'testName'
                }
            };
            layout._placeComponent(comp, def);

            expect(app.lang.get).toHaveBeenCalledWith('testName');
        });

        it('should get label key from compDef.type', function() {
            def = {
                layout: {
                    type: 'testType'
                }
            };
            layout._placeComponent(comp, def);

            expect(app.lang.get).toHaveBeenCalledWith('testType');
        });
    });
});
