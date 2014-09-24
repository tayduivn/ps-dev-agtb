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
describe('Plugins.SearchForMore', function() {
    var $el, app, appDrawer, appDrawerOpen, context, field, fieldDef, model, module, participants, sandbox;

    module = 'Meetings';

    participants = [
        {_module: 'Contacts', id: '1', name: 'Jim Brennan', accept_status_meetings: 'accept', delta: 0},
        {_module: 'Contacts', id: '2', name: 'Will Weston', accept_status_meetings: 'decline', delta: 0},
        {_module: 'Contacts', id: '3', name: 'Jim Gallardo', accept_status_meetings: 'tentative', delta: 0},
        {_module: 'Contacts', id: '4', name: 'Sallie Talmadge', accept_status_meetings: 'none', delta: 0}
    ];

    fieldDef = {links: ['users', 'contacts', 'leads']};

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'participants');
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('CollectionAttribute');
        SugarTest.loadPlugin('SearchForMore');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.api, 'call', function(method, url, data, callbacks, options) {
            if (callbacks.success) {
                callbacks.success({});
            }
        });
        sandbox.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Meetings', 'users').returns('Users');
        app.data.getRelatedModule.withArgs('Meetings', 'contacts').returns('Contacts');
        app.data.getRelatedModule.withArgs('Meetings', 'leads').returns('Leads');

        appDrawer = app.drawer;
        app.drawer || (app.drawer = {});
        appDrawerOpen = app.drawer.open;
        app.drawer.open || (app.drawer.open = $.noop);

        field = SugarTest.createField(
            'base',
            'invitees',
            'participants',
            'edit',
            fieldDef,
            module,
            model,
            context
        );
        field.action = 'edit';
        field.getFieldValue().reset(participants);
        field.render();
        field.$('button[data-action=addRow]').click();
        $el = field.getFieldElement();
        $(document.body).append($el);
    });

    afterEach(function() {
        sandbox.restore();
        if (_.isUndefined(appDrawerOpen)) {
            delete app.drawer.open;
        } else {
            app.drawer.open = appDrawerOpen;
        }
        if (_.isUndefined(appDrawer)) {
            delete app.drawer;
        } else {
            app.drawer = appDrawer;
        }

        $el.select2('destroy');
        $el.remove();
        $('#select2-drop-mask').remove();
        if (field) {
            field.dispose();
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should add the search for more button to the select widget when the widget is opened', function() {
        $el.select2('open');
        expect($el.select2('dropdown').find('[name=search_for_more]').length).not.toBe(0);
    });

    it('should open the selection-list when the button is clicked', function() {
        var stub = sandbox.stub(field, 'searchForMore');
        $el.select2('open');
        $el.select2('dropdown').find('[name=search_for_more]').mousedown();
        expect(stub).toHaveBeenCalled();
    });

    it('should broadcast the selected model when a model is chosen', function() {
        var spy = sandbox.spy();
        $el.on('change', function(model) {
            spy(model);
        });
        sandbox.stub(app.drawer, 'open', function(def, callback) {
            callback(participants[0]);
        });
        field.searchForMore($el);
        expect(spy).toHaveBeenCalled();
        expect(spy.args[0]).not.toBeEmpty();
    });

    it('should do nothing when the selection-list is closed without selecting a model', function() {
        var spy = sandbox.spy();
        $el.on('change', function(model) {
            spy(model);
        });
        sandbox.stub(app.drawer, 'open', function(def, callback) {
            callback();
        });
        field.searchForMore($el);
        expect(spy).not.toHaveBeenCalled();
    });

    it('should open the selection-list layout in a drawer when there are no links defined', function() {
        var stub = sandbox.stub(app.drawer, 'open');
        delete field.def.links;
        field.searchForMore($el);
        expect(stub.args[0][0].layout).toEqual('selection-list');
        expect(stub.args[0][0].context.module).toEqual(module);
        expect(stub.args[0][0].context.filterList.length).toBe(1);
    });

    it('should open the selection-list-module-switch layout in a drawer when there are links defined', function() {
        var stub = sandbox.stub(app.drawer, 'open');
        field.searchForMore($el);
        expect(stub.args[0][0].layout).toEqual('selection-list-module-switch');
        expect(stub.args[0][0].context.module).toEqual('Users');
        expect(stub.args[0][0].context.filterList.length).toBe(field.def.links.length);
    });
});
