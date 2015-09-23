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

describe('modules.NotificationCenter.clients.base.fields.carriers', function() {
    var app, field, sandbox,
        module = 'NotificationCenter',
        fieldName = 'carriers',
        fieldType = 'carriers',
        model;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        model.set(fieldName, {
            carrier1: {
                status: false
            },
            carrier2: {
                status: true
            }
        });
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {
            'name' : 'carriers',
            'type' : 'carriers',
            'default' : false,
            'enabled' : true,
            'view' : 'edit'
        }, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('should be called render when model attribute changed', function() {
        var render = sandbox.spy(field, '_render');
        model.set(fieldName, {
            carrier1: {
                status: true
            }
        });
        expect(render).toHaveBeenCalled();
    });

    it('should be called setItems when render is called', function() {
        var setItems = sandbox.spy(field, '_setItems');
        field.render();
        expect(setItems).toHaveBeenCalled();
    });

    it('items should be populated properly when rendering field', function() {
        field.render();
        var items = [
            {
                name: 'carrier1',
                label: app.lang.get('LBL_CONFIG_LABEL', 'carrier1'),
                enabled: false
            },
            {
                name: 'carrier2',
                label: app.lang.get('LBL_CONFIG_LABEL', 'carrier1'),
                enabled: true
            },
        ]
        expect(field.items).toEqual(items);
    });

    it('should change carrier\'s status when checkbox of the corresponding carrier is clicked', function() {
        field.render();
        field.$('input[name="carrier1"]').attr('checked', true).trigger('click');
        expect(model.get('carriers').carrier1.status).toBeTruthy();
        field.$('input[name="carrier2"]').attr('checked', false).trigger('click');
        expect(model.get('carriers').carrier2.status).toBeFalsy();
    });

});
