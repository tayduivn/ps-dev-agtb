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

import * as _ from 'lodash';
import {Then} from '@sugarcrm/seedbed';
import ProductCatalogQuickPicksDashlet from '../views/product-catalog-quick-picks-dashlet-view';
import {TableDefinition} from 'cucumber';

/**
 *  Verify that product is (isn't) present in the the active tab of Product Catalog Quick Picks dashlet
 *
 *  @example: I verify product *Prod_1 exists in #Dashboard.ProductCatalogQuickPicksDashlet
 */
Then(/^I verify product (\*[a-zA-Z](?:\w|\S)*) (not )?exists in (#[a-zA-Z](?:\w|\S)*)$/,
    async function( record:any, not,  view: ProductCatalogQuickPicksDashlet) {

        let isItemExists = await view.isRecordExists(record.id);

        if (_.isEmpty(not) !== isItemExists) {
            throw new Error('Expected ' + (not || '') + ' to see product ' + record.input.getData().name);
        }
    });


/**
 *  Verify if pagination chevron in Favorites tab is active
 *
 *  @example:
 *      Then I verify pagination chevron in #Dashboard.ProductCatalogQuickPicksDashlet
 *      | buttonName | Disabled |
 *      | rightNav   | false    |
 *      | leftNav    | true     |
 *
 */
Then(/I verify pagination controls in (#[a-zA-Z](?:\w|\S)*)$/,
    async function( view: ProductCatalogQuickPicksDashlet, data: TableDefinition) {

        let errors = [];
        let rows = data.rows();

        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let expectedValue  = row[1];
            let value  = await view.isControlActive(row[0]);

            if (value.toString() != expectedValue) {
                errors.push(
                        [
                            `Field '${row[0]}' should be`,
                            `\t'${expectedValue}'`,
                            `instead of`,
                            `\t'${value.toString()}'`,
                            `\n`,
                        ].join('\n')
                )
            }
        }

        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }
    });
