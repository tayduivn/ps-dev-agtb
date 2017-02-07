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
    BaseLayout = Cukes.BaseLayout,
    _ = require('lodash');

/**
 * Represents a Detail/Record page layout.
 *
 * @class SugarCukes.RecordLayout
 * @extends Cukes.BaseLayout
 */
class RecordLayout extends BaseLayout {

    constructor(options) {

        super(options);

        this.selectors = {
            $: '.main-pane'
        };

        this.type = 'record';

        this.addView('RecordView', 'RecordView', {
            module: options.module,
            fieldsMeta: this._getRecordFieldsMeta(),
            default: true
        });

        this.addView('HeaderView', 'HeaderView', {
            module: options.module,
            fieldsMeta: this._getHeaderFieldsMeta()
        });

        this.addView('ToggleShowView', 'ToggleShowView', {
            module: options.module
        });
    }

    /**
     * Returns array of fields for HeaderView
     *
     * @returns {Array}
     * @private
     */
    _getHeaderFieldsMeta() {
        return this.mergeFieldPanelsMeta(_.filter(
            seedbed.meta.modules[this.module].views[this.type].meta.panels,
            {header : true}
        ));
    }

    /**
     * Returns array of fields for RecordView
     *
     * @returns {Array}
     * @private
     */
    _getRecordFieldsMeta() {
        return this.mergeFieldPanelsMeta(_.reject(
            seedbed.meta.modules[this.module].views[this.type].meta.panels,
            function(panel) {return panel.header === true;}
        ));
    }

    /**
     * Searches child views for the field and returns the first one that the method came across
     *
     * @param fieldName
     */
    getField(fieldName) {
        var view = _.find(this.components, function (view) {
            return !!view.$$(fieldName);
        });

        return view ? view.$$(fieldName) : null;
    }
}

module.exports = RecordLayout;
