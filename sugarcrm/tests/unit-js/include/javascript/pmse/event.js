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
describe('includes.javascript.pmse.event', function() {
    var app;
    var event;
    var initObjectStub;

    var oldProject;
    var oldApp;

    beforeEach(function() {
        app = SugarTest.app;

        // AdamEvent requires some global variables to be set to function without
        // shitting itself. I don't think this one is used outside of that, but
        // to make 100% sure I don't ruin other application stuff or tests I'll
        // keep a copy.
        oldProject = project;
        project = {};

        //Seriously this Adam stuff sucks.
        oldApp = App;
        App = app;

        // Stub out other objects used by the AdamEvent.
        // Since AdamShape is defined in the global scope, we can stub it like this.
        sinon.collection.stub(window, 'AdamShape');

        /*
        createStubInstance is supposed to be used to make a stubbed object,
        but I don't think it works correctly in the version of sinon we use,
        so I'll avoid it. See https://github.com/sinonjs/sinon/issues/860
        */
        /*
        Instead because the constructor doesn't do anything but call AdamShape
        and AdamEvent.initObject, I'll just stub those two functions. It's not
        ideal but it'll have to do.
        When initObject needs to actually be tested, you can do that by calling
        initObjectStub.restore().
        */
        initObjectStub = sinon.collection.stub(AdamEvent.prototype, 'initObject');
        event = new AdamEvent();
    });

    afterEach(function() {
        sinon.collection.restore();
        app = null;
        event = null;
        initObjectStub = null;

        // Restore the globals
        project = oldProject;
        App = oldApp;
    });

    describe('_makeCriteriaField', function() {
        // To mock out the constructor for CriteriaFields.
        // Defined here because not every function uses it.
        // Defined in a var because we need to spy on it.
        var fakeCriteriaField;
        // Data common to all event types can be kept in the before section;
        var commonData;

        beforeEach(function() {
            // Since CriteriaField is defined in the global scope, we can mock out
            // its constructor as a function in window;
            fakeCriteriaField = sinon.collection.stub(window, 'CriteriaField');

            // _makeCriteriaField uses the "project" global for this function,
            // so it needs a mock.
            var replaceMock = sinon.collection.stub().returns('b');
            project.getMetadata = sinon.collection.stub().returns({url: {replace: replaceMock}});
            project.process_definition = {pro_module: 'goat'};

            // Mock out app functions called below and in makeCriteriaField
            sinon.collection.stub(window, 'translate').returns('a');
            sinon.collection.stub(App.date, 'getUserDateFormat').returns('c');
            sinon.collection.stub(App.user, 'getPreference').returns('d');
            commonData = {
                name: 'evn_criteria',
                label: translate('LBL_PMSE_FORM_LABEL_CRITERIA'),
                required: false,
                fieldWidth: 414,
                decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
                currencies: project.getMetadata('currencies'),
                dateFormat: App.date.getUserDateFormat(),
                timeFormat: App.user.getPreference('timepref'),
            };
        });

        afterEach(function() {
            //Reset data since it is mutated by the call
            commonData = null;
        });

        it('should send the right info for start events', function() {
            var startData = {
                fieldHeight: 80,
                panelContext: '#container',
                operators: {
                    logic: true,
                    group: true
                },
                constant: false
            };

            event.evn_type = 'START';
            event._makeCriteriaField();

            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(commonData));
            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(startData));
        });

        it('should send the right info for receive message events', function() {
            var receiveData = {
                operators: {
                    logic: true,
                    group: true
                },
                constant: false,
                evaluation: {
                    module: {
                        dataURL: 'pmse_Project/CrmData/related/' + PROJECT_MODULE,
                        dataRoot: 'result',
                        fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                        fieldDataRoot: 'result'
                    },
                    user: {
                        defaultUsersDataURL: 'pmse_Project/CrmData/defaultUsersList',
                        defaultUsersDataRoot: 'result',
                        userRolesDataURL: 'pmse_Project/CrmData/rolesList',
                        userRolesDataRoot: 'result',
                        usersDataURL: 'pmse_Project/CrmData/users',
                        usersDataRoot: 'result'
                    }
                }
            };

            event.evn_type = 'INTERMEDIATE';
            event.evn_marker = 'MESSAGE';
            event.evn_behavior = 'CATCH';
            event._makeCriteriaField();

            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(commonData));
            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(receiveData));
        });

        it('should send the right info for timer events', function() {
            var timerData = {
                fieldHeight: 80,
                operators:
                    {
                        arithmetic: ['+', '-']
                    },
                constant:
                    {
                        datetime: true,
                        timespan: true
                    },
                variable:
                    {
                        dataURL: project.getMetadata('fieldsDataSource')
                            .url.replace('{MODULE}', project.process_definition.pro_module),
                        dataRoot: project.getMetadata('fieldsDataSource').root,
                        dataFormat: 'hierarchical',
                        dataChildRoot: 'fields',
                        textField: 'text',
                        valueField: 'value',
                        typeField: 'type',
                        typeFilter: ['Date', 'Datetime'],
                        moduleTextField: 'text',
                        moduleValueField: 'value'
                    }
            };

            event.evn_type = 'INTERMEDIATE';
            event.evn_marker = 'TIMER';
            event.evn_behavior = 'CATCH';
            event._makeCriteriaField();

            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(commonData));
            expect(fakeCriteriaField).toHaveBeenCalledWith(jasmine.objectContaining(timerData));
        });
    });
});
