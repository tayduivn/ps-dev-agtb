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
import FilterView from '../views/filter-view';

/**
 *  Verify button state (active or disable) on filter View
 *
 *  @example
 *  Then I verify button state in #TasksList.FilterView
 *       | control        | Disabled |
 *       | VisualPipeline | true     |
 */
Then(/I verify button state in (#[a-zA-Z](?:\w|\S)*)$/,
    async function( view: FilterView, data: TableDefinition) {

        let errors = [];
        let rows = data.rows();

        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let expectedValue  = row[1];
            let value  = await view.isDisabled(row[0].toLowerCase());

            if (value.toString() != expectedValue) {
                errors.push(
                    [
                        `The '${row[0]}' control expected to be`,
                        `\t'${expectedValue}'`,
                        `instead of`,
                        `\t'${value.toString()}'`,
                        `\n`,
                    ].join('\n')
                );
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
