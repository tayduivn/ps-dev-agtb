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

describe('OutboundEmail.BaseEmailProviderField', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.declareData('base', 'OutboundEmail', true, false);
        SugarTest.loadHandlebarsTemplate('email-provider', 'field', 'base', 'detail', 'OutboundEmail');
        SugarTest.loadHandlebarsTemplate('email-provider', 'field', 'base', 'edit', 'OutboundEmail');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'radioenum');
        SugarTest.loadComponent('base', 'field', 'email-provider', 'OutboundEmail');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'OutboundEmail'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.api, 'triggerBulkCall');

        field = SugarTest.createField({
            name: 'mail_smtptype',
            type: 'email-provider',
            viewName: 'edit',
            fieldDef: {
                name: 'mail_smtptype',
                type: 'enum',
                options: 'mail_smtptype_options',
                default: 'other'
            },
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });

        // Mock the connector information already being loaded
        field.connectorsLoaded = true;
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('rendering in disabled mode', function() {
        it('should use the detail template', function() {
            field.render();
            expect(field.tplName).toBe('edit');
            expect(field.template).toBe(Handlebars.templates['f.email-provider.OutboundEmail.edit']);

            sandbox.spy(field, '_getFallbackTemplate');
            field.setDisabled();
            expect(field.tplName).toBe('disabled');
            expect(field.template).toBe(Handlebars.templates['f.email-provider.OutboundEmail.detail']);
            expect(field._getFallbackTemplate.alwaysReturned('detail')).toBe(true);
        });
    });

    describe('handleAuthorizeComplete', function() {
        it('should return false if dataSource is wrong', function() {
            let data = {
                dataSource: 'fake source'
            };
            let e = {data: JSON.stringify(data)};
            field.model.set(field.name, 'google_oauth2');
            expect(field.handleAuthorizeComplete(e, field.value)).toBeFalsy();
        });

        it('should set eapm_id, authorized_account, and mail_smtpuser in model', function() {
            let data = {
                dataSource: 'googleEmailRedirect',
                eapmId: 'fakeId',
                emailAddress: 'fakeEmail',
                userName: 'fakeUserName'
            };
            let e = {data: JSON.stringify(data)};
            field.model.set(field.name, 'google_oauth2');
            field.model.set('eapm_id', '');
            field.handleAuthorizeComplete(e, field.value);
            expect(field.model.get('eapm_id')).toEqual('fakeId');
            expect(field.model.get('authorized_account')).toEqual('fakeEmail');
            expect(field.model.get('mail_smtpuser')).toEqual('fakeUserName');
        });
    });

    describe('_displayAuthorizationElements', function() {
        it('should have no warning or button for non-oauth2 providers', function() {
            field._displayAuthorizationElements('exchange');
            expect(field.authWarning).toEqual('');
            expect(field.authButton).toEqual(false);
        });

        it('should have a warning and disabled button for oauth2 providers that are not configured', function() {
            field.oauth2Types.google_oauth2.auth_url = false;
            field.oauth2Types.google_oauth2.auth_warning = 'This connector is not configured';
            field._displayAuthorizationElements('google_oauth2');
            expect(field.authWarning).toEqual('This connector is not configured');
            expect(field.authButton).toEqual('disabled');
        });

        it('should have no warning and an enabled button for oauth2 providers that are configured', function() {
            field.oauth2Types.google_oauth2.auth_url = 'fakeURL';
            field.oauth2Types.google_oauth2.auth_warning = 'This connector is not configured';
            field._displayAuthorizationElements('google_oauth2');
            expect(field.authWarning).toEqual('');
            expect(field.authButton).toEqual('enabled');
        });
    });
});
