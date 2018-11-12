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

/**
 * Tests the basic behavior for commentlog field
 * */
describe('commentlog field', function() {
    var app;
    var field;
    var template;
    var editTemplate;
    var module = 'Bugs';
    var fieldName = 'commentlog';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate(fieldName, 'field', 'base', 'detail');
        editTemplate = SugarTest.loadHandlebarsTemplate(fieldName, 'field', 'base', 'edit');
        SugarTest.seedMetadata(false, 'fixtures');
        SugarTest.testMetadata.set();
        var fieldDef = {
            name: 'commentlog',
            type: 'collection'
        };
        field = SugarTest.createField('base', fieldName, 'commentlog', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('Detailed View Behavior', function() {
        beforeEach(function() {
            field.tplName = 'detail';
        });

        using('Test if formating function would parse passed in json data correctly',
            [
                {
                    'msgs': [] // when no message has been recorded in the past
                },
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when ' +
                            'an unknown printer took a galley of type and scrambled it to make a type specimen bo' +
                            'ok. It has survived not only five centuries, but also the leap into electronic types' +
                            'etting, remaining essentially unchanged. It was popularised in the 1960s with the re' +
                            'lease of Letraset sheets containing Lorem Ipsum passages, and more recently with des' +
                            'ktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
                        }
                    ]
                },
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when' +
                            '\' an unknown printer took a galley of type and scrambled it to make a type specimen ' +
                            'book. It has survived not only five centuries, but also the leap into electronic ty' +
                            'pesetting, remaining essentially unchanged. It was popularised in the 1960s with th' +
                            'e release of Letraset sheets containing Lorem Ipsum passages, and more recently wit' +
                            'h desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
                        },
                        {
                            'created_by_name': 'I am another author, and a wizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Ur a wizard Harry.'
                        }
                    ]
                },
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when' +
                            '\' an unknown printer took a galley of type and scrambled it to make a type specimen \'' +
                            'book. It has survived not only five centuries, but also the leap into electronic ty' +
                            'pesetting, remaining essentially unchanged. It was popularised in the 1960s with th' +
                            'e release of Letraset sheets containing Lorem Ipsum passages, and more recently wit' +
                            'h desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
                        },
                        {
                            'created_by_name': 'I am another author, and a wizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Ur a wizard Harry.'
                        },
                        {
                            'created_by_name': 'U are a wizard, but I am a lizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Lizards are better'
                        }
                    ]
                }
            ], function(value) {
                it('format() should correctly parse passed in function', function() {
                    var resultValue = field.format(value.msgs);

                    // the amount of the messages should remain the same
                    expect(resultValue.length).toEqual(value.msgs.length);

                    // the order of each message should remain the same
                    for (var i = 0; i < value.msgs.length; i++) {
                        // the entry should be exactly the same, since commentlog.js is not responsible
                        // for formatting
                        expect(resultValue[i].created_by_name).toEqual(value.msgs[i].created_by_name);
                        expect(resultValue[i].date_entered).toEqual(value.msgs[i].date_entered);
                        expect(resultValue[i].entry).toEqual(value.msgs[i].entry);
                    }
                });
            }, function(value) {
                it('showCommentLog() should handle data from showCommentLog correctly', function() {
                    field.model.set(fieldName, value.msgs);
                    field.showCommentLog();

                    // the amount of the messages should remain the same
                    expect(field.msgs.length).toEqual(value.msgs.length);

                    // the order of each message should remain the same
                    for (var i = 0; i < value.msgs.length; i++) {
                        // the entry should be exactly the same, since commentlog.js is not responsible
                        // for formatting
                        expect(field.msgs[i].created_by_name).toEqual(value.msgs[i].created_by_name);
                        expect(field.msgs[i].date_entered).toEqual(value.msgs[i].date_entered);
                        expect(field.msgs[i].entry).toEqual(value.msgs[i].entry);
                    }
                });
            });
    });

    describe('Edit View Behavior', function() {
        beforeEach(function() {
            field.tplName = 'edit';
        });

        using('unformat()', [
            '\'\'', // nothing
            '\'     \'', // nothing but spaces
            '<p>I\'m a paragraph, with terms of html</p>',
            '<scr' + 'ipt>console.log(\'Im vicious\')</scr' + 'ipt>',
            'I\'m a muggle, i don\'t magic',
        ], function(value) {
            it('should return whatever was entered in the textarea', function() {
                expect(field.unformat(value)).toEqual(value);
            });
        });
    });

    describe('Bugs from past', function() {
        var userDateFormatStub;
        var userTimeFormatStub;
        var aclStub;

        beforeEach(function() {
            field.tplName = 'detail';
            userDateFormatStub = sinon.stub(app.date, 'getUserDateFormat', function() { return 'YYMMDD'; });
            userTimeFormatStub = sinon.stub(app.date, 'getUserTimeFormat', function() { return 'H:m'; });
            aclStub = sinon.stub(app.acl, 'hasAccess', function() { return true; });
        });

        afterEach(function() {
            userDateFormatStub.restore();
            userTimeFormatStub.restore();
            aclStub.restore();
        });

        using('Going into edit mode and then going back to detail mode will not display all past messages',
            [
                {
                    'msgs': [
                        {
                            'created_by_name': 'I\' the author, I authored',
                            'date_entered': '2018-08-29T22:50:17+00:00',
                            'entry': 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' +
                            '\' Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when ' +
                            'an unknown printer took a galley of type and scrambled it to make a type specimen bo' +
                            'ok. It has survived not only five centuries, but also the leap into electronic types' +
                            'etting, remaining essentially unchanged. It was popularised in the 1960s with the re' +
                            'lease of Letraset sheets containing Lorem Ipsum passages, and more recently with des' +
                            'ktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                            'created_by_link': {
                                '_acl': 'stub'
                            }
                        },
                        {
                            'created_by_name': 'I am another author, and a wizard',
                            'date_entered': '2018-08-29T22:51:17+00:00',
                            'entry': 'Ur a wizard Harry.',
                            'created_by_link': {
                                '_acl': 'stub'
                            }
                        },
                        {
                            'created_by_name': 'U are a wizard, but I am a lizard',
                            'date_entered': '2018-08-29T22:52:17+00:00',
                            'entry': 'Lizards are better',
                            'created_by_link': {
                                '_acl': 'stub'
                            }
                        }
                    ],
                    'entered': 'Please don\'t kill me, I have a family\''
                }
            ],
            function(value) {
                it('should still show the same past messages after coming back from edit mode without saving anything',
                    function() {
                        // first render the details mode
                        var collOptions = {
                            parentBean: field.model,
                            collectionField: fieldName,
                            links: ['commentlog_link']
                        };
                        var coll = app.data.createMixedBeanCollection(
                            [],
                            collOptions
                        );
                        var oldCollection = app.data.createMixedBeanCollection(value.msgs, collOptions);
                        field.model.set(fieldName, coll);
                        field.model.get(fieldName).add(value.msgs);
                        field.model.setSyncedAttributes({commentlog: coll});
                        field.model.save();
                        field.render();

                        // the amount of the messages should remain the same
                        expect(field.msgs.length).toEqual(value.msgs.length);

                        // go into edit mode
                        field.setMode('edit');
                        field.$el.find(field.fieldTag).val(value.entered).change();

                        // return to detail view without saving
                        sinon.collection.stub(field.model, 'changedAttributes').returns({commentlog: oldCollection});
                        sinon.collection.stub(coll, 'hasDelta').returns(true);
                        sinon.collection.stub(field.model, 'getCollectionFields').returns({commentlog: oldCollection});
                        field.model.revertAttributes();
                        field.setMode('detail');

                        // the amount of the messages should be the same as before
                        expect(field.msgs.length).toEqual(value.msgs.length);
                    }
                );
            },
            function(value) {
                it('should still keep entered message after entering, focus on something outside of testarea, then ' +
                   'come back',
                    function() {
                        // first render the record mode
                        field.model.set(fieldName, value.msgs);
                        field.render();

                        // the amount of the messages should remain the same
                        expect(field.msgs.length).toEqual(value.msgs.length);

                        // get into edit mode
                        field.tplName = 'edit';
                        field.model.set(fieldName, value.entered);
                        field.render();

                        // should read in the value as entered
                        expect(field.value).toEqual(value.entered);

                        // render edit mode
                        field.render();

                        // previously entered value should be still there even came back from somewhere else
                        expect(field.value).toEqual(value.entered);
                    });
            });
    });
});