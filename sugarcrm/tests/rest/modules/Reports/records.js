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

const {Agent, Fixtures} = require('@sugarcrm/thorn');
const chakram = require('chakram');
const expect = require('chakram').expect;

describe('Reports.Records', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        let johnId = records.Users[0].id;

        records = [
            {attributes: {name: 'Account1', industry: 'TEST', assigned_user_id: johnId}},
            {attributes: {name: 'Account2', industry: 'TEST', assigned_user_id: johnId}},
            {attributes: {name: 'Account3', industry: 'Engineering', assigned_user_id: johnId}}
        ];

        yield Fixtures.create(records, {module: 'Accounts'});

        let content = {
            display_columns: [{name: 'name', label: 'Name', table_key: 'self'}],
            module: 'Accounts',
            group_defs: [{"table_key": "self", "name": "industry", "type": "enum"}],
            summary_columns: [],
            report_name: 'test',
            do_round: 1,
            numerical_chart_column: '',
            numerical_chart_column_type: '',
            assigned_user_id: johnId,
            report_type: 'tabular',
            full_table_list: {self: {value: 'Accounts', module: 'Accounts', label: 'Accounts'}},
            filters_def: {Filter_1: {operator: 'AND'}},
            chart_type: 'none'
        };

        let report = {
            name: 'test',
            module: 'Accounts',
            report_type: 'tabular',
            assigned_user_id: johnId,
            content: JSON.stringify(content)
        };

        let response = yield Agent.as('John').post('Reports', report);
        this.reportId = response.body.id;
    });

    after(function*() {
        yield Agent.as('John').delete('Reports/' + this.reportId);
        yield Fixtures.cleanup();
    });

    it('should return filtered records whose field value matches given value', function*() {
        let filter = 'group_filters%5B0%5D%5Bindustry%5D=TEST';
        let response = yield Agent.as('John').get('Reports/' + this.reportId + '/records?' + filter);
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(2);
            expect(records[0].industry).to.be.equal('TEST');
        });
    });
});
