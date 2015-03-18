describe('View.Views.Base.QuicksearchBarView', function() {
    var viewName = 'quicksearch-bar',
        view, layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        sinon.collection.stub(SugarTest.app.metadata, 'getModules', function() {
            var fakeModuleList = {
                Accounts: {ftsEnabled: true, globalSearchEnabled: true},
                Contacts: {ftsEnabled: true, globalSearchEnabled: true},
                ftsDisabled: {ftsEnabled: false, globalSearchEnabled: true},
                ftsNotSet: {},
                NoAccess: {ftsEnabled: true}
            };
            return fakeModuleList;
        });
        sinon.collection.stub(SugarTest.app.acl, 'hasAccess', function(action, module) {
            return module !== 'NoAccess';
        });
        sinon.collection.stub(SugarTest.app.api, 'isAuthenticated').returns(true);

        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', null, viewName, null, null, null, layout);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('populateModules', function() {
        it('Should show searchable modules only', function() {
            sinon.collection.stub(view, 'render');
            view.populateModules();
            expect(view.searchModules).toContain('Accounts');
            expect(view.searchModules).toContain('Contacts');
            expect(view.searchModules).not.toContain('ftsDisabled');
            expect(view.searchModules).not.toContain('ftsNotSet');
            expect(view.searchModules).not.toContain('NoAccess');
        });
    });

    describe('after populateModules is called', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'render');
            view.populateModules();
        });

        it('Should return search results', function() {

            sinon.collection.stub(SugarTest.app.metadata, 'getModule', function(module) {
                return {isBwcEnabled: module === 'bwcModule'};
            });
            sinon.collection.stub(SugarTest.app.api, 'search', function(params, cb) {
                var data = {
                    next_offset: -1,
                    records: [
                        {id: 'id1', name: 'test1', _module: 'Accounts', _search: {}},
                        {id: 'id2', name: 'test2', _module: 'bwcModule', _search: {}}
                    ]
                };
                cb.success(data);
            });

            var buildRouteSpy = sinon.collection.spy(SugarTest.app.router, 'buildRoute');
            var bwcBuildRouteSpy = sinon.collection.stub(SugarTest.app.bwc, 'buildRoute');
            view.fireSearchRequest('test');
            expect(buildRouteSpy.calledWith('Accounts', 'id1')).toBe(true);
            expect(bwcBuildRouteSpy.calledWith('bwcModule', 'id2')).toBe(true);
        });
    });

    describe('navigation', function() {
        var keyDisposeStub, triggerBeforeStub, triggerStub;
        beforeEach(function() {
            keyDisposeStub = sinon.collection.stub(view, 'disposeKeyEvents');
            triggerBeforeStub = sinon.collection.stub(view.layout, 'triggerBefore', function() {
                return true;
            });
            triggerStub = sinon.collection.stub(view.layout, 'trigger');
        });

        describe('moveForward', function() {
            it('should run the appropriate functions and fire the appropriate events when moving forward', function() {
                view.moveForward();
                expect(triggerBeforeStub).toHaveBeenCalledOnce();
                expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
                expect(keyDisposeStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledWith('navigate:next:component');
            });
        });

        describe('moveBackward', function() {
            it('should run the appropriate functions and fire the appropriate events when moving backward', function() {
                view.moveBackward();
                expect(triggerBeforeStub).toHaveBeenCalledOnce();
                expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:previous:component');
                expect(keyDisposeStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledWith('navigate:previous:component');
            });
        });

        describe('requestFocus', function() {
            it('should trigger navigate:to:component on the layout', function() {
                view.requestFocus();
                expect(triggerStub).toHaveBeenCalledOnce();
                expect(triggerStub).toHaveBeenCalledWith('navigate:to:component', viewName);
            });
        });

        describe('navigate:focus:lost', function() {
            it('should disposeKeyEvents', function() {
                view.trigger('navigate:focus:lost');
                //expect(keyDisposeStub).toHaveBeenCalledOnce();
                expect(keyDisposeStub).toHaveBeenCalled();
            });
        });
    });
});
