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

import DashletView from './dashlet-view';
import {KeyCodes} from '../step_definitions/steps-helper';

/**
 * Represents Comment Log dashlet
 *
 * @class CommentLogDashletView
 * @extends DashletView
 */
export default class CommentLogDashlet extends DashletView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `.dashlet-container[name=dashlet_${options.position}]`,
            newComment: {
                $: '.new-comment',
                textarea: '[aria-label="Comment Log"]',
                addButton: '[data-action="save"]'
            },
            message: {
                $: '.comment-log-dashlet-content li:nth-child({{index}})',
                content: '.body .msg-content',
            },
            view_all: '.btn.btn-link.btn-invisible.more'
        });
    }

    /**
     * Add new comment
     *
     * @param {string} value
     */
    public async addNewComment(value: string) {
        // Type-in comment's value
        let selector = this.$('newComment.textarea');
        await this.driver.setValue(selector, value);

        // Wait for 2 seconds and click enter for cases where another user or another
        // record is referenced in the comment
        await this.driver.pause(2000);
        // Press <ENTER> to link referenced user or record
        await this.driver.keys(KeyCodes.ENTER);
        await this.driver.pause(1000);
        await this.driver.waitForApp();

        // Click 'Add' button
        selector = this.$('newComment.addButton');
        await this.driver.click(selector);
    }

    /**
     * Return comment message based by the index
     *
     * @param {string} index
     * @return {string} value of the comment
     */
    public async getCommentByIndex(index: string) {
        let selector = this.$('message.content', {index});
        return await this.driver.getText(selector);
    }

    /**
     *  Click View All button if it is visible
     */
    public async clickViewAllBtn() {
        let selector = this.$('view_all');
        let isVisible = await this.driver.isVisible(selector);
        if (isVisible) {
            await this.driver.click(selector);
            await this.driver.waitForApp();
        }
    }
}
