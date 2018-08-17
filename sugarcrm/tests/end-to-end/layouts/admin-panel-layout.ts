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
import AdminPanelView from '../views/admin/admin-panel-view';
import BWCEditView from '../views/bwc-edit-view';

/**
 * Represents Admin Panel layout.
 *
 * @class AdminPanelLayout
 * @extends BaseView
 */
export default class AdminPanelLayout extends BaseView {

    public AdminPanelView: AdminPanelView;
    public defaultView: AdminPanelView;
    public SystemSettings: BWCEditView;

    constructor(options) {

        super(options);

        this.defaultView = this.AdminPanelView = this.createComponent<AdminPanelView>(AdminPanelView);

        this.SystemSettings = this.createComponent<BWCEditView>(BWCEditView, {
            module: 'Settings',
        });
    }
}
