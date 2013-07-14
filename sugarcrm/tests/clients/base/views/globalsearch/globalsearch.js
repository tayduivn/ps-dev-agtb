describe("Global Search", function() {
    var moduleName = 'Accounts',
        viewName = 'globalsearch',
        view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        sinon.collection.stub(SugarTest.app.metadata, 'getModules', function() {
            return {
                Accounts: {ftsEnabled:true, globalSearchEnabled: true},
                Contacts: {ftsEnabled:true, globalSearchEnabled: true},
                ftsDisabled: {ftsEnabled:false, globalSearchEnabled: true},
                ftsNotSet: {},
                NoAccess: {ftsEnabled: true}
            }
        });
        sinon.collection.stub(SugarTest.app.acl, 'hasAccess', function(action,module) {
            if (module === 'NoAccess') {
                return false;
            } else {
                return true;
            }
        });
        sinon.collection.stub(SugarTest.app.api, 'isAuthenticated', function() {
            return true;
        });
        view = SugarTest.createView("base", moduleName, "globalsearch", null, null);
        view.populateModules();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
        view = null;
    });

    it("Should show searchable modules only", function() {
        var modules = _.map(view.$('[data-module]'), function(elem) {
            return $(elem).data('module');
        });
        expect(modules).toContain('all');
        expect(modules).toContain('Accounts');
        expect(modules).toContain('Contacts');
        expect(modules).not.toContain('ftsDisabled');
        expect(modules).not.toContain('ftsNotSet');
        expect(modules).not.toContain('NoAccess');
    });
    it("Should only show global search enabled modules", function() {
        var actual,
            acl = {
                hasAccess: function() {
                    return true;
                }
            },
            moduleNames = ['Bugs','Cases','KBDocuments','Home'],
            modules = {
                Bugs: {globalSearchEnabled:true},
                Cases: {globalSearchEnabled:true},
                KBDocuments: {globalSearchEnabled:true},
                Home: {globalSearchEnabled: false}
            };
        actual = view.populateSearchableModules({
            modules: modules,
            moduleNames: moduleNames,
            acl: acl,
            checkFtsEnabled: false,
            checkGlobalSearchEnabled: true
        });
        expect(_.contains(actual, 'Bugs')).toBeTruthy();
        expect(_.contains(actual, 'Cases')).toBeTruthy();
        expect(_.contains(actual, 'KBDocuments')).toBeTruthy();
        expect(_.contains(actual, 'Home')).toBeFalsy();
    });
    it("Should check 'Search all' and uncheck other modules by default", function() {
        var checkedModules = _.map(view.$('input:checkbox:checked[data-module]'), function(elem) {
            return $(elem).data('module');
        });
        expect(checkedModules).toContain('all');
        expect(checkedModules).not.toContain('Accounts');
        expect(checkedModules).not.toContain('Contacts');
    });

    it("Should uncheck 'Search all' when any module is selected", function() {
        var accountModule = view.$('input:checkbox[data-module="Accounts"]');
        // Set 'checked' to true now because click() will do this only after event is triggered
        // There may be a better way to simulate 'check a checkbox'
        accountModule.attr('checked', true);
        accountModule.click();
        expect(view.$('input:checkbox[data-module="all"]').attr('checked')).toBeUndefined();
    });

    it("Should check 'Search all' when no module is selected", function() {
        var accountModule = view.$('input:checkbox[data-module="Accounts"]');
        // 'Search all' should be checked now
        accountModule.attr('checked', true);
        accountModule.click();
        // 'Search all' should be unchecked now
        accountModule.removeAttr('checked');
        accountModule.click();
        // 'Search all' should be checked now
        expect(view.$('input:checkbox[data-module="all"]').attr('checked')).toBeDefined();
    });

    it('Should return search results', function() {

        sinon.collection.stub(SugarTest.app.metadata, 'getModule', function(module) {
            return {isBwcEnabled: module === 'bwcModule'};
        });
        sinon.collection.stub(SugarTest.app.api, 'search', function(params, cb) {
            var data = {
                next_offset: -1,
                records: [
                    {id: 'test1', name: 'test1', _module: 'Accounts', _search: {}},
                    {id: 'test2', name: 'test2', _module: 'bwcModule', _search: {}}
                ]
            };
            cb.success(data);
        });

        var buildRouteSpy = sinon.collection.spy(SugarTest.app.router, 'buildRoute');
        var bwcBuildRouteSpy = sinon.collection.stub(SugarTest.app.bwc, 'buildRoute');
        view.fireSearchRequest('test', {
            provide: function(data) {
                return data;
            }
        });
        expect(buildRouteSpy.calledWith('Accounts', 'test1')).toBe(true);
        expect(bwcBuildRouteSpy.calledWith('bwcModule', 'test2')).toBe(true);
    });

    it("Should fire search request when 'enter' key is typed", function() {
        var searchSpy = sinon.collection.stub(view, 'fireSearchRequest');
        var e = jQuery.Event("keyup");
        e.keyCode = $.ui.keyCode.ENTER;
        view.$('.search-query').focus();
        view.$('.search-query').val('abc');
        view.$('.search-query').trigger(e);
        expect(searchSpy).toHaveBeenCalled();
    });

    it("Should fire search request when search button is clicked", function() {
        var searchSpy = sinon.collection.stub(view, 'fireSearchRequest');
        view.$('.search-query').val('abc');
        view.$('.icon-search').click();
        expect(searchSpy).toHaveBeenCalled();
    });

    it("Should not fire search request when search field is empty", function() {
        var searchSpy = sinon.collection.stub(view, 'fireSearchRequest');
        view.$('.search-query').val('');
        view.$('.icon-search').click();
        expect(searchSpy).not.toHaveBeenCalled();
    });
});
