var Cukes = require('@sugarcrm/seedbed'),
    BaseLayout = Cukes.BaseLayout;

/**
 * Represents List page layout.
 *
 * @class SugarCukes.ListLayout
 * @extends Cukes.BaseLayout
 */
class ListLayout extends BaseLayout {

    constructor(options) {
        super(options);

        this.type = 'list';

        // TODO:
        // we are lucky that activitystream-layout has display: none,
        // but that isn't always true for list views,
        // since some might have only the class hide which does the same.
        this.selectors = {
            $: '.main-pane:not([style*="display: none"])'
        };

        this.addView('FilterView', 'FilterView', { module: options.module });
        this.addView('ListView', 'ListView', { module: options.module, default: true });
    }
}

module.exports = ListLayout;
