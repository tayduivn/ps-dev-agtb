/*
 Represents List view PageObject.
 */

var BaseListView = require('./baselist-view');

/**
 * @class SugarCukes.ListView
 * @extends SugarCukes.BaseListView
 */
class ListView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = {
            $ : '.flex-list-view:not([style*="display: none"])',
                errorBox: ".error-box",
                unlink: ".icon-minus-circle",
                showMoreBottom: ".show-more-bottom-btn",
                showMoreTop: ".show-more-top-btn",
                list : {
                allRows : 'tr[name*="{{module}}"]',
                    row : 'tr[name*="{{module}}"]:nth-child({{index}})'
            }
        }

    }

        /**
     * Get List View table rows counts
     *
     * @param callback
     */
    getRowsCount (callback) {
        seedbed.client.elements(this.$('list.allRows', { module : this.module })).then(result => {
            callback(null, result.value.length);
        }).catch(err => {
            callback("Failed to query elements: " + err, null);
        });
    }

    /**
     * Returns id of random record on ListView
     *
     * @param callback
     */
    getRandomRecordId (callback) {
        this.getRowsCount(function(err, count) {
            if (err) {
                callback('Failed to get ListView records: ' + err);
            } else if (count === 0) {
                callback('Failed to get random record: ListView is empty');
            } else {
                var randomFromTotal = Math.floor((Math.random() * count));
                this.getRecordIDOnView(randomFromTotal, callback);
            }
        }.bind(this));
    }

    /**
     * Returns id of index'th record on the list view
     *
     * @param index
     * @param callback
     */
    getRecordIDOnView (index, callback) {
        var selector = this.$('list.row', {index: index + 1, module : this.module});
        seedbed.client.waitForVisible(selector).getAttribute(selector, 'name').then(name => {
            callback(null, name.replace(this.module + '_', ''));
        });
    }

    waitForErrorBox (callback) {
        var self = this;
        seedbed.client.waitFor(this.$('errorBox')).then((err) => {
            if (err) {
                callback("Failed to see error box: " + err, null);
            } else {
                self.getErrorBoxMessage(function(err, message) {
                    callback(null, message);
                });
            }
        });
    }

    getErrorBoxMessage (callback) {
        return seedbed.client.getText(this.$('errorBox')).then((result) => {
                callback(null, result);
        }).catch((err) => {
            callback("Failed to retrieve error box message: " + err, null);
        });
    }
}

module.exports = ListView;
