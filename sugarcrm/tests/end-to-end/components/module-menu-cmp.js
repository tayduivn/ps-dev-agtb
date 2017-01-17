var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView;

/**
 * Represents Modules Top Menu.
 *
 * @class SugarCukes.ModuleMenuCmp
 * @extends Cukes.BaseView
 */
class ModuleMenuCmp extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            moduleList: {
                $: '.module-list',
                item: {
                    $: 'a[href="#{{itemName}}"].module-name',
                    iconStatus: ".icon.status-{{status}}"
                },
                listItem: {
                    $: 'a.module-list-link[href="#{{itemName}}"]'
                },
                currentItem: 'li.current a[href="#{{itemName}}"]',
                moreIcon: ".more button"
            }
        };
    }

    /**
     * Get Module Item Selector
     *
     * @param itemName
     * @param dropdown
     * @returns {*|String}
     */
    getModuleButtonSelector(itemName, dropdown) {
        return (dropdown) ?
            this.$('moduleList.listItem', {itemName: itemName}) :
            this.$('moduleList.item', {itemName: itemName});
    }

    /**
     * Click on Module Item in Modules menu
     *
     * @param link
     * @param dropdown
     * @returns {*}
     */
    clickItem (link, dropdown) {
        var itemSelector = this.getModuleButtonSelector(link, dropdown);

        return seedbed.client
            .waitForVisible(itemSelector)
            .click(itemSelector);

    }

    /**
     * Is Module Menu Item visible
     * Modules Mega menu contain visible modules and other modules are hidden in dropdown menu
     *
     * @param itemName
     * @param callback
     */
    isVisible(itemName, callback) {
        seedbed.client.isVisible(this.getModuleButtonSelector(itemName)).then((isVisible) => {
                callback(isVisible);
        });
    }

    /**
     * Click on Modules Mege Menu dropdown to show all modules
     */
    showAllModules() {
        return seedbed.client
            .click(this.$('moduleList.moreIcon'));
    }
};

module.exports = ModuleMenuCmp;
