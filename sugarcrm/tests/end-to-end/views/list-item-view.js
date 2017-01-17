"use strict";

var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView,
    async = require('async');

/**
 * @class SugarCukes.ListItemView
 * @extends Cukes.BaseView
 */
class ListItemView extends BaseView{

    constructor(options) {

        super(options);

        this.selectors = {
            $ : 'tr[name*="{{id}}"]',
                listItem: {
                listItemName : 'a[href*="{{id}}"]',
                    listItemGrip : ".menu-container-grip",
                    preview : ".actions span.list a"
            },
            buttons: {
                addRow: '.addBtn'
            }
        };

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Click on list view item list element (name in most cases)
     *
     * @param callback
     * @returns {*}
     */
    clickListItem(callback) {

        var selector = this.$('listItem.listItemName', {id: this.id}),
            rowSelector = this.$();

        var chain = seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);

        if (callback) {
            chain.call(function(){
                callback();
            });
        }

        return chain;
    }

    /**
     * Click preview button on list view element
     *
     * @param callback
     * @returns {*}
     */
    clickPreviewButtonOnItem(callback) {
        var selector = this.$('listItem.preview', {id: this.id}),
            rowSelector = this.$();

        var chain = seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);

        if (callback) {
            chain.call(function(){
                callback();
            });
        }

        return chain;
    }

    clickWithinListItem(elName, callback) {
        var chain = seedbed.client.click(this.$('listItem.' + elName.toLowerCase(), {id: this.id}));

        if (callback) {
            chain.call(callback);
        }

        return chain;
    }

    clickCheckBox(callback) {
        return this.clickWithinListItem('checkbox', callback);
    }

    /**
     * Populates view fields with the values from data
     *
     * @param data
     * @param callback
     */
    setFieldsValue (data, callback) {

        let tasks = [];
        let fields = this.fields;

        data.iterate((providedValue, providedFieldName) => {
            let field = fields[providedFieldName];
            if (field) {
                tasks.push((c) => {
                    field.setValue(providedValue, c);
                });
            }
        });

        async.series(tasks, (error) => {
            callback(error);
        });
    }

    /**
     * Clicks on add row button
     *
     * @param callback
     * @returns {*}
     */
    clickAddRowButton(callback) {
        return seedbed.client.click(this.$('buttons.addRow')).waitForApp().call(callback);
    }

}

module.exports = ListItemView;
