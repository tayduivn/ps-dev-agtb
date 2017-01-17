var Cukes = require('@sugarcrm/seedbed'),
    BaseField = Cukes.BaseField;

/**
 * @class SugarCukes.NameField
 * @extends Cukes.BaseField
 */
class NameField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = {
            field: {
                list: {
                    selector: 'a'
                },
                detail: {
                    selector: "div"
                },
                preview: {
                    selector: "div"
                }
            }
        };

    };
}

module.exports = NameField;
