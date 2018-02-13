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

describe("Sugar7 utils", function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) { eval(d) });

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('hideForecastCommitStageField()', function() {
        var options;
        beforeEach(function() {
            options = {
                panels: [
                    {
                        fields: [
                            {
                                name: 'commit_stage',
                                label: 'LBL_COMMIT_STAGE'
                            }
                        ]
                    }
                ]
            };
        });

        afterEach(function() {
            options = undefined;
        });
        it('should replace commit_stage with a spacer', function() {
            sinon.stub(app.metadata, 'getModule', function() {
                return {
                    is_setup: false
                };
            });
            app.utils.hideForecastCommitStageField(options.panels);
            expect(options.panels[0].fields[0]).toEqual(
                {name: 'spacer', label: 'LBL_COMMIT_STAGE', span: 6, readonly: true}
            );
            app.metadata.getModule.restore();
        });
    });

    describe("getSubpanelCollection()", function() {
        it("should return the proper subpanel collection", function() {
            var ctx = {};
            ctx.children = [];

            var mdl = new Backbone.Model(),
                targetMdl = new Backbone.Model();

            targetMdl.set({id: 'targetMdl'});
            mdl.set({module: 'Test'});

            var col = new Backbone.Collection();
            col.add(targetMdl);

            mdl.set({collection: col});
            ctx.children.push(mdl);

            var targetCol = app.utils.getSubpanelCollection(ctx, 'Test');

            expect(targetCol.models[0].get('id')).toEqual('targetMdl');
        });
    });
    
    describe('Handling iframe URLs', function() {
		
    	it('Add frame mark to URL', function() {
    		var withMark = app.utils.addIframeMark('/sugar7/index.php?module=Administration&action=Home'); 
    		expect(withMark).toBe('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php?bwcFrame=1'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    	});
    	
    	it('Remove frame mark from URL', function() {
    		var noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&bwcFrame=1&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php');
    		expect(noMark).toBe('/sugar7/index.php'); 
    	});
    });

    describe('getRecordName', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model();
        });
        it('should get document_name for Documents module', function() {
            model.module = 'Documents';
            model.set({
                document_name: 'Awesome Document',
                name: 'document.zip'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Document');
        });
        it('get full_name when available', function() {
            model.module = 'Contacts';
            model.set({
                full_name: 'Awesome Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('build full name based on first name and last name', function() {
            model.module = 'Users';
            model.set({
                first_name: 'Awesome',
                last_name: 'Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('get name otherwise', function() {
            model.module = 'Leads';
            model.set({
                name: 'Simple Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Simple Name');
        });

        it('should return the last name', function() {
            model.module = 'Contacts';
            model.set('last_name', 'Name');
            expect(app.utils.getRecordName(model)).toEqual('Name');
        });
    });

    describe('email addresses', function() {
        var combos,
            model;

        combos = {
            primary_valid: {
                email_address: 'primary@valid.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            },
            primary_invalid: {
                email_address: 'primary@invalid.com',
                primary_address: true,
                invalid_email: true,
                opt_out: false
            },
            primary_opted_out: {
                email_address: 'primary@optout.com',
                primary_address: true,
                invalid_email: false,
                opt_out: true
            },
            primary_bad: {
                email_address: 'primary@bad.com',
                primary_address: true,
                invalid_email: true,
                opt_out: true
            },
            valid: {
                email_address: 'is@valid.com',
                primary_address: false,
                invalid_email: false,
                opt_out: false
            },
            invalid: {
                email_address: 'is@invalid.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            },
            opted_out: {
                email_address: 'is@optout.com',
                primary_address: false,
                invalid_email: false,
                opt_out: true
            },
            bad: {
                email_address: 'is@bad.com',
                primary_address: false,
                invalid_email: true,
                opt_out: true
            }
        };

        beforeEach(function() {
            model = new Backbone.Model();
        });

        using('getEmailAddress', [
            [
                [combos.primary_valid, combos.valid],
                {primary_address: true},
                combos.primary_valid.email_address
            ],
            [
                [combos.valid, combos.primary_valid],
                undefined,
                combos.valid.email_address
            ],
            [
                [combos.primary_invalid, combos.invalid],
                {invalid_email: true},
                combos.primary_invalid.email_address
            ],
            [
                [combos.primary_valid, combos.valid],
                {invalid_email: true},
                ''
            ],
            [
                [combos.primary_valid, combos.valid],
                {opt_out: true},
                ''
            ],
            [
                [combos.valid, combos.invalid],
                {invalid_email: true},
                combos.invalid.email_address
            ],
            [
                [combos.valid, combos.opted_out],
                {opt_out: true},
                combos.opted_out.email_address
            ],
            [
                [combos.valid, combos.invalid, combos.opted_out, combos.bad],
                {invalid_email: true, opt_out: true},
                combos.bad.email_address
            ],
            [
                [combos.bad, combos.valid, combos.invalid, combos.primary_bad, combos.opted_out],
                {primary_address: true, invalid_email: true, opt_out: true},
                combos.primary_bad.email_address
            ]
        ], function(emails, options, expected) {
            it('should return ' + expected, function() {
                model.set('email', emails);
                expect(app.utils.getEmailAddress(model, options)).toEqual(expected);
            });
        });

        using('getPrimaryEmailAddress', [
            [[combos.primary_valid, combos.valid], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.invalid], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.opted_out], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.bad], combos.primary_valid.email_address],
            [[combos.primary_invalid, combos.valid], combos.valid.email_address],
            [[combos.primary_invalid, combos.invalid], ''],
            [[combos.primary_invalid, combos.opted_out], combos.opted_out.email_address],
            [[combos.primary_invalid, combos.bad], ''],
            [[combos.primary_opted_out, combos.valid], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.invalid], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.opted_out], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.bad], combos.primary_opted_out.email_address],
            [[combos.primary_bad, combos.valid], combos.valid.email_address],
            [[combos.primary_bad, combos.invalid], ''],
            [[combos.primary_bad, combos.opted_out], combos.opted_out.email_address],
            [[combos.primary_bad, combos.bad], ''],
            [[combos.valid, combos.invalid], combos.valid.email_address],
            [[combos.valid, combos.opted_out], combos.valid.email_address],
            [[combos.valid, combos.bad], combos.valid.email_address],
            [[combos.invalid, combos.opted_out], combos.opted_out.email_address],
            [[combos.invalid, combos.bad], ''],
            [[combos.opted_out, combos.bad], combos.opted_out.email_address]
        ], function(emails, expected) {
            it('should return ' + expected, function() {
                model.set('email', emails);
                expect(app.utils.getPrimaryEmailAddress(model)).toEqual(expected);
            });
        });
    });

    var name = 'module';
    using('query strings',
        [
            ['?module=asdf', 'asdf'],
            ['?asdf=asdf&module=asdf&module=zxcv', 'zxcv'],
            ['?asdf=asdf&module=zxcv&modtrwer=zxcv', 'zxcv'],
            ['?xcvb=asdf&asdf=asdf&ryuit=zxcv', '']
        ],
        function (value, result) {
            it('should be able to get parameters', function () {
                var testResult = app.utils.getWindowLocationParameterByName(name, value);
                expect(result).toEqual(testResult);
            });
        });

    describe('getSelectedUsersReportees', function() {
        describe('as manager', function() {
            var user;
            beforeEach(function() {
                user = {
                    is_manager: true,
                    id: 'test_id'
                };
            });

            afterEach(function() {
                sinon.collection.restore();
                delete user;
            });

            it('will make an xhr call with status equal to active', function() {
                var post_args = undefined;
                sinon.collection.stub(app.api, 'call', function(type, url, args) {
                    post_args = args;
                });
                app.utils.getSelectedUsersReportees(user, {});
                expect(app.api.call).toHaveBeenCalled();
                expect(post_args).not.toBeUndefined();
                expect(post_args.filter[0].status).toEqual('Active');
            });
        });
    });

    describe('getArrowDirectionSpan', function() {
        it('should return a properly styled i tag', function() {
            var expectedHtml = '&nbsp;<i class="fa fa-arrow-up font-green"></i>';
            expect(app.utils.getArrowDirectionSpan('LBL_UP')).toEqual(expectedHtml);
            expectedHtml = '&nbsp;<i class="fa fa-arrow-down font-red"></i>';
            expect(app.utils.getArrowDirectionSpan('LBL_DOWN')).toEqual(expectedHtml);
            expect(app.utils.getArrowDirectionSpan('anything else')).toEqual('');
        });
    });

    describe('getDifference', function() {
        beforeEach(function() {
            this.newModel = app.data.createBean('MyModule');
            this.oldModel = app.data.createBean('MyModule');
            this.isDifferentWithPrecisionStub = sinon.stub(app.math, 'isDifferentWithPrecision');
            this.getDifferenceStub = sinon.stub(app.math, 'getDifference');
        });

        afterEach(function() {
            this.isDifferentWithPrecisionStub.restore();
            this.getDifferenceStub.restore();
        });

        it('should return the difference in the attributes on the models', function() {
            this.isDifferentWithPrecisionStub.returns(true);
            this.getDifferenceStub.returns('2');
            expect(app.utils.getDifference(this.oldModel, this.newModel, 'myAttr')).toEqual('2');
        });

        it('should return 0 if there is no difference', function() {
            this.isDifferentWithPrecisionStub.returns(false);
            expect(app.utils.getDifference(this.oldModel, this.newModel, 'sameAttr')).toEqual(0);
        });
    });

    describe('getDirection', function() {
        it('should return the proper direction label', function() {
            expect(app.utils.getDirection(5)).toEqual('LBL_UP');
            expect(app.utils.getDirection(-2)).toEqual('LBL_DOWN');
            expect(app.utils.getDirection(0)).toEqual('');
        });
    });

    describe('isTruthy', function() {
        it('should determine if a value is truthy in the SugarCRM sense', function() {
            expect(app.utils.isTruthy(true)).toBeTruthy();
            expect(app.utils.isTruthy('true')).toBeTruthy();
            expect(app.utils.isTruthy(1)).toBeTruthy();
            expect(app.utils.isTruthy('1')).toBeTruthy();
            expect(app.utils.isTruthy('on')).toBeTruthy();
            expect(app.utils.isTruthy('yes')).toBeTruthy();
            expect(app.utils.isTruthy('no')).not.toBeTruthy();
        });

        it('should accept uppercase truthy strings', function() {
            expect(app.utils.isTruthy('YES')).toBeTruthy();
        });
    });

    describe('getReadableFileSize', function() {
        using('file sizes',
            [
                [undefined, '0K'],
                [null, '0K'],
                ['', '0K'],
                [0, '0K'],
                [1, '1K'],
                [999, '1K'],
                [2000, '2K'],
                [999999, '1M'],
                [1000000, '1M'],
                [1500000, '2M'],
                [1073741824, '1G'],
                [1099511627776, '1T'],
                [10000000000000000, '10000T']
            ],
            function(rawSize, readableSize) {
                it('should convert the file size to a readable format', function() {
                    var actual = app.utils.getReadableFileSize(rawSize);
                    expect(actual).toEqual(readableSize);
                });
            });
    });

    describe('creating an email', function() {
        beforeEach(function() {
            var metadata = SugarTest.loadFixture('emails-metadata');

            SugarTest.testMetadata.init();

            _.each(metadata.modules, function(def, module) {
                SugarTest.testMetadata.updateModuleMetadata(module, def);
            });

            SugarTest.testMetadata.set();

            app.data.declareModels();
            app.routing.start();

            app.drawer = {
                open: sandbox.stub()
            };
        });

        afterEach(function() {
            delete app.drawer;
        });

        using('layouts', ['create', 'compose-email'], function(layout) {
            it('should load the specified layout when opening the drawer', function() {
                app.utils.openEmailCreateDrawer(layout);

                expect(app.drawer.open).toHaveBeenCalledOnce();
                expect(app.drawer.open.firstCall.args[0].layout).toBe(layout);
            });
        });

        it('should open the drawer with an Emails create context', function() {
            app.utils.openEmailCreateDrawer('compose-email');

            expect(app.drawer.open).toHaveBeenCalledOnce();
            expect(app.drawer.open.firstCall.args[0].context.create).toBe(true);
            expect(app.drawer.open.firstCall.args[0].context.module).toBe('Emails');
            expect(app.drawer.open.firstCall.args[0].context.model.module).toBe('Emails');
        });

        describe('populating the model', function() {
            it('should use the model if one is provided', function() {
                var model;
                var email = app.data.createBean('Emails');

                app.utils.openEmailCreateDrawer('compose-email', {model: email});
                model = app.drawer.open.firstCall.args[0].context.model;

                expect(model).toBe(email);
            });

            using('recipients fields', ['', '_collection'], function(suffix) {
                it('should add recipients if to, cc, or bcc value is passed in', function() {
                    var model;
                    var data = {};

                    data['to' + suffix] = [
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'to@foo.com'
                        }),
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'too@foo.com'
                        })
                    ];
                    data['cc' + suffix] = [
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'cc@foo.com'
                        })
                    ];
                    data['bcc' + suffix] = [
                        app.data.createBean('Contacts', {
                            id: _.uniqueId(),
                            email: 'bcc@foo.com'
                        })
                    ];

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('to_collection').length).toBe(2);
                    expect(model.get('cc_collection').length).toBe(1);
                    expect(model.get('bcc_collection').length).toBe(1);
                });
            });

            using('attachments fields', ['attachments', 'attachments_collection'], function(fieldName) {
                it('should add attachments from ' + fieldName, function() {
                    var model;
                    var data = {};

                    data[fieldName] = [
                        app.data.createBean('Notes', {
                            id: _.uniqueId(),
                            name: 'attachment 1'
                        }),
                        app.data.createBean('Notes', {
                            id: _.uniqueId(),
                            name: 'attachment 2'
                        })
                    ];

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('attachments_collection').length).toBe(2);
                });
            });

            using('attributes', ['name', 'description_html', 'reply_to_id'], function(fieldName) {
                it('should set standard attributes', function() {
                    var model;
                    var data = {};

                    data[fieldName] = 'foo';

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get(fieldName)).toBe('foo');
                });
            });

            using('non-fields', [
                [
                    'signature_location',
                    'above'
                ],
                [
                    'foo',
                    'bar'
                ]
            ], function(fieldName, value) {
                it('should pass non-fields to be set as attributes on the context', function() {
                    var context;
                    var data = {};

                    data[fieldName] = value;

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    context = app.drawer.open.firstCall.args[0].context;

                    expect(context.model.get(fieldName)).toBeUndefined();
                    expect(context[fieldName]).toBe(value);
                });
            });

            using('static options', [
                [
                    'create',
                    false,
                    true
                ],
                [
                    'module',
                    'Notes',
                    'Emails'
                ]
            ], function(option, value, expected) {
                it('should not allow some options to be overridden by the caller', function() {
                    var context;
                    var data = {};

                    data[option] = value;

                    app.utils.openEmailCreateDrawer('compose-email', data);
                    context = app.drawer.open.firstCall.args[0].context;

                    expect(context.model.get(option)).toBeUndefined();
                    expect(context[option]).toBe(expected);
                });
            });

            describe('populating the related fields', function() {
                beforeEach(function() {
                    sandbox.stub(app.lang, 'getAppListStrings')
                        .withArgs('record_type_display_emails')
                        .returns({
                            Accounts: 'Account',
                            Contacts: 'Contact',
                            Tasks: 'Task',
                            Opportunities: 'Opportunity',
                            Products: 'Quoted Line Item',
                            Quotes: 'Quote',
                            Bugs: 'Bug',
                            Cases: 'Case',
                            Leads: 'Lead',
                            Project: 'Project',
                            ProjectTask: 'Project Task',
                            Prospects: 'Target',
                            Notes: 'Note',
                            Meetings: 'Meeting',
                            RevenueLineItems: 'Revenue Line Item'
                        });
                    sandbox.stub(app.acl, 'hasAccess').withArgs('list').returns(true);
                });

                it('should set the parent attributes without fetching the name of the related record', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {
                        id: _.uniqueId(),
                        name: 'Bob Tillman'
                    });

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('parent_type')).toBe('Contacts');
                    expect(model.get('parent_id')).toBe(contact.get('id'));
                    expect(model.get('parent_name')).toBe('Bob Tillman');
                });

                it('should set the parent attributes after fetching the name of the related record', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {id: _.uniqueId()});

                    sandbox.stub(contact, 'fetch', function(params) {
                        contact.set('name', 'Torry Young');
                        params.success(contact);
                    });

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(model.get('parent_type')).toBe('Contacts');
                    expect(model.get('parent_id')).toBe(contact.get('id'));
                    expect(model.get('parent_name')).toBe('Torry Young');
                });

                it('should not set the parent attributes when there is no ID for the related record', function() {
                    var model;
                    var contact = app.data.createBean('Contacts', {
                        name: 'Andy Hopkins'
                    });
                    sandbox.spy(contact, 'fetch');

                    app.utils.openEmailCreateDrawer('compose-email', {related: contact});
                    model = app.drawer.open.firstCall.args[0].context.model;

                    expect(contact.fetch).not.toHaveBeenCalled();
                    expect(model.get('parent_type')).toBeUndefined();
                    expect(model.get('parent_id')).toBeUndefined();
                    expect(model.get('parent_name')).toBeUndefined();
                });

                describe('populating for a related case', function() {
                    var aCase;
                    var relatedCollection;

                    beforeEach(function() {
                        sandbox.stub(app.metadata, 'getConfig').returns({'inboundEmailCaseSubjectMacro': '[CASE:%1]'});

                        aCase = app.data.createBean('Cases', {
                            id: _.uniqueId(),
                            case_number: '100',
                            name: 'My Case'
                        });

                        relatedCollection = app.data.createBeanCollection('Contacts');
                        sandbox.stub(relatedCollection, 'fetch', function(params) {
                            params.success(relatedCollection);
                        });

                        sandbox.stub(aCase, 'getRelatedCollection').returns(relatedCollection);
                    });

                    it('should set only the subject and when the case does not have any related contacts', function() {
                        var model;

                        app.utils.openEmailCreateDrawer('compose-email', {related: aCase});
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBe('[CASE:100] My Case');
                        expect(model.get('to_collection').length).toBe(0);
                    });

                    it('should populate the subject and "to" field when the case has related contacts', function() {
                        var model;

                        relatedCollection.add([
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Jaime Hammonds'
                            }),
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Frank Upton'
                            })
                        ]);

                        app.utils.openEmailCreateDrawer('compose-email', {related: aCase});
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBe('[CASE:100] My Case');
                        expect(model.get('to_collection').length).toBe(2);
                    });

                    it('should not add to the "to" field when the field already has recipients', function() {
                        var model;
                        var email = app.data.createBean('Emails');

                        relatedCollection.add([
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Jaime Hammonds'
                            }),
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Frank Upton'
                            })
                        ]);
                        email.get('to_collection').add([
                            app.data.createBean('Leads', {
                                id: _.uniqueId(),
                                name: 'Nancy Rollins'
                            })
                        ]);

                        app.utils.openEmailCreateDrawer('compose-email', {
                            model: email,
                            related: aCase
                        });
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBe('[CASE:100] My Case');
                        expect(model.get('to_collection').length).toBe(1);
                    });

                    it('should not prepopulate the email with case data', function() {
                        var model;
                        var email = app.data.createBean('Emails');

                        relatedCollection.add([
                            app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                name: 'Jaime Hammonds'
                            })
                        ]);

                        app.utils.openEmailCreateDrawer('compose-email', {
                            related: aCase,
                            skip_prepopulate_with_case: true
                        });
                        model = app.drawer.open.firstCall.args[0].context.model;

                        expect(model.get('parent_type')).toBe('Cases');
                        expect(model.get('parent_id')).toBe(aCase.get('id'));
                        expect(model.get('parent_name')).toBe('My Case');
                        expect(model.get('name')).toBeUndefined();
                        expect(email.get('to_collection').add).not.toHaveBeenCalled();
                    });
                });
            });
        });
    });
});
