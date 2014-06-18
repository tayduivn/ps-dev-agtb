/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
describe('Base.View.HelpDashlet', function() {
    var app,
        view,
        testObj,
        testModule = 'Accounts',
        testLayout = 'Record',
        initOptions,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        testObj = {
            title: 'testTitle',
            body: 'testBody',
            more_help: 'testMoreHelp'
        };

        sandbox.stub(app.lang, 'get', function(label, module) {
            var obj = {
                LBL_HELP_RECORD_TITLE: testObj.title,
                LBL_HELP_RECORD: testObj.body,
                LBL_HELP_MORE_INFO: testObj.more_help
            };
            return (obj[label]) ? obj[label] : label;
        });

        var context = app.context.getContext();
        context.set({
            module: testModule,
            layout: testLayout
        });

        var meta = {
            config: false,
            label: 'LBL_TEST_LBL'
        };

        initOptions = {
            context: context,
            meta: meta
        };

        view = SugarTest.createView('base', null, 'help-dashlet', meta, context, false, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        view = null;
        testObj = null;
        app = null;
        initOptions = null;
    });

    describe('initialize()', function() {
        describe('with proper help values', function() {
            beforeEach(function() {
                view.initialize(initOptions);
            });

            it('should set the helpObject correctly', function() {
                expect(view.helpObject).not.toBeEmpty();
            });

            it('should set the helpObject.title correctly', function() {
                expect(view.helpObject.title).not.toBeEmpty();
                expect(view.helpObject.title).toEqual(testObj.title);
            });

            it('should set the helpObject.body correctly', function() {
                expect(view.helpObject.body).not.toBeEmpty();
                expect(view.helpObject.body).toEqual(testObj.body);
            });

            it('should set the helpObject.more_help correctly', function() {
                expect(view.helpObject.more_help).not.toBeEmpty();
                expect(view.helpObject.more_help).toEqual(testObj.more_help);
            });
        });

        describe('with missing help title', function() {
            it('should use meta.label for helpObject.title', function() {
                testObj.title = '';
                view.initialize(initOptions);
                expect(view.helpObject.title).toEqual('LBL_TEST_LBL');
            });
        });

        describe('when meta.preview is true', function() {
            beforeEach(function() {
                sinon.collection.spy(app.help, 'get');
            });

            afterEach(function() {
                sinon.collection.restore();
            });

            it('will call app.help.get with preview for layout', function() {
                initOptions.meta.preview = true;
                sinon.collection.stub(view, 'createMoreHelpLink', function() {
                    return '<a>';
                });
                view.initialize(initOptions);
                expect(app.help.get).toHaveBeenCalledWith('Accounts', 'preview', {
                    more_info_url: '<a>',
                    more_info_url_close: '</a>'
                });
            });
        });
    });
});
