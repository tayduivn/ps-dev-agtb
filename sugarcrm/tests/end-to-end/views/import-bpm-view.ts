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
import * as path from 'path';

/**
 * Represents Record view.
 *
 * @class ImportBpmView Import files related to Business Process Management
 * @extends BaseView
 */
export default class ImportBpmView extends BaseView {

    public static ImportOptions = {
        BUSINESS_RULES: '2',
        EMAIL_TEMPLATES: '3',
    };

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            chooseFile: '.edit input',
            importOptionSelector: '.table:nth-child({{importOption}}) .btn.checkall input',
        });
    }

    /**
     *  Import BPM, PET, or BPR file into Sugar
     *
     * @param {string} fileName file name
     * @param {string} folderName folder name
     * @returns {WebdriverIO.Client<void>}
     */
    public importFile(folderName: string, fileName: string): WebdriverIO.Client<void> {

        let filePath = path.resolve(__dirname, '..', 'import', folderName, fileName);
        return this.driver.chooseFile(this.$('chooseFile'), filePath).pause(2000);
    }

    /**
     * Select which part of the BPM workflow to import
     *
     * @param {string} importOption
     * @returns {Promise<void>}
     */
    public async selectImportOptions(importOption: string ) {
        let selector = this.$('importOptionSelector', {importOption});
        await this.driver.click(selector);
    }
}
