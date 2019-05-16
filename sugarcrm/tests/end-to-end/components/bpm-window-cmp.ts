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

import {BaseView, seedbed} from '@sugarcrm/seedbed';

/**
 * @class BpmWindowCmp
 * @extends BaseView
 */
export default class BpmWindowCmp extends BaseView {

    public alertType: boolean;
    public method: string;

    constructor(options: any = {}) {
        super(options);

        this.selectors = {
            $: '.adam-window',
            container: '.adam-panel-body',
            closeIcon: '.adam-window-close',
            // to add a new note
            field: {
              $: '.adam-field',
              textArea: '#notesTextArea',
              addNotesButton: '.adam-button.btn.btn-primary'
            },
            // to get value of already created last note
            lastFieldNote: '.adam-field:last-child div:first-child p',
            lastFieldDeleteButton: '.adam-field:last-child a#deleteNoteBtn',

            // to do screen compare
            elements: {
                // Entire popup window
                BpmWindow: '.adam-window',
                // Title of the pop-up window
                BpmWindowTitle: '.adam-window-title',
                // Body of the pop-up window
                BpmWindowBody: '.adam-panel-body',
            }
        };
    }

    /**
     * Close BMP pop-up window
     *
     * @returns {Promise<void>}
     */
    public async close() {
        return this.driver.waitForVisibleAndClick(this.$('closeIcon'));
    }

    /**
     * Add note to the process
     *
     * @param {string} value
     * @returns {Promise<void>}
     */
    public async addNote(value: string) {
        await this.driver.waitForVisible(this.$('field.textArea'));
        await this.driver.setValue(this.$('field.textArea'), value);
        await this.clickAddNotesButton();
    }

    /**
     * Retrieve last note's text
     *
     * @returns {Promise<any>}
     */
    public async getLastNote() {
        await this.driver.waitForVisible(this.$('lastFieldNote'));
        return this.driver.getText(this.$('lastFieldNote'));
    }

    /**
     * Click Add Notes button
     *
     * @returns {Promise<void>}
     */
    public async clickAddNotesButton() {
        return this.driver.waitForVisibleAndClick(this.$('field.addNotesButton'));
    }

    /**
     * Delete last note
     *
     * @returns {Promise<void>}
     */
    public async deleteLastNote() {
        return this.driver.waitForVisibleAndClick(this.$('lastFieldDeleteButton'));
    }
}
