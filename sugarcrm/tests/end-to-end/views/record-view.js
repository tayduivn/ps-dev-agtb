var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView,
    _ = require('lodash'),
    async = require('async');

/**
 * Represents Record view.
 *
 * @class SugarCukes.RecordView
 * @extends Cukes.BaseView
 */
class RecordView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            $: ".record",

            title: ".title",
            arrow: ".icon-chevron-right",

            listingItem: {
                $: "a[href$='{{module}}']",
                count: ".records-count",
                label: ".label-module-sm.label-{{label}}"
            },
            listingItemCreateLink: "a[href$='{{module}}/create']"
        };

    }

    setFieldsValue (data, callback) {

        var tasks = [];

        _.each(this.fields, function (field, name) {
            if (data[name] || (data[name] === '')) {
                tasks.push(function (c) {
                    field.setValue(data[name], c);
                });
            }
        });

        async.series(tasks, function () {
            callback();
        });
    }
}

module.exports = RecordView;
