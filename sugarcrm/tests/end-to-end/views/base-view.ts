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

import * as _ from 'lodash';
import * as chalk from 'chalk';
import * as TextField from '../fields/text-field';
import {BaseField} from '../fields/base-field';
import {FIELD_TYPES__MAP} from './field-types-map';

/**
 * @extends BaseView
 */
export default class extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: '[field-name={{name}}]',
        });
    }

    public async getField(name: string, type?: string): Promise<BaseField> {

        let selector = '';
        try {
            selector = this.$('field', {name});
            let field = this.fields[name];

            if (field) {

                console.log(`field ${name}, template ${field.constructor.name}`);

                let isCorrectTemplate = await this.driver.isVisible(
                    field.$('field.selector')
                );
                if (isCorrectTemplate) {
                    return field;
                }
            }

            let fieldTypeAttr = await this.driver.getAttribute(
                selector,
                'field-type'
            );

            type = _.isArray(fieldTypeAttr) ? fieldTypeAttr[0] : fieldTypeAttr;

            field = await this.createField(name, type);

            console.log(`field ${name}, template ${field.constructor.name}`);

            return field;
        } catch (err) {
            throw new Error(
                `Field '${name}' is missing on ${this.constructor.name}
                via selector: '${chalk.yellow(selector)}'`
            );
        }
    }

    protected async createField(name: string, type: string): Promise<BaseField> {

        let options = {
            module: this.module,
            layout: this.parent,
            name,
            type,
        };

        let field: BaseField;

        let Clazz = FIELD_TYPES__MAP[type];

        if (!Clazz) {
            console.error(
                `Type: ${type} of field is not recognized. Falling back to 'TextField' field type`
            );
            Clazz = TextField;
        }

        let fieldClass: any;

        // we check a selector for each field template (Edit, Disabled ...) to find the needed one
        for (fieldClass of _.values(Clazz)) {
            field = this.createComponent<BaseField>(fieldClass, options);
            let selector = field.$('field.selector');

            // check for existense because field can have placeholder that overlaps the element pointed by selector
            let isCorrectFieldInstance = await this.driver.isElementExist(
                selector
            );
            if (isCorrectFieldInstance) {
                this.fields[name] = field;
                return field;
            }
        }

        throw new Error(`Failed to create '${name}' field of '${type}' type`);
    }

    public async getAttribute(sel: string, attr: string): Promise<string> {
        let templateName: string | string[] = await this.driver.getAttribute(sel, attr);
        if (_.isArray(templateName)) {
            throw new Error(`Please verify selector: ${sel}. It matched ${templateName.length} elements`);
        }

        if (_.isNull(templateName)) {
            throw new Error(`Please verify selector: ${sel}. It didn't match an element`);
        }

        return templateName.toString();
    }

    public async clickField(fieldName: string): Promise<void> {
        let field: BaseField = await this.getField(fieldName);
        await field.click();
    }

    public async clickButton(buttonName) {
        await this.driver.click(this.$(`buttons.${buttonName.toLowerCase()}`));
    }
}
