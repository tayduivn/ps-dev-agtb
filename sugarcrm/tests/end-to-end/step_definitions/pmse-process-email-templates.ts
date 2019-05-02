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
'use strict';

import {givenStepsHelper, whenStepsHelper, stepsHelper} from '@sugarcrm/seedbed';
import {Utils, Given, When, Then, seedbed} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import ListView from '../views/list-view';
import PmseEtDesign from '../views/pmse-et-design';
import PmseEtFieldSelect from '../views/pmse-et-compose-varbook-list';
import * as _ from 'lodash';
import {chooseModule, chooseRecord, recordViewHeaderButtonClicks, checkValues} from './general_bdd';

Given(/^I design (\w+) \*(\w+)/,
    async function(module: string, name: string) {
        await chooseModule(module);
        let view = await seedbed.components[`${module}List`].ListView;
        await seedbed.client.driver.waitForApp();
        let record = await seedbed.cachedRecords.get(name);
        await chooseRecord({id: record.id}, view);
        let rec_view = await seedbed.components[`${name}Record`];
        await recordViewHeaderButtonClicks('actions', rec_view);
        await recordViewHeaderButtonClicks('design_pet', rec_view);
        await seedbed.client.driver.waitForApp();
}, {waitForApp: true});

const petRelatedModules = {};
When(/^a placeholder is inserted in the (subject|content) \*(\w+) record from module (\w+):$/,
    async function(recordField: string, recordName: string, recordModule: string, table: TableDefinition) {
        let selectedModule;
        let petDesignView;
        let petSelectField;
        let gearIcon;
        let record = await seedbed.cachedRecords.get(recordName);
        const rec_view = await seedbed.components[`${recordName}Record`];
        const rModule = table['rawTable'][1][0];

        seedbed.defineComponent(`${record.id}PmseEtDesign`, PmseEtDesign, {id: 'PmseEtDesign'});
        petDesignView = await seedbed.components[`${record.id}PmseEtDesign`];

        seedbed.defineComponent(`${record.id}PmseEtComposeVarbookList`, PmseEtFieldSelect, {id: 'PmseEtComposeVarbookList'});
        petSelectField = await seedbed.components[`${record.id}PmseEtComposeVarbookList`];

        const linkName = table['rawTable'][1][3];
        let linkText;
        const findModulesListener = (data, req, res) => {
            if (req.method === 'GET' && /pmse_Emails_Templates\/\w+\/find_modules/.test(req.url)) {
                const responseText = data.buffer.toString();

                if (responseText === '') {
                    if (petRelatedModules[rModule] && petRelatedModules[rModule][linkName]) {
                        linkText = petRelatedModules[rModule][linkName];
                    }
                } else {
                    let result = JSON.parse(responseText).result;
                    let linkInfo: any = _.find(result, (linkObj: any) => {
                        return linkObj.relationship === linkName;
                    });

                    if (linkInfo) {
                        petRelatedModules[rModule] = petRelatedModules[rModule] || {};
                        petRelatedModules[rModule][linkName] = linkText = linkInfo.text;
                    }
                }
            }
        };
        seedbed.addAsyncHandler(seedbed.events.RESPONSE, findModulesListener);
        await seedbed.client.driver.waitForApp();
        // Depending on the field, subject or content, click the corresponding button to open the drawer.
        gearIcon = recordField === 'subject' ? 'subject_gear' : 'content_gear';
        await petDesignView.clickButton(gearIcon);
        await seedbed.client.driver.waitForApp();
        seedbed.removeAsyncHandler(seedbed.events.RESPONSE, findModulesListener);
        await seedbed.client.driver.waitForApp();

        // Change from target module to a related module.
        if (recordModule != table['rawTable'][1][0]) {
            await petSelectField.clickButton('module_dropdown');
            await seedbed.client.driver.waitForApp();
            await petSelectField.clickModuleSelect(linkText);
            await seedbed.client.driver.waitForApp();
            selectedModule = recordModule;
        } else {
            selectedModule = table['rawTable'][1][0];
        }
        // Select a field from the table.
        await petSelectField.selectFieldOption(selectedModule, table['rawTable'][1][1], table['rawTable'][1][2]);
        await seedbed.client.driver.waitForApp();

        await recordViewHeaderButtonClicks('done', rec_view);
        await seedbed.client.driver.waitForApp();
}, {waitForApp: true});

When(/^a link placeholder is inserted in the content \*(\w+) record:$/,
    async function(recordName: string, table: TableDefinition) {
        let record = await seedbed.cachedRecords.get(recordName);
        let rec_view = await seedbed.components[`${recordName}Record`];

        seedbed.defineComponent(`${record.id}PmseEtDesign`, PmseEtDesign, {id: 'PmseEtDesign'});
        let petDesignView = await seedbed.components[`${record.id}PmseEtDesign`];

        await petDesignView.clickButton('content_link');
        await seedbed.client.driver.waitForApp();

        seedbed.defineComponent(`${record.id}PmseEtComposeVarbookList`, PmseEtFieldSelect, {id: 'PmseEtComposeVarbookList'});
        let petSelectLink = await seedbed.components[`${record.id}PmseEtComposeVarbookList`];

        // Select a link from the table.
        await petSelectLink.selectRecordLink(table['rawTable'][1][0]);
        await seedbed.client.driver.waitForApp();

        await recordViewHeaderButtonClicks('select', rec_view);
        await seedbed.client.driver.waitForApp();
}, {waitForApp: true});

Then(/^(\w+) \*(\w+) should have the values:$/,
    async function(module, recordName: string, table: TableDefinition) {
        let record = await seedbed.cachedRecords.get(recordName);
        let rec_view = await seedbed.components[`${recordName}Record`];
        await checkValues(rec_view, table);
        await recordViewHeaderButtonClicks('cancel', rec_view);
    }, {waitForApp: true}
);
