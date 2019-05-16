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
import BpmWindowCmp from '../components/bpm-window-cmp';

/**
 * BPM Window Pop-up window for checking process status, adding process notes,
 * and viewing process history
 *
 * Note: This view is only needed for using compare screenshots functionality
 * since 'view' is one of the required arguments there.
 *
 * @class BpmWindowView
 * @extends BaseView
 */
export default class BpmWindowView extends BaseView {

    public BpmWindowCmp: BpmWindowCmp;

    constructor(options) {
        super(options);

        this.BpmWindowCmp = this.createComponent<BpmWindowCmp>(BpmWindowCmp);
    }
}
