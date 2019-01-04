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

import BaseView from '../views/base-view';
import SalesRepWorksheetView from '../views/forecasts-sales-rep-worksheet-view';
import SalesManagerWorksheetView from '../views/forecasts-manager-worksheet-view';


/**
 * Represents Forecast Sales Rep layout
 *
 * @class ForecastsLayout
 * @extends BaseView
 */
export default class ForecastsLayout extends BaseView {

    public SalesRepWorksheet: SalesRepWorksheetView;
    public SalesManagerWorksheet: SalesManagerWorksheetView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.main-pane:not([style*="display: none"])'
        });

        this.SalesRepWorksheet = this.createComponent<SalesRepWorksheetView>(SalesRepWorksheetView);
        this.SalesManagerWorksheet = this.createComponent<SalesManagerWorksheetView>(SalesManagerWorksheetView);
    }
}
