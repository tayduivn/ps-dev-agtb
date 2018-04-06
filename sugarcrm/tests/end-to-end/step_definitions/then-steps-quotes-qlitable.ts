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

import {Then} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import RecordLayout from '../layouts/record-layout';
import QliTable from '../views/qli-table'

Then(/^I verify fields on QLI total (header|footer) on (#[a-zA-Z](?:\w|\S)*) view$/, async function (componentName, view: RecordLayout, data: TableDefinition) {

    let fieldsData: any = data.hashes();
    let component = null;

    switch (componentName) {
        case 'header':
            component = view.QliTable.Header;
            break;
        default:
            component = view.QliTable.Footer;
            break;
    }

    let errors = await component.checkFields(fieldsData);

    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }
});

Then(/^I verify fields for (\d+) row for (#\S+)$/, async function (recordIndex, view: QliTable, data: TableDefinition) {

    let fildsData: any = data.hashes();

    const record = view.getRecord(recordIndex);

    let errors = await record.checkFields(fildsData);

    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }
});

