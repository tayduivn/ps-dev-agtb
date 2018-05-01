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
import HeaderView from '../views/record-header-view';
import {BaseView, seedbed} from '@sugarcrm/seedbed';
import RecordView from '../views/record-view';
import SubpanelsLayout from '../layouts/subpanels-layout';
import QliTable from '../views/qli-table';

/**
 * Represents a Detail/Record page layout.
 *
 * @class RecordLayout
 * @extends BaseView
 */
export default class RecordLayout extends BaseView {

    public HeaderView: HeaderView;
    public QliTable: QliTable;
    public SubpanelsLayout: SubpanelsLayout;
    protected type: string;
    public RecordView: RecordView;
    public defaultView: RecordView;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.main-pane',
            'show more': '.show-hide-toggle .btn.more',
            'show less': '.show-hide-toggle .btn.less',
            'more guests': '.detail .btn.btn-link.btn-invisible.more',
        });

        this.type = 'record';

        this.defaultView = this.RecordView = this.createComponent<RecordView>(RecordView, {
            module: options.module,
            default: true
        });

        this.HeaderView = this.createComponent<HeaderView>(HeaderView, {
            module: options.module,
        });

        this.QliTable = this.createComponent<QliTable>(QliTable, {
            module: options.module,
        });

        this.SubpanelsLayout = this.createComponent<SubpanelsLayout>(SubpanelsLayout, {
            module: options.module,
        });
    }

    public async showMore(btnName) {
        if (await this.driver.isVisible(this.$(btnName))) {
            await this.driver.scroll(this.$(btnName));
            await this.driver.click(this.$(btnName));
        }
    }
}
