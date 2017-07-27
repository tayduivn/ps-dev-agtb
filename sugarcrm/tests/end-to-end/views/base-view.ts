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
import * as TextField from '../fields/text-field';
import * as TextareaField from '../fields/textarea-field';
import * as NameField from '../fields/name-field';
import * as EnumField from '../fields/enum-field';
import * as IntField from '../fields/int-field';
import * as FloatField from '../fields/float-field';
import * as DateField from '../fields/date-field';
import * as RelateField from '../fields/relate-field';
import * as CopyField from '../fields/copy-field';
import * as CurrencyField from '../fields/currency-field';
import * as UrlField from '../fields/url-field';
import * as FullnameField from '../fields/fullname-field';
import * as TagField from '../fields/tag-field';
import {BaseField} from '../fields/base-field';

const classify = name => _.upperFirst(_.camelCase(name));

export const TEMPLATES = {
    EDIT: 'edit'
};

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

        let selector = this.$('field', {name});
        let templateName: string;
        try {
            templateName = await this.getAttribute(selector, 'field-tpl-name');
        } catch (e) {
            templateName = 'edit';
        }

        let field = this.fields[name];
        templateName = this.getFieldTemplateName(templateName);

        if (field && field.templateName === templateName) {
            return field;
        }

        let fieldTypeAttr = await seedbed.client.getAttribute(selector, 'field-type');

        type = _.isArray(fieldTypeAttr) ? fieldTypeAttr[0] : fieldTypeAttr;

        field = this.createField(name, type, templateName);

        return field;
    }

    protected createField(name: string, type: string, templateName?: string): BaseField {

        let options = {
            module: this.module,
            layout: this.parent,
            name,
            type,
            templateName,
        };
        let field: BaseField;
        let Clazz;

        switch (type) {
            case 'name':
                Clazz = NameField[templateName];
                break;
            case 'url':
                Clazz = UrlField[templateName];
                break;
            case 'fullname':
                Clazz = FullnameField[templateName];
                break;
            case 'phone':
            case 'text':
                Clazz = TextField[templateName];
                break;
            case 'textarea':
                Clazz = TextareaField[templateName];
                break;
            case 'enum':
                Clazz = EnumField[templateName];
                break;
            case 'int':
                Clazz = IntField[templateName];
                break;
            case 'date':
                Clazz = DateField[templateName];
                break;
            case 'float':
                Clazz = FloatField[templateName];
                break;
            case 'relate':
                Clazz = RelateField[templateName];
                break;
            case 'checkbox':
            case 'copy':
                Clazz = CopyField[templateName];
                break;
            case 'currency':
                Clazz = CurrencyField[templateName];
                break;
            case 'tag':
                Clazz = TagField[templateName];
                break;
            default:
                throw new Error(`Field type '${type}' is not found`);
        }

        if (!Clazz) {
            console.error(`Type: ${type} of field is not recognized. Falling back to base field type: ${templateName} or Edit`);
            Clazz = BaseField;
        }

        field = this.createComponent<BaseField>(Clazz, options);

        this.fields[name] = field;

        return field;
    }

    private getFieldTemplateName(templateName: string = TEMPLATES.EDIT): string {
        return classify(templateName);
    }

    public async getAttribute(sel: string, attr: string): Promise<string> {
        let templateName: string | string[] = await seedbed.client.getAttribute(sel, attr);
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

}
