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

describe('Dashboards.Base.View.DashboardHeaderpane', function() {
    var app;
    var view;
    var sandbox = sinon.sandbox.create();

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'dashboard-headerpane', 'Dashboards');

        app.routing.start();
    });

    afterEach(function() {
        sandbox.restore();
        app.router.stop();

        app.cache.cutAll();
        app.view.reset();

        view.layout = null;
        view.context = null;
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should create a RHS dashboard and enter edit mode', function() {
            var context = new app.Context({
                model: app.data.createBean('Dashboards'),
                create: true
            });
            context.parent = new app.Context({
                module: 'Accounts'
            });
            sandbox.stub(app.metadata, 'getView')
                .withArgs('Accounts', 'dashboard-headerpane', 'Dashboards')
                .returns('dashboard metadata');
            sandbox.stub(app.template, 'getView').withArgs('dashboard-headerpane')
                .returns($.noop);

            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane', null, context, true);

            expect(view.changed).toBeTruthy();
            expect(view.action).toEqual('edit');
            expect(view.inlineEditMode).toBeTruthy();
        });
    });

    describe('duplicateClicked', function() {
        var oldName;
        var prefill;
        var unsetStub;
        var saveStub;
        var appLangStub;

        beforeEach(function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            oldName = 'oldName';

            // create bean stub so that we can stub its method
            prefill = app.data.createBean('Dashboards', {name: oldName, module: 'Dashboards'});
            prefill.set('id', 'new_id');
            sandbox.stub(app.data, 'createBean').withArgs('Dashboards').returns(prefill);

            unsetStub = sandbox.stub(prefill, 'unset');
            saveStub = sandbox.stub(prefill, 'save');
            sandbox.stub(prefill, 'copy');
            appLangStub = sandbox.stub(app.lang, 'get');

            appLangStub.withArgs(oldName, 'Accounts').returns(oldName);
            appLangStub.withArgs(oldName, 'Home').returns(oldName);
            appLangStub.withArgs('LBL_COPY_OF', 'Dashboards', {name: oldName}).returns('Copy of oldName');
        });

        afterEach(function() {
            prefill = null;
        });

        it('should save the new RHS Dashboard model and navigate to it', function() {
            var contextBro = new app.Context();
            contextBro.set('collection', app.data.createBeanCollection('Dashboards', [view.model]));
            prefill.set('dashboard_module', 'Accounts');
            view.context.parent = {
                getChildContext: function() {
                    return contextBro;
                },
                get: function() {
                    return 'record';
                }
            };
            view.layout = {
                navigateLayout: sandbox.stub()
            };

            saveStub.withArgs({
                name: 'Copy of oldName',
                my_favorite: true
            }).yieldsToOn('success', view);

            view.duplicateClicked();

            expect(unsetStub.lastCall.args[0]).toEqual({
                id: void 0,
                assigned_user_id: void 0,
                assigned_user_name: void 0,
                team_name: void 0,
                default_dashboard: void 0
            });
            expect(view.layout.navigateLayout).toHaveBeenCalledWith('new_id');
            expect(contextBro.get('collection').length).toEqual(2);

            contextBro = null;
        });

        it('should save the new Home Dashboard model and navigate to it', function() {
            var navigateStub = sandbox.stub(app.router, 'navigate');

            prefill.set('dashboard_module', 'Home');
            sandbox.stub(app.router, 'buildRoute')
                .withArgs(view.module, prefill.get('id')).returns('NewModelRoute');

            saveStub.withArgs({
                name: 'Copy of oldName',
                my_favorite: true
            }).yieldsToOn('success', view);

            view.duplicateClicked();

            expect(navigateStub).toHaveBeenCalledWith('NewModelRoute', {trigger: true});
        });

        it('should show an error alert when saving fails', function() {
            var alertStub = sandbox.stub(app.alert, 'show');
            prefill.set('dashboard_module', 'Home');

            saveStub.withArgs({
                name: 'Copy of oldName',
                my_favorite: true
            }).yieldsTo('error');

            view.duplicateClicked();

            expect(alertStub).toHaveBeenCalled();
        });
    });

    describe('toggleNameField', function() {
        it('should call toggleField', function() {
            var field = 'test';
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            sandbox.stub(view, 'getField').returns(field);
            view.toggleField = sandbox.stub();

            view.toggleNameField(true);
            expect(view.toggleField).toHaveBeenCalledWith(field, true);
        });
    });

    describe('saveHandle', function() {
        it('should call handleSave', function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');

            sandbox.stub(view.model, 'changedAttributes').returns({'name': true});
            view.layout = {
                handleSave: sinon.stub(),
            };
            view.setButtonStates = sandbox.stub();
            view.toggleEdit = sandbox.stub();

            view.saveHandle();
            expect(view.layout.handleSave).toHaveBeenCalled();
        });
    });

    describe('editOverviewTabClicked', function() {
        it('should switch tab and call editClicked', function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            view.context = {
                trigger: sandbox.stub(),
                get: function() {
                    return 1;
                }
            };
            var editClickedStub = sandbox.stub(view, 'editClicked');
            view.editOverviewTabClicked();
            expect(view.context.trigger).toHaveBeenCalledWith('tabbed-dashboard:switch-tab', 0);
            expect(editClickedStub).toHaveBeenCalled();
        });
    });

    describe('editModuleTabsClicked', function() {
        it('should open drawer', function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            app.drawer = {
                open: sinon.stub()
            };
            view.editModuleTabsClicked();
            expect(app.drawer.open).toHaveBeenCalled();
        });
    });

    describe('hasUnsavedChanges', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
        });

        it('should return false if the model has no change', function() {
            sandbox.stub(view.model, 'save', function(attrs) {
                _.extend(view.model.changed, attrs);
            });

            // new model that has no change
            expect(view.hasUnsavedChanges()).toBeFalsy();

            view.model.set('id', 'model_id');

            // no change to existing model
            expect(view.hasUnsavedChanges()).toBeFalsy();

            // the only change to an existing model is my_favorite
            view.model.favorite(true);
            view.model.setSyncedAttributes({my_favorite: true});

            expect(view.hasUnsavedChanges()).toBeFalsy();
        });

        it('should return true if the model has been changed', function() {
            // model that is updated
            view.model.set('updated', true);

            expect(view.hasUnsavedChanges()).toBeTruthy();
            view.model.unset('updated');

            // new model that has change
            view.model.set('name', 'new model');

            expect(view.hasUnsavedChanges()).toBeTruthy();
            view.model.unset('name');

            // existing model that is modified
            view.model.set({
                updated: false,
                id: 'model_id',
                name: 'old model'
            });
            sandbox.stub(view.model, 'changedAttributes').returns({name: 'new model'});

            expect(view.hasUnsavedChanges()).toBeTruthy();
        });
    });

    describe('handleDelete', function() {
        var alertStub;
        var destroyStub;

        beforeEach(function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            alertStub = sandbox.stub(app.alert, 'show');
            destroyStub = sandbox.stub(view.model, 'destroy');
        });

        it('should navigate to fallback Home dashboard after deleting current one', function() {
            sandbox.stub(app.router, 'buildRoute')
                .withArgs('Dashboards').returns('DashboardsRoute');
            var navigateStub = sandbox.stub(app.router, 'navigate');

            alertStub.yieldsToOn('onConfirm', view, []);
            destroyStub.yieldsToOn('success', view, []);

            view.handleDelete();

            expect(navigateStub).toHaveBeenCalledWith('DashboardsRoute', {trigger: true});
        });

        it('should navigate to fallback RHS dashboard layout after deleting current one', function() {
            var dashboardList = [
                view.model,
                app.data.createBean('Dashboards', {name: 'Dashboard B'})
            ];
            var contextBro = new app.Context({
                module: 'Home'
            });
            contextBro.set('collection', app.data.createBeanCollection('Dashboards', dashboardList));
            view.context.parent = {
                getChildContext: function() {
                    return contextBro;
                },
                get: function() {
                    return 'record';
                }
            };
            view.layout = {
                navigateLayout: sandbox.stub()
            };

            alertStub.yieldsToOn('onConfirm', view, []);
            destroyStub.yieldsToOn('success', view, []);

            view.handleDelete();

            expect(_.findWhere(contextBro.get('collection'), view.model)).toBeUndefined();
            expect(view.layout.navigateLayout).toHaveBeenCalledWith('list');
        });

        it('should show an error alert when deletion fails', function() {
            alertStub.withArgs('delete_confirmation').yieldsToOn('onConfirm', view, []);
            destroyStub.yieldsToOn('error', view, []);

            view.handleDelete();

            expect(alertStub.lastCall.args[0]).toEqual('error_while_save');
        });
    });

    describe('_isDashboard', function() {
        var context;

        beforeEach(function() {
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            context = app.context.getContext();
            context.set('tabs', [tab0, tab1]);
            context.prepare();
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane', null, context, true);
        });

        it('should return true for dashboard tab', function() {
            context.set('activeTab', 0);
            expect(view._isDashboard()).toBeTruthy();
        });

        it('should return false for non-dashboard tab', function() {
            context.set('activeTab', 1);
            expect(view._isDashboard()).toBeFalsy();
        });
    });

    describe('_enableEditButton', function() {
        var button;
        var setDisabledStub;

        beforeEach(function() {
            SugarTest.loadComponent('base', 'field', 'button');
            button = SugarTest.createField('base', 'button', 'button', 'edit');
            button.name = 'edit_button';
            setDisabledStub = sandbox.stub(button, 'setDisabled');
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            view.buttons = [{
                type: 'actiondropdown',
                fields: [button],
                _orderButtons: $.noop,
                render: $.noop
            }];
        });

        afterEach(function() {
            button.dispose();
            button = null;
        });

        it('should disable edit button', function() {
            view._enableEditButton(false);
            expect(setDisabledStub).toHaveBeenCalledWith(true);
        });

        it('should enable edit button', function() {
            view._enableEditButton(true);
            expect(setDisabledStub).toHaveBeenCalledWith(false);
        });
    });
});
