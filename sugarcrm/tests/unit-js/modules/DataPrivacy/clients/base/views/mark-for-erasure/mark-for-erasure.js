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
describe('View.Views.Base.DataPrivacy.MarkForErasureView', function() {
    var view;
    var app;
    var model;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'filtered-list');
        SugarTest.loadComponent('base', 'view', 'pii');
        SugarTest.loadComponent('base', 'view', 'mark-for-erasure', 'DataPrivacy');

        SugarTest.seedMetadata();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('mark-for-erasure', {
            'panels': [
                {
                    name: 'primary',
                    fields: [
                        {
                            type: 'piiname',
                            name: 'field_name',
                            label: 'LBL_DATAPRIVACY_FIELDNAME',
                            sortable: true,
                            filter: 'contains',
                        },
                        {
                            type: 'base',
                            name: 'value',
                            label: 'LBL_DATAPRIVACY_VALUE',
                            sortable: true,
                            filter: 'contains',
                        },
                        {
                            type: 'base',
                            name: 'created_by_username',
                            label: 'LBL_DATAPRIVACY_CHANGED_BY',
                            sortable: false,
                        },
                        {
                            type: 'datetimecombo',
                            name: 'date_modified',
                            label: 'LBL_DATAPRIVACY_CHANGE_DATE',
                            sortable: false,
                        }
                    ],
                }
            ],
        }, 'DataPrivacy');
        SugarTest.testMetadata.set();
        app = SUGAR.App;
        var modelForErase = app.data.createBean('Contacts');

        modelForErase.set('id', 5);
        var context = new app.Context({
            modelForErase: modelForErase
        });
        view = SugarTest.createView('base', 'DataPrivacy', 'mark-for-erasure', null, context, 'DataPrivacy');
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.collection.restore();
    });

    describe('rendering the collection', function() {
        it('should render on collection reset', function() {
            view.collection = app.data.createBeanCollection('MarkForErasure', [
                {
                    field_name: 'first_name',
                    value: 'Bob',
                    date_modified: '2018-01-24T12:44:58-08:00',
                    source: {
                        type: 'user',
                        module: 'Users',
                        id: '1',
                        name: 'Max Jensen',
                        first_name: 'Max',
                        last_name: 'Jensen'
                    }
                },
                {
                    field_name: 'last_name',
                    value: 'Belcher',
                    date_modified: '2017-01-20T12:44:58-08:00',
                    source: {
                        type: 'pmse_process',
                        module: 'pmse_Inbox',
                        id: 'pid1',
                        pmse_project_id: 'ppid1',
                        name: 'My Process',
                    }
                },
                {
                    field_name: 'phone_office',
                    value: '555-555-5555',
                    date_modified: '2018-01-23T02:44:58-08:00',
                    source: {
                        type: 'markto',
                    }
                },
                {
                    field_name: 'email',
                    value: 'foo@example.com',
                    date_modified: '2018-01-23T12:44:58-08:00',
                    source: {
                        type: 'user',
                        module: 'Users',
                        id: '1',
                        name: 'Max Jensen',
                        first_name: 'Max',
                        last_name: 'Jensen'
                    }
                },
                {
                    field_name: 'email',
                    value: 'bar@example.net',
                    date_modified: '2018-01-23T12:44:58-08:00',
                    source: {
                        type: 'markto'
                    }
                }
            ]);
            view._renderData();
            var types = ['varchar', 'varchar', 'base', 'base', 'base'];
            _.each(view.collection.models, function(model, index) {
                var fields = model.fields;
                var value = _.findWhere(fields, {name: 'value'});
                expect(fields.length).toEqual(4);
                expect(value.type).toEqual(types[index]);
            });
        });
    });

    describe('MarkForErasureCollection', function() {
        describe('sync', function() {
            it('should make a call to the PII endpoint and translate retrieved records to fields', function() {
                var url = 'rest/v11/Contacts/5/pii';
                var attributes = {key: 'value'};
                var dummySyncCallbacks = {
                    success: $.noop,
                    error: $.noop,
                    complete: $.noop,
                    abort: $.noop
                };

                sinon.collection.stub(app.api, 'buildURL').returns(url);
                sinon.collection.stub(app.data, 'getSyncCallbacks').returns(dummySyncCallbacks);
                var defaultSuccessCallbackStub = sinon.collection.stub();
                sinon.collection.stub(app.data, 'getSyncSuccessCallback').returns(defaultSuccessCallbackStub);

                var callStub = sinon.collection.stub(app.api, 'call');
                var dummyFields = [{dummy: 'field'}];
                callStub.yieldsTo('success', {fields: dummyFields});
                view.collection.sync('read', model, {attributes: attributes, params: {}});
                expect(callStub).toHaveBeenCalledWith(
                    'read',
                    url,
                    attributes
                );

                expect(defaultSuccessCallbackStub).toHaveBeenCalledWith({
                    fields: dummyFields,
                    records: dummyFields
                });
            });
        });
    });
});
