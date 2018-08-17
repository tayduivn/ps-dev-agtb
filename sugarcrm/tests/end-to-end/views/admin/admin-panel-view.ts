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

import BaseView from '../base-view';

/**
 * Represents admin panel view.
 *
 * @class AdminPanelView
 * @extends BaseView
 */
export default class AdminPanelView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            links: {
                moduleloader: '#module_loader',
                licensemanagement: '#license_management',
                systemsettings: '#configphp_settings',
                studio: '#studio',
            },
        });
    }
}
