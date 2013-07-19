describe("BaseFilterModuleDropdownView", function () {
    var view, layout, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'filter-module-dropdown');
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', "Cases", "filter", {}, null, null, { layout: new Backbone.View() });
        view = SugarTest.createView("base", "Cases", "filter-module-dropdown", null, null, null, layout);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function () {
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('handleChange callback of filter:change:module', function() {
        var layoutStub;

        beforeEach(function() {
            layoutStub = sinon.stub(view.layout, 'trigger');
            view.filterNode = $('');
        });

        afterEach(function() {
            layoutStub.restore();
        });

        it('should only trigger subpanel:change if linkName is all_modules and silent is true', function() {
            var silent = true;
            view.handleChange('Cases', 'all_modules', silent);

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[0]).toEqual('subpanel:change');
            expect(layoutStub.secondCall).toBeNull();
        });

        it('should trigger subppanel:change if linkName is all_modules and silent is false', function() {
            var silent = false;
            view.handleChange('Cases', 'all_modules', silent);

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[0]).toEqual('subpanel:change');
            expect(layoutStub.secondCall.args[0]).toEqual('filter:get');
        });

        it('should trigger filter:create:close if link is not all_modules', function() {
            var silent = true;
            view.handleChange('Cases', 'Contacts', silent);

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[0]).toEqual('filter:create:close');
            expect(layoutStub.secondCall.args[0]).toEqual('subpanel:change');
        });

        it('should do same as above with filter:get because silent is false', function() {
            var silent = false;
            view.handleChange('Cases', 'Contacts', silent);

            expect(layoutStub).toHaveBeenCalled();
            expect(layoutStub.firstCall.args[0]).toEqual('filter:create:close');
            expect(layoutStub.secondCall.args[0]).toEqual('subpanel:change');
            expect(layoutStub.thirdCall.args[0]).toEqual('filter:get');
        });
    });

    describe('filterList', function() {

        it('gets module list for Activity Stream', function() {
            var expected, filterList;
            expected = [{ id: 'Activities', text: app.lang.get('LBL_MODULE_NAME', 'Cases')}];
            filterList = view.getModuleListForActivities();
            expect(filterList).toEqual(expected);
            view.module = 'Activities';
            expected = [{ id: 'Activities', text: app.lang.get("LBL_TABGROUP_ALL")}];
            filterList = view.getModuleListForActivities();
            expect(filterList).toEqual(expected);
        });

        it('gets module list for Records layout', function() {
            var expected, filterList;
            expected = [{ id: 'Cases', text: app.lang.get('LBL_MODULE_NAME', 'Cases')}];
            filterList = view.getModuleListForRecords();
            expect(filterList).toEqual(expected);
        });

        it('gets module list for Subpanels', function() {
            var metadataStub = sinon.stub(app.utils, 'getSubpanelList', function(module) {
                return {"LBL_CONTACTS_SUBPANEL_TITLE":'cases'};
            });
            var dataStub = sinon.stub(app.data, 'getRelatedModule', function(module, link) {
                return 'Cases';
            })
            var expected, filterList;
            expected = [{ id: 'all_modules', text: app.lang.get("LBL_TABGROUP_ALL")},
                        { id: 'cases', text: app.lang.get("LBL_CONTACTS_SUBPANEL_TITLE") }];
            filterList = view.getModuleListForSubpanels();
            expect(filterList).toEqual(expected);
            metadataStub.restore();
            dataStub.restore();
        });

        it('gets module list for Subpanels without hidden subpanels', function() {
            //Contacts is hidden
            var metadataStub = sinon.stub(app.utils, 'getSubpanelList', function(module) {
                return {"LBL_MODULE_NAME":'cases', "LBL_CONTACTS_SUBPANEL_TITLE":'contacts'};
            });
            var dataStub = sinon.stub(app.data, 'getRelatedModule', function(module, link) {
                if(link === 'cases'){
                    return 'Cases';
                }
                if(link === 'contacts'){
                    return 'Contacts';
                }
            })
            var expected, filterList;
            expected = [{ id: 'all_modules', text: app.lang.get("LBL_TABGROUP_ALL")},
                { id: 'cases', text: app.lang.get("LBL_MODULE_NAME") }];
            filterList = view.getModuleListForSubpanels();
            expect(filterList).toEqual(expected);
            metadataStub.restore();
            dataStub.restore();
        });
    });

    describe('pullSubpanelRelationships', function() {

        it('should return subpanels metadata', function() {
            var metadataStub = sinon.stub(app.utils, 'getSubpanelList', function(module) {
                return {"LBL_CONTACTS_SUBPANEL_TITLE":'contacts'};
            });
            var subpanels = view.pullSubpanelRelationships();
            expect(subpanels).toEqual({'LBL_CONTACTS_SUBPANEL_TITLE' : 'contacts'});
            metadataStub.restore();
        });
    });

    describe('select2 options', function() {

        it('should initSelection for selected module', function() {
            var $input = $('<input type="text">').val('all_modules'),
                callback = sinon.stub(),
                expected = { id: 'all_modules', text: app.lang.get("LBL_MODULE_NAME", 'Cases')};

            view.initSelection($input, callback);

            expect(callback).toHaveBeenCalled();
            expect(callback.lastCall.args[0]).toEqual(expected);
            callback.reset();

            $input.val('Cases');
            expected = { id: 'Cases', text: app.lang.get("LBL_MODULE_NAME", 'Cases')};

            view.filterList = [expected];
            view.initSelection($input, callback);

            expect(callback).toHaveBeenCalled();
            expect(callback.lastCall.args[0]).toEqual(expected);
        });


        it('should formatSelection for selected module', function() {
            var expected = app.lang.get("LBL_MODULE"),
                html;

            //Template replacement
            view._select2formatSelectionTemplate = function(val) { return val; };

            html = view.formatSelection({id: 'test', text: 'TEST'});

            expect(html).toEqual(expected);

            view.layout.layoutType = 'record';
            view.layout.showingActivities = false;

            html = view.formatSelection({id: 'test', text: 'TEST'});
            expected = app.lang.get("LBL_RELATED") + '<i class="icon-caret-down"></i>';
            expect(html).toEqual(expected);
        });

        it('should formatResult for selected module', function() {
            var expected = 'TEST',
                html;

            //Template replacement
            view._select2formatResultTemplate = function(val) { return val; };

            html = view.formatResult({id: 'test', text: 'TEST'});

            expect(html).toEqual(expected);
        });
    });
});
