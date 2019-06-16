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
              addNotesButton: '.adam-button.btn.btn-primary',
              user: '.select2-container',
              type: {
                  $: '#adhoc_type',
                  option: 'option[value={{routeType}}]',
              },
              label: '#not_content'
            },

            // to get value of already created last note
            lastFieldNote: '.adam-field:last-child div:first-child p',
            lastFieldDeleteButton: '.adam-field:last-child a#deleteNoteBtn',

            // to access Save and Cancel buttons
            buttons: {
                save: '.adam-button.btn-primary',
                cancel: '.adam-button.btn-invisible',
            },

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

    /**
     * Change process user
     *
     * @param {string} userName
     * @returns {Promise<void>}
     */
    public async selectProcessUser(userName: string) {
        // Click on the 'User' control
        await this.driver.click(this.$('field.user'));
        await this.driver.waitForApp();

        // Type in the new value
        await this.driver.keys(userName);
        // Forcing the pause to wait for the select2 debounce after text entry
        await this.driver.pause(1500);
        await this.driver.waitForApp();

        // Confirm new value by click <enter>
        await this.driver.keys('\uE007');
        await this.driver.pause(1000);
        await this.driver.waitForApp();
    }

    /**
     * Change Process Type
     *
     * @param {string} routeType
     * @returns {Promise<void>}
     */
    public async selectRoutingType(routeType: string) {
        // Click on the 'Type' control
        await this.driver.click(this.$('field.type'));
        await this.driver.pause(1500);
        await this.driver.waitForApp();
        // Select routing type
        await this.driver.click(this.$('field.type.option', {routeType}));
        await this.driver.pause(1500);
        await this.driver.waitForApp();
    }

    /**
     * Add a note while assign a new process user
     *
     * @param {string} val
     * @returns {Promise<void>}
     */
    public async addText(val: string) {
        let selector = this.$('field.label');
        await this.driver.setValue(selector, val);
        await this.driver.waitForApp();
    }

    /**
     * Click Save or Cancel button
     *
     * @param {string} btnName
     * @returns {Promise<void>}
     */
    public async btnClick(btnName: string) {
        let selector = this.$(`buttons.${btnName}`);
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }
}
