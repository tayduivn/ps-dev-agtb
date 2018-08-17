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

import UserProfileView from '../views/user-profile-view';
import BaseView from '../views/base-view';

export default class UserProfileLayout extends BaseView {
    public UserProfileView: UserProfileView;
    public defaultView: UserProfileView;

    constructor(options) {
        super(options);
        this.defaultView = this.UserProfileView = this.createComponent(UserProfileView);
    }

    public async clickButton(btnName: string): Promise<void> {
        return this.defaultView.clickButton(btnName);
    }
}
