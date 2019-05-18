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

import {BaseView} from '@sugarcrm/seedbed';
import {KeyCodes} from '../step_definitions/steps-helper';
/**
 * Represents a Detail/Record page layout.
 *
 * @class ActivityStreamLayout
 * @extends BaseView
 */
export default class ActivityStreamLayout extends BaseView {

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.block',
            messages: {
                $: '.activitystream-list.results li:nth-child({{index}})',
                message: 'div .tagged',
                user: '.details a',
                comment: '.fa.fa-comment',
                preview: '.fa.fa-eye'
            },
            omnibar: {
                $: '.omnibar',
                inputbox: '.inputwrapper',
                addpost: '.addPost',
                sayit: '.inputwrapper .sayit'
            },
            comments: {
                $: '.activitystream-list.results li:nth-child({{index}}) .comments .reply-input',
                replyArea: 'div',
                reply: ' .reply.taggable',
                replyButton: '.btn.btn-primary'
            }
        });
    }

    /**
     * Get message from activity stream
     *
     * @param index
     * @returns {Promise<any>}
     */
    public async getMessage(index) {
        let selector  = this.$('messages.message', {index} );
        return this.driver.getText(selector);
    }

    /**
     * Post to activity stream
     *
     * @param value
     * @returns {Promise<void>}
     */
    public async addPost(value) {
        await this.driver.click(this.$(`omnibar.inputbox`));
        await this.driver.waitForApp();
        let selector  = this.$('omnibar.sayit');
        await this.driver.setValue(selector, value);
        await this.driver.waitForApp();
        // The delay is needed to properly reference an existing record in activity message
        await this.driver.pause(2000);
        // Press Enter
        await this.driver.keys(KeyCodes.ENTER);
        await this.driver.waitForApp();
        await this.driver.click(this.$(`omnibar.addpost`));
        await this.driver.waitForApp();
    }

    /**
     * Add comment(s) to the top activity in Activity Stream
     *
     * @param index
     * @param value
     * @returns {Promise<void>}
     */
    public async addComment(index, value) {
        let selector  = this.$(`messages.comment`, {index});
        await this.driver.click(selector);
        await this.driver.waitForApp();

        selector  = this.$(`comments.replyArea`, {index});
        await this.driver.click(selector);
        await this.driver.waitForApp();

        await this.driver.keys(value);
        await this.driver.waitForApp();

        selector = this.$(`comments.replyButton`, {index});
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }
}
