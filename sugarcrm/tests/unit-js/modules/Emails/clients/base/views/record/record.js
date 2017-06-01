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
describe('Emails.Views.Record', function() {
    var app;
    var context;
    var view;
    var sandbox;

    beforeEach(function() {
        var viewName = 'record';
        var moduleName = 'Emails';
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();
        app.drawer = {on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop, reset: $.noop};

        context = app.context.getContext({module: moduleName});
        context.prepare(true);

        var meta = {
            panels: [
                {
                    fields: [
                        {
                            name: 'recipients',
                            fields: [
                                {name: 'to'},
                                {name: 'cc'},
                                {name: 'bcc'}
                            ]
                        }
                    ]
                }
            ]
        };

        view = SugarTest.createView('base', moduleName, viewName, meta, context, true);

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Delete Confirmation', function() {
        it('should display (no subject) as the record name on delete confirmation', function() {
            var name = view._getNameForMessage(view.model);
            expect(name).toBe('LBL_NO_SUBJECT');
        });

        it('should display the record name when not empty on delete confirmation', function() {
            var recordName = 'Test Record';

            view.model.set('name', recordName);
            var name = view._getNameForMessage(view.model);
            expect(name).toBe(recordName);
        });
    });

    describe('alert the user that the email is a draft', function() {
        beforeEach(function() {
            sandbox.stub(app.alert, 'show');
        });

        it('should alert the user when state changes to draft on the model', function() {
            view.model.set('state', view.STATE_DRAFT);
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should alert the user when the model is synced and state becomes draft', function() {
            sandbox.stub(view.model, 'sync', function(method, model, options) {
                options.success({state: view.STATE_DRAFT});
            });
            view.model.save();
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should alert the user when the model starts with state equal to draft', function() {
            context.get('model').set('state', view.STATE_DRAFT);
            view = SugarTest.createView('base', 'Emails', 'record', null, context, true);

            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('loading all recipients', function() {
        it('should toggle action buttons while loading all recipients', function() {
            sandbox.spy(view, 'toggleButtons');

            view.trigger('loading_collection_field', 'to');
            view.trigger('loading_collection_field', 'cc');
            view.trigger('loading_collection_field', 'bcc');

            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.alwaysCalledWithExactly(false)).toBe(true);

            view.trigger('loaded_collection_field', 'to');
            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.neverCalledWith(true)).toBe(true);

            view.trigger('loaded_collection_field', 'cc');
            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.neverCalledWith(true)).toBe(true);

            view.trigger('loaded_collection_field', 'bcc');
            expect(view.toggleButtons.callCount).toBe(4);
            expect(view.toggleButtons.lastCall.args[0]).toBe(true);
        });

        describe('Render each recipient field when it has changed', function() {
            using('recipient fields', ['from', 'to', 'cc', 'bcc'], function(data) {
                it('should render the ' + data + ' field', function() {
                    var field = {
                        render: sandbox.spy()
                    };
                    sandbox.stub(view, 'getField').withArgs(data).returns(field);
                    view.model.trigger('change:' + data);
                    expect(field.render).toHaveBeenCalled();
                });
            });
        });
    });

    describe('saving an email', function() {
        it('should set the view parameter to the name of the view', function() {
            var options = view.getCustomSaveOptions({});
            expect(options.params.view).toBe(view.name);
        });
    });
});
