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
describe('ConsoleConfiguration.Fields.FieldList', function() {
    var app;
    var module = 'ConsoleConfiguration';
    var field;
    var fieldName;
    var model;
    var fields;
    var getViewStub;

    beforeEach(function() {
        app = SugarTest.app;

        fields = [
            {
                name: 'next_renewal_date',
                label: 'LBL_NEXT_RENEWAL_DATE',
                subfields: [
                    {
                        name: 'next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        default: 'true',
                        enabled: 'true',
                        type: 'relative-date',
                        widget_name: 'widget_next_renewal_date',
                    },
                    {
                        name: 'next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        default: 'true',
                        enabled: 'true',
                    },
                ],
            },
        ];

        model = app.data.createBean(module);
        model.set({
            enabled_module: 'Accounts',
        });

        var enabledModule = model.get('enabled_module');

        getViewStub = sinon.collection.stub(app.metadata, 'getView')
            .withArgs(enabledModule, 'multi-line-list')
            .returns({
            panels: [
                {
                    fields: fields,
                },
            ],
        });

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField(
            'base',
            fieldName,
            'field-list',
            'edit',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        model.dispose();
    });

    describe('getViewMetaData', function() {
        it('should call proper function based on defaultViewMeta in context', function() {
            var fieldContextGetSpy = sinon.collection.spy(field.context, 'get');
            field.getViewMetaData('Accounts');

            // when no defaultViewMeta in context, app.metadata.getView
            // should be called twice, one from initialize, another from  getViewMetaData
            expect(getViewStub).toHaveBeenCalled(2);
            // should not call context.get
            expect(fieldContextGetSpy).toHaveBeenCalled(0);

            // this time, set defaultViewMeta to context and try again
            field.context.set('defaultViewMeta', {Accounts: {aaa: true}});
            field.getViewMetaData('Accounts');

            // when defaultViewMeta exists in context, shouldn't call getView
            // so the call count should remain twice.
            expect(getViewStub).toHaveBeenCalled(2);
            // should call context.get instad
            expect(fieldContextGetSpy).toHaveBeenCalled(1);
        });
    });

    describe('initialize', function() {
        it('should call getTabContentFields once', function() {
            var getTabContentFieldsSpy = sinon.collection.spy(field, 'getMappedFields');

            field.initialize(field.options);

            expect(getTabContentFieldsSpy.calledOnce).toBe(true);
        });
    });

    describe('getTabContentFields', function() {
        it('should return field to subfield mapping', function() {
            var expected = {
                next_renewal_date: [
                    {
                        name: 'widget_next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_name: 'next_renewal_date',
                        widget_name: 'widget_next_renewal_date'
                    },
                    {
                        name: 'next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_name: 'next_renewal_date',
                    },
                ],
            };

            var actual = field.getMappedFields();

            expect(actual).toEqual(expected);
        });
    });
});
