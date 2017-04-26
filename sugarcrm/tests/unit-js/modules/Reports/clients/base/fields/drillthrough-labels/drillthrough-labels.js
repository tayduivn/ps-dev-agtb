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
ddescribe('Reports.Fields.DrillthroughLabels', function() {
    var app;
    var field;
    var chartModule = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'drillthrough-labels',
            type: 'drillthrough-labels',
            viewName: 'detail',
            module: 'Reports',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('format', function() {
        var langStub;

        beforeEach(function() {
            field.context.set('chartModule', chartModule);
            SugarTest.testMetadata.init();
            langStub = sinon.collection.stub(app.lang, 'get')
                .withArgs('LBL_INDUSTRY', chartModule).returns('Industry');
            langStub.withArgs('LBL_TYPE', chartModule).returns('Type');

        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
            sinon.collection.restore();
        });

        it('should only set series when group isn\'t present', function() {
            var meta = {
                fields: {
                    industry: {
                        name: 'industry',
                        type: 'enum',
                        vname: 'LBL_INDUSTRY'
                    }
                }
            };
            SugarTest.testMetadata.updateModuleMetadata(chartModule, meta);
            SugarTest.testMetadata.set();

            var filterDef = [
                {
                    'self:industry': ['Biotechnology']
                }
            ];
            field.context.set('filterDef', filterDef);
            field.format();
            expect(field.groupTitle).toBe('Industry: ');
            expect(field.group).toBe('Biotechnology');
        });

        it('should set series and group when both are present', function() {
            var meta = {
                fields: {
                    industry: {
                        name: 'industry',
                        type: 'enum',
                        vname: 'LBL_INDUSTRY'
                    },
                    account_type: {
                        name: 'account_type',
                        type: 'enum',
                        vname: 'LBL_TYPE'
                    }
                }
            };
            SugarTest.testMetadata.updateModuleMetadata(chartModule, meta);
            SugarTest.testMetadata.set();

            var filterDef = [
                {
                    industry: ['Biotechnology']
                },
                {
                    account_type: ['Customer']
                }
            ];
            field.context.set('filterDef', filterDef);
            field.format();
            expect(field.groupTitle).toBe('Industry: ');
            expect(field.group).toBe('Biotechnology');
            expect(field.seriesTitle).toBe('Type: ');
            expect(field.series).toBe('Customer');
        });
    });

});
