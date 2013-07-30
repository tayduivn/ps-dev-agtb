describe('Base.View.Dashletselect', function() {
    var moduleName = 'Home',
        app,
        sinonSandbox, view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        view = SugarTest.createView('base', moduleName, 'dashletselect');

    });
    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinon.collection.restore();
    });

    describe('get available dashlets', function() {
        it('should get all dashlet views that defines Dashlet plugin', function() {
            var customModule = 'RevenueLineItems';
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            sinon.collection.stub(app.view, 'componentHasPlugin', function() {
                return true;
            }, this);
            sinon.collection.stub(app.metadata, 'getModuleNames', function() {
                return [customModule];
            });
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    {
                        config: {}
                    }
                ]
            });
            //custom module dashlet
            SugarTest.testMetadata.addViewDefinition('piechart', {
                dashlets: [
                    {
                        config: {}
                    }
                ]
            }, customModule);
            view.loadData();
            var actual = view.context.get('dashlet_collection');
            expect(actual.length).toBe(2);
            expect(actual[0].type).toBe('dashablelist');
            expect(actual[1].type).toBe('piechart');
            expect(actual[1].metadata.module).toBe(customModule);
        });
        it('should get all sub dashlets that defines in dashlets array', function() {
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            sinon.collection.stub(app.view, 'componentHasPlugin', function() {
                return true;
            }, this);
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    {
                        name: 'first1',
                        config: {}
                    },
                    {
                        name: 'first2',
                        config: {}
                    }
                ]
            });
            view.loadData();
            var actual = view.context.get('dashlet_collection');
            expect(actual.length).toBe(2);
            expect(actual[0].type).toBe('dashablelist');
            expect(actual[1].type).toBe('dashablelist');
        });
    });

    describe('getFilteredList', function() {
        it('should get filtered dashlet list', function() {
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            sinon.collection.stub(app.controller.context, 'get', function(type) {
                if (type === 'module') {
                    return 'Home';
                }
                if (type === 'layout') {
                    return 'records';
                }
            });
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    //Matched module and layout
                    {
                        name: 'first1',
                        config: {},
                        filter: {
                            module: [
                                'Home'
                            ],
                            view: 'records'
                        }
                    },
                    //Mismatched the module (Excluded)
                    {
                        name: 'first2',
                        config: {},
                        filter: {
                            module: [
                                'Accounts',
                                'Contacts'
                            ]
                        }
                    },
                    //Matched module without filtering view
                    {
                        name: 'first3',
                        config: {},
                        filter: {
                            module: [
                                'Home',
                                'Contacts'
                            ]
                        }
                    },
                    //Mismatched the view with matched module (Excluded)
                    {
                        name: 'first4',
                        config: {},
                        filter: {
                            module: [
                                'Home'
                            ],
                            view: 'record'
                        }
                    }
                ]
            });
            view.loadData();
            var actualCollection = view.context.get('dashlet_collection'),
                actualList = view.getFilteredList();

            expect(actualCollection.length).toBe(4);
            expect(actualList.length).toBe(2);
        });
    });
});
