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
import DrawerLayout from './drawer-layout';
import QuotesConfigAccordion from '../views/quotes-config-accordion';
import QuotesConfigRHSPane from '../views/quotes-config-rhspane';

/**
 * Represents Quotes Configuration Drawer (accessible through Admin Panel)
 *
 * @class QuotesConfigDrawerLayout
 * @extends DrawerLayout
 */
export default class QuotesConfigDrawerLayout extends DrawerLayout {

    public Accordion: QuotesConfigAccordion;
    public IntelligencePane: QuotesConfigRHSPane;

    constructor(options) {

        super(options);

        this.Accordion = this.createComponent<QuotesConfigAccordion>(QuotesConfigAccordion, {
            module: options.module,
            default: true
        });

        this.IntelligencePane = this.createComponent<QuotesConfigRHSPane>(QuotesConfigRHSPane, {
            module: options.module,
        });
    }
}
