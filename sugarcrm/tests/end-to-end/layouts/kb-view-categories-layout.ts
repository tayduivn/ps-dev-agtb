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
import ListLayout from './list-layout';
import {KbCategoriesListView} from '../views/kb-categories-list-view';

/**
 * Represents Knowledge Base Categories drawer
 *
 * @class KBViewCategoriesDrawer
 * @extends ListLayout
 */
export default class KBViewCategoriesDrawer extends ListLayout {

    public KBCategoriesList: KbCategoriesListView;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
        });

        this.type = 'drawer';

        this.KBCategoriesList = this.createComponent<KbCategoriesListView>(KbCategoriesListView);
    }
}
