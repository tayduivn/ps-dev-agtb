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
