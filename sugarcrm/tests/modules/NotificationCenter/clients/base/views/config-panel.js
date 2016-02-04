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
describe('NotificationCenter.View.ConfigPanel', function() {
    var app, view, sandbox, module = 'NotificationCenter';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        view.dispose();
        view = null;
        sandbox.restore();
    });

    describe('initialize()', function() {
        it('should set up title if label is defined in meta', function() {
            var meta = {
                label: 'foo-label'
            };
            sandbox.stub(app.lang, 'get').returns('foo-title');
            view = SugarTest.createView('base', module, 'config-panel', meta, null, true);
            expect(view.title).toEqual('foo-title');
        });

        it('should not set up title if there is no label in meta', function() {
            var meta = {};
            view = SugarTest.createView('base', module, 'config-panel', meta, null, true);
            expect(view.title).toBeUndefined();
        });
    });

    describe('_loadTemplate()', function() {
        it('should set up template based on meta.template value if it exists', function() {
            var meta = {
                template: 'bar-template'
            };
            var getView = sandbox.spy(app.template, 'getView');
            view = SugarTest.createView('base', module, 'config-panel', meta, null, true);
            view._loadTemplate();
            expect(getView).toHaveBeenCalledWith('bar-template');
        });

        it('should set up template based on view type value if meta.template is not defined', function() {
            var meta = {};
            var getView = sandbox.spy(app.template, 'getView');
            view = SugarTest.createView('base', module, 'config-panel', meta, null, true);
            view._loadTemplate();
            expect(getView).toHaveBeenCalledWith(view.type);
        });
    });
});
