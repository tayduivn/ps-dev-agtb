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
describe('modules.kbcontents.clients.base.fields.htmleditable_tinymce', function() {

    var app, field,
        module = 'KBContents',
        fieldName = 'htmleditable',
        fieldType = 'htmleditable_tinymce',
        model;

    beforeEach(function() {
        Handlebars.templates = {};
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module, model, null, true);
        field.tinyMCEFileBrowseCallback = sinon.stub();
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('apply document css style to editor', function() {
        var config = field.getTinyMCEConfig();
        expect(config.content_css).toEqual(jasmine.any(Object));
        expect(config.body_class).toEqual('kbdocument-body');
    });

    describe('setViewContent', function() {
        var value;
        beforeEach(function() {
            sinon.collection.stub(field, '_super');
        });

        it('should not add css when value is empty', function() {
            value = '';
            field.setViewContent(value);
            expect(field._super).toHaveBeenCalledWith('setViewContent', ['']);
        });

        it('should add css when value is not empty', function() {
            value = '<p>test</p><ul><li>test</li></ul><p>test</p>';
            field.setViewContent(value);
            expect(field._super).toHaveBeenCalledWith('setViewContent',
                ['<p style="margin: auto;">test</p><ul><li>test</li></ul><p>test</p>']);
        });
    });
});
