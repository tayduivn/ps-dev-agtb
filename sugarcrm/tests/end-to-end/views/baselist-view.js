/*
 Represents a base list that is a parent for home, recents, list views.
 */

var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView,
    _ = require('lodash');

/**
 * @class SugarCukes.BaseListView
 * @extends Cukes.BaseView
 */
class BaseListView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            noRecordsFound: ".no-records-found",
                pullToRefreshStartPosition: '.items article:nth-child(1)',

                contextMenu: {
                $: ".menu-container.on",
                    edit: ".edit-item i",
                    delete: ".delete-item i",
                    unlink: '.unlink-item i',
                    grip: ".grip",
                    follow: ".icon-check-circle-o",
                    unfollow: ".icon-check-circle"
            }
        };

        this.listItems = [];
    }

    getListItem (conditions, options) {
        var keys = _.keys(conditions);

        if (keys.length !== 1 || !_.includes(['id', 'index', 'current'], keys[0])) {
            return null;
        } else {
            var listItems = _.filter(this.listItems, conditions),
                listViewItem = listItems.length ? listItems[0] : null;

            if (!listViewItem) {
                listViewItem = this.createListItem(conditions, options);
            }
            return listViewItem;
        }
    }

    createListItem (conditions, options) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        var listViewItem = this.createComponent('ListItemView', {
            id: conditions.id,
            module: this.module,
            fieldsMeta: options && options.fields || this.fieldsMeta
        });

        this.listItems.push(listViewItem);

        return listViewItem;
    }
}

module.exports = BaseListView;
