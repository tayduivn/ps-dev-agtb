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
var Cukes = require('@sugarcrm/seedbed'),
    BaseField = Cukes.BaseField;

/**
 * @class SugarCukes.TextField
 * @extends Cukes.BaseField
 */
class TextField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = {
            field: {
                edit: {
                    selector: "input"
                },
                detail: {
                    selector: "div"
                },
                preview: {
                    selector: "div"
                },
                list: {
                    selector: 'div'
                },
                readonly: {
                    selector: '.readonly-item'
                }
            }
        }

    }

}

module.exports = TextField;
