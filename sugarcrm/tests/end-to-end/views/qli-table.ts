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
import {seedbed} from '@sugarcrm/seedbed';
import QliRecord from './qli-record';
import CommentRecord from './comment-record';
import GroupRecord from './group-record';


/**
 * Represents Record view.
 *
 * @class RecordView
 * @extends BaseView
 */
export default class QliTable extends BaseView {

    public QliRecord: QliRecord;
    public CommentRecord: CommentRecord;
    public GroupRecord: GroupRecord;


    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.quote-data-container',
            plusButton: '.quote-data-panel-controls',
            menu: {
                createLineItem: '[name=create_qli_button]',
                createComment: '[name=create_comment_button]',
                createGroup: '[name=create_group_button]'
            }
        });

        this.QliRecord =  this.createComponent<QliRecord>(QliRecord);
        this.CommentRecord =  this.createComponent<CommentRecord>(CommentRecord);
        this.GroupRecord =  this.createComponent<GroupRecord>(GroupRecord);

    }

    getRecord(recordIndex) {
        return this.createComponent<QliRecord>(QliRecord, {recordIndex});
    }

    async openMenu() {
        await seedbed.client.click(this.$('plusButton'));
    }

    async clickMenuItem(itemName) {
        await seedbed.client.click(this.$(`menu.${itemName}`));
    }

}

