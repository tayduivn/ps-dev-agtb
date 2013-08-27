describe('View.BaseDashablelistView', function() {
    var app,
        view,
        sampleFieldMetadata = [{name: 'foo'}, {name: 'bar'}],
        sampleColumns = {foo: 'foo', bar: 'bar'};

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Home', 'dashablelist');
        view.settings = app.data.createBean('Home');
        view._availableModules = {Accounts: 'Accounts', Contacts: 'Contacts'};
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });

    describe('initialize the dashlet', function() {
        describe('init dashlet workflow', function() {
            var stubInitializeSettings,
                stubConfigureDashlet,
                stubDisplayDashlet;

            beforeEach(function() {
                view.meta = {};
                view.settings._events = {};
                stubInitializeSettings = sinon.collection.stub(view, '_initializeSettings');
                stubConfigureDashlet = sinon.collection.stub(view, '_configureDashlet');
                stubDisplayDashlet = sinon.collection.stub(view, '_displayDashlet');
            });

            it('should call BaseDashablelistView#_configureDashlet when in config mode', function() {
                view.meta.config = true;
                view.initDashlet('config');
                expect(stubInitializeSettings).toHaveBeenCalledOnce();
                expect(stubConfigureDashlet).toHaveBeenCalledOnce();
                expect(stubDisplayDashlet).not.toHaveBeenCalled();
                expect(_.isEmpty(_.pick(view.settings._events, 'change:module'))).toBeFalsy();
            });

            it('should call BaseDashablelistView#_displayDashlet when in preview mode', function() {
                view.meta.preview = true;
                view.initDashlet('preview');
                expect(stubInitializeSettings).toHaveBeenCalledOnce();
                expect(stubConfigureDashlet).not.toHaveBeenCalled();
                expect(stubDisplayDashlet).toHaveBeenCalledOnce();
                expect(_.isEmpty(_.pick(view.settings._events, 'change:module'))).toBeTruthy();
            });

            it('should call BaseDashablelistView#_displayDashlet when in view mode', function() {
                view.initDashlet('view');
                expect(stubInitializeSettings).toHaveBeenCalledOnce();
                expect(stubConfigureDashlet).not.toHaveBeenCalled();
                expect(stubDisplayDashlet).toHaveBeenCalledOnce();
                expect(_.isEmpty(_.pick(view.settings._events, 'change:module'))).toBeTruthy();
            });
        });

        describe('setting default options', function() {
            var firstAvailableModule;

            beforeEach(function() {
                firstAvailableModule = _.first(_.keys(view._availableModules));
            });

            it('should default all undefined settings', function() {
                sinon.collection.stub(app.lang, 'get').returnsArg(1);
                view._initializeSettings();
                expect(view.settings.get('module')).toBe(firstAvailableModule);
                expect(view.settings.get('label')).toBe(firstAvailableModule);
                expect(view.settings.get('limit')).toBe(5);
                expect(view.settings.get('my_items')).toBe('1');
                expect(view.settings.get('favorites')).toBe('0');
            });

            it('should not change the module setting when the module is approved', function() {
                var module = 'Contacts';
                view.settings.set('module', module);
                view._setDefaultModule();
                expect(view.settings.get('module')).toBe(module);
            });

            it('should change the module setting to the first available module when the module is unapproved',
               function() {
                   view.settings.set('module', 'Leads');
                   view._setDefaultModule();
                   expect(view.settings.get('module')).toBe(firstAvailableModule);
               }
            );
        });
    });

    describe('configure the dashlet', function() {
        it('should update the label and columns when the module is changed', function() {
            var oldModule = 'Accounts',
                newModule = 'Contacts',
                stubUpdateDisplayColumns = sinon.collection.stub(view, '_updateDisplayColumns');
            sinon.collection.stub(view, '_initializeSettings');
            sinon.collection.stub(view, '_configureDashlet');
            sinon.collection.stub(app.lang, 'get').returnsArg(1);
            view.meta = {config: true};
            view.settings.set('module', oldModule);
            view.settings.set('label', 'Foo');
            view.initDashlet('config');
            expect(_.isEmpty(_.pick(view.settings._events, 'change:module'))).toBeFalsy();
            view.settings.set('module', newModule);
            expect(view.settings.get('label')).toBe(newModule);
            expect(stubUpdateDisplayColumns).toHaveBeenCalledOnce();
        });

        it('should run through all of the logic necessary to render the dashlet configuration view', function() {
            var module = 'Accounts';
            view.settings.set('module', module);
            _.extend(view._availableModules, {Leads: 'Leads'});
            view._availableColumns = {};
            view._availableColumns[module] = sampleColumns;
            view.meta = {
                panels: [
                    {
                        fields: [{name: 'module'}, {name: 'display_columns'}]
                    }
                ]
            };
            view._configureDashlet();
            var fieldMeta = view.getFieldMetaForView(view.meta),
                moduleField = _.findWhere(fieldMeta, {name: 'module'}),
                columnsField = _.findWhere(fieldMeta, {name: 'display_columns'});
            expect(moduleField.options).toEqual(view._availableModules);
            expect(columnsField.options).toEqual(view._availableColumns[module]);
        });

        describe('get the approved modules', function() {
            var stubAppUserGet,
                stubGetFieldMetaForView;

            beforeEach(function() {
                sinon.collection.stub(app.lang, 'get').returnsArg(1);
                stubGetFieldMetaForView = sinon.collection.stub(view, 'getFieldMetaForView');
                stubAppUserGet = sinon.collection.stub(app.user, 'get');
                stubAppUserGet.withArgs('module_list').returns(_.extend({Leads: 'Leads'}, view._availableModules));
            });

            it('should cache and return the approved modules', function() {
                view._availableModules = {};
                stubGetFieldMetaForView.returns(sampleFieldMetadata);
                var modules = view._getAvailableModules();
                expect(modules).toEqual({Accounts: 'Accounts', Contacts: 'Contacts', Leads: 'Leads'});
            });

            it('should not cache and return unapproved modules', function() {
                view._availableModules = {};
                view.moduleBlacklist.push('Accounts');
                stubGetFieldMetaForView.returns(sampleFieldMetadata);
                var modules = view._getAvailableModules();
                expect(modules).toEqual({Contacts: 'Contacts', Leads: 'Leads'});
            });

            it('should not cache and return modules without a list view', function() {
                view._availableModules = {};
                stubGetFieldMetaForView.returns([]);
                var modules = view._getAvailableModules();
                expect(modules).toEqual({});
            });

            it('should return the approved modules from cache', function() {
                var modules = view._getAvailableModules();
                expect(stubAppUserGet).not.toHaveBeenCalled();
                expect(modules).toEqual({Accounts: 'Accounts', Contacts: 'Contacts'});
            });
        });

        describe('get the available columns', function() {
            it('should cache and return columns from the module list view', function() {
                var module = 'Accounts';
                sinon.collection.stub(view, 'getFieldMetaForView').returns(sampleFieldMetadata);
                sinon.collection.stub(app.lang, 'get').returnsArg(0);
                view.settings.set('module', module);
                view._availableColumns = {};
                var columns = view._getAvailableColumns();
                expect(columns).toEqual(sampleColumns);
                expect(view._availableColumns[module]).toEqual(sampleColumns);
            });

            it('should return the available columns from cache', function() {
                var module = 'Accounts';
                view.settings.set('module', module);
                view._availableColumns[module] = sampleFieldMetadata;
                var columns = view._getAvailableColumns();
                expect(columns).toEqual(sampleFieldMetadata);
            });

            it('should return an empty set when the module is not set', function() {
                var columns = view._getAvailableColumns();
                expect(columns).toEqual({});
            });
        });

        describe('update the display_columns field/attribute', function() {
            var module;

            beforeEach(function() {
                module = 'Accounts';
                view._availableColumns = {};
                view._availableColumns[module] = sampleColumns;
                view.settings.set('module', module);
            });

            it('should update only the attribute when there is no DOM field', function() {
                var stubGetField = sinon.collection.stub(view, 'getField');
                stubGetField.withArgs('display_columns').returns(null);
                view._updateDisplayColumns();
                expect(view.settings.get('display_columns')).toEqual(_.keys(sampleColumns));
            });

            it('should update both the attribute and the field when there is a DOM field', function() {
                var field = {},
                    stubGetField = sinon.collection.stub(view, 'getField');
                stubGetField.withArgs('display_columns').returns(field);
                view._updateDisplayColumns();
                expect(view.settings.get('display_columns')).toEqual(_.keys(sampleColumns));
                expect(field.items).toEqual(view._availableColumns[module]);
            });
        });
    });

    describe('view the dashlet', function() {
        it('should run through all of the logic necessary to render the dashlet', function() {
            var columns = _.map(sampleFieldMetadata, function(column) {
                    return _.extend(column, {sortable: true});
                }),
                stubStartAutoRefresh = sinon.collection.stub(view, '_startAutoRefresh');
            sinon.collection.stub(view, '_getColumnsForDisplay').returns(columns);
            view.settings.set('limit', 5);
            view.meta = {panels: []};
            view._displayDashlet();
            expect(view.context.get('skipFetch')).toBeFalsy();
            expect(view.context.get('limit')).toBe(5);
            expect(view.meta.panels).toEqual([{fields: columns}])
            expect(stubStartAutoRefresh).toHaveBeenCalledOnce();
        });

        describe('get the columns to include in the list', function() {
            var displayColumns = _.union(_.pluck(sampleFieldMetadata, 'name'), 'qux'),
                fieldMeta = [{name: 'foo', sortable: false}, {name: 'baz'}];

            beforeEach(function() {
                sinon.collection.stub(view, 'getFieldMetaForView').returns(fieldMeta);
            });

            it('should merge the field metadata onto the display_columns and return those fields', function() {
                view.settings.set('display_columns', displayColumns);
                var columns = view._getColumnsForDisplay();
                // "baz" should not have been added because only fields from display_columns should be used
                expect(columns.length).toBe(displayColumns.length);
                // "foo" should not be sortable
                var first = columns.shift();
                expect(first.sortable).toBeFalsy();
                // all other columns should be sortable
                var rest = _.every(columns, function(column) {
                    return true === column.sortable;
                });
                expect(rest).toBeTruthy();
            });

            it('should return an empty array when display_columns is set but has no fields', function() {
                view.settings.set('display_columns', []);
                var columns = view._getColumnsForDisplay();
                expect(columns.length).toBe(0);
            });

            it('should call BaseDashablelistView#_updateDisplayColumns when display_columns is undefined', function() {
                var stubUpdateDisplayColumns = sinon.collection.stub(view, '_updateDisplayColumns', function() {
                    view.settings.set('display_columns', displayColumns);
                });
                var columns = view._getColumnsForDisplay();
                expect(stubUpdateDisplayColumns).toHaveBeenCalledOnce();
                expect(columns.length).toBe(displayColumns.length);
            });
        });

        describe('set the filter definition on the collection', function() {
            it('should add an empty filter definition', function() {
                view.settings.set({my_items: '0', favorites: '0'});
                view._intializeFilter();
                expect(view.context.get('collection').filterDef.length).toBe(0);
            });

            it('should add only one option to the filter definition', function() {
                view.settings.set({my_items: '1', favorites: '0'});
                view._intializeFilter();
                var filterDef = view.context.get('collection').filterDef;
                expect(_.isArray(filterDef)).toBeTruthy();
                expect(view.context.get('collection').filterDef.length).toBe(1);
            });

            it('should add two options to the filter definition', function() {
                view.settings.set({my_items: '1', favorites: '1'});
                view._intializeFilter();
                var filterDef = view.context.get('collection').filterDef;
                expect(_.isObject(filterDef)).toBeTruthy();
                expect(_.first(_.keys(filterDef))).toBe('$and');
                expect(filterDef.$and.length).toBe(2);
            });
        });
    });
});
