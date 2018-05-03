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
import LeadConversionView from '../views/lead-conversion-view';
import HeaderView from '../views/record-header-view';

/**
 * Represents a Lead Conversion Drawer.
 *
 * @class LeadConversionLayout
 * @extends DrawerLayout
 */
export default class LeadConversionLayout extends DrawerLayout {

    public HeaderView: HeaderView;
    public ContactContent: LeadConversionView;
    public AccountContent: LeadConversionView;
    public OpportunityContent: LeadConversionView;

    constructor(options) {

        super(options);

        this.type = 'drawer';

        this.HeaderView = this.createComponent<HeaderView>(HeaderView, {
            module: options.module,
        });

        this.ContactContent = this.createComponent<LeadConversionView>(LeadConversionView, {module: 'Contacts'});
        this.AccountContent = this.createComponent<LeadConversionView>(LeadConversionView, {module: 'Accounts'});
        this.OpportunityContent = this.createComponent<LeadConversionView>(LeadConversionView, {module: 'Opportunities'});
    }
}
