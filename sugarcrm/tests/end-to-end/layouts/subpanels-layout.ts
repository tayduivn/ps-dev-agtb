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
import SubpanelLayout from './subpanel-layout';

/**
 * Represents subpanels layout.
 *
 * @class SubpanelsLayout
 * @extends BaseView
 */
export default class SubpanelsLayout extends BaseView {
    public subpanels: SubpanelLayout[];

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.subpanels-layout',
        });

        this.subpanels = this.createSubpanels();
    }

    /**
     * Opens the subpanel corresponding to the given link.
     * @param {string} subpanelName The link name for the subpanel to open.
     * @return {Promise<any>} The result of opening the specified subpanel.
     */
    public async openSubpanel(subpanelName: string): Promise<any> {
        let subpanel = this.subpanels[subpanelName];
        if (!subpanel) {
            throw new Error('Subpanel ' + subpanelName + ' does not exist!');
        }
        await subpanel.open();
    }

    public async createRecord(subpanelName: string): Promise<any> {
        let subpanel = this.subpanels[subpanelName];
        if (!subpanel) {
            throw new Error('Subpanel ' + subpanelName + ' does not exist!');
        }
        await subpanel.createRecord();
    }

    public async linkRecord(subpanelName: string): Promise<any> {
        let subpanel = this.subpanels[subpanelName];
        if (!subpanel) {
            throw new Error('Subpanel ' + subpanelName + ' does not exist!');
        }
        await subpanel.openActionsMenu();
        await subpanel.selectMenuItem();
    }


    /**
     * Creates the subpanels corresponding to this module's metadata.
     * @return {SubpanelLayout[]} The list of created subpanels.
     * @private
     */
    private createSubpanels(): SubpanelLayout[] {
        let meta = seedbed.meta.modules[this.module];
        if (!meta) {
            throw new Error('Metadata not found for module: ' + this.module);
        }
        if (!meta.layouts ||
            !meta.layouts.subpanels ||
            !meta.layouts.subpanels.meta ||
            !meta.layouts.subpanels.meta.components) {
            return [];
        }
        let subpanels = [];
        let subpanelComponents = meta.layouts.subpanels.meta.components;
        subpanelComponents.forEach(component => {
            if (!component.context || !component.context.link) {
                return;
            }
            subpanels[component.context.link] = <SubpanelLayout> this.createComponent(SubpanelLayout, {
                link: component.context.link,
            });
        });
        return subpanels;
    }
}
