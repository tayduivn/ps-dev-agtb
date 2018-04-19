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

import BaseView from './base-view';
import QliTableRecord from './qli-table-record';

/**
 * Represents Record view.
 *
 * @class GroupRecord
 * @extends BaseView
 */
export default class GroupRecord extends QliTableRecord {

    public id: string;

    constructor(options) {
        super(options);

        this.id = options.id;
        this.module = 'ProductBundles';

        this.selectors = this.mergeSelectors({
            $: this.id ? `[data-record-id="${this.id}"]` : '',
        });
    }
}
