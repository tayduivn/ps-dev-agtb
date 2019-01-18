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

/**
 * @class KbListItemView
 * @extends BaseView
 */
export default class KbListItemView extends BaseView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'li[data-id*="{{id}}"]',
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }
}
