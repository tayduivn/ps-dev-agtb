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
import DrawerLayout from '../layouts/drawer-layout';
import ListView from './list-view';

/**
 * Represents a Detail/Record page layout.
 *
 * @class FindDuplicatesDrawer
 * @extends DrawerLayout
 */
export default class FindDuplicatesDrawer extends DrawerLayout {

    public ListView: ListView;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
        });

        this.type = 'drawer';
        this.ListView = this.createComponent<ListView>(ListView, { module: options.module });
    }
}
