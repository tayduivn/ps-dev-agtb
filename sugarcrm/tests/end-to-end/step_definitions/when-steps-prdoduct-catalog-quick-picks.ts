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

import {When} from '@sugarcrm/seedbed';
import ProductCatalogQuickPicksDashlet from '../views/product-catalog-quick-picks-dashlet-view';
import DashletView from '../views/dashlet-view';

/**
 * Switch between tabs in the Product Catalog Quick Picks dashlet
 *
 * @example "I select Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet"
 */
When(/^I select (Recently Used|Favorites) tab in (#\S+)$/,
    async function (action: string, view: ProductCatalogQuickPicksDashlet): Promise<void> {
        await view.toggleTabs();
    }, {waitForApp: true});

/**
 * Click Product Info Icon in the Product Catalog Quick Picks dashlet
 *
 * @example "I click *Prod_1 on Favorites tab in #Dashboard.ProductCatalogQuickPicksDashlet"
 */
When(/^I click (\*[a-zA-Z](?:\w|\S)*) on (Recently Used|Favorites) tab in (#\S+)$/,
    async function (record: { id: string }, tab: string, view: ProductCatalogQuickPicksDashlet): Promise<void> {
        await view.clickRecordByID(record.id);
    }, {waitForApp: true});

/**
 * Select dashlet operations Edit, Refresh, and Delete
 *
 * Note: This step definition is NOT available for dashlets in record create mode.
 *
 * @example "I refresh #Dashboard.ProductCatalogQuickPicksDashlet dashlet"
 */
When(/^I (edit|refresh|remove) (#\S+) dashlet$/,
    async function (action: string, view: DashletView ): Promise<void> {
        await view.performAction(action.toLowerCase());
    }, {waitForApp: true});

/**
 * Go to next/previous page in Favorites tab of Product Catalog Quick Picks dashlet
 *
 * @example "I go to next page in #Dashboard.ProductCatalogQuickPicksDashlet"
 */
When(/^I go to (next|previous) page in (#\S+)/,
    async function (page: string, view: ProductCatalogQuickPicksDashlet ): Promise<void> {

    switch (page) {
        case 'next':
            await view.clickChevron('rightChevron');
            break;
        case 'previous':
            await view.clickChevron('leftChevron');
            break;
        }
    }, {waitForApp: true});

/**
 * Go to specific page specified by page number in Favorites tab of Product Catalog Quick Picks dashlet
 *
 * @example "I go to page 2 in #Dashboard.ProductCatalogQuickPicksDashlet"
 */
When(/^I go to page (\d+) in (#\S+)/,
    async function (pageNum , view: ProductCatalogQuickPicksDashlet ): Promise<void> {
            await view.clickPageByPageNum(pageNum);
    }, {waitForApp: true});
