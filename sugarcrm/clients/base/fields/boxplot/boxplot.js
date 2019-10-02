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
({
    /**
     * list of events to listen for
     * @type {Object}
     */
    'events': {
        'click': 'updateCss'
    },

    overall_worst_case: 0.0,

    overall_best_case: 0.0,

    overall_case_difference: 0.0,

    worst_case: 0.0,

    best_case: 0.0,

    likely: 0.0,

    difference: 0.0,

    box_start: 0.0,

    box_end: 0.0,

    box_width: 0.0,

    likely_percent: 0.0,

    caret_pos: 0.0,

    amount_pos: 0.0,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.overall_worst_case = this._worstCaseComparator(this.collection.models);
        this.overall_best_case = this._bestCaseComparator(this.collection.models);
    },

    /**
     * @inheritdoc
     *
     * calculates the width of the box-plot and the worst_case and the best_case end whiskers,
     * by calculating the worst_case and best_case value for a collection.
     * It also calculates the median line for the likely value position to be placed in the box plot.
     * @return {Object} this
     * @private
     */
    _render: function() {
        this.overall_case_difference = parseFloat(this.overall_best_case - this.overall_worst_case).toFixed(2);
        this.worst_case = parseFloat(this.model.attributes.worst_case).toFixed(2);
        this.likely =  parseFloat(this.model.attributes.amount).toFixed(2);
        this.best_case = parseFloat(this.model.attributes.best_case).toFixed(2);
        this.difference = parseFloat(this.best_case - this.worst_case).toFixed(2);
        this.likely_percent = parseFloat(this.likely / this.overall_best_case).toFixed(2) * 100;
        this.likely_percent = this.likely_percent === 100 ? this.likely_percent - 1 : this.likely_percent;
        this.caret_pos =  this.likely_percent - 0.8;
        this.amount_pos = this.likely_percent - 6;
        this.box_start = parseFloat(this.worst_case / this.overall_best_case).toFixed(2) * 100;
        this.box_end = parseFloat(this.best_case / this.overall_best_case).toFixed(2) * 100;
        this.box_end = this.box_end === 100 ? this.box_end - 1 : this.box_end;
        this.box_width = parseFloat(this.box_end - this.box_start).toFixed(2);
        this._super('_render');
        return this;
    },

    /**
     * _worstCaseComparator() accepts modelArray for the collection
     * and gets the overall worst_case value.
     * @return {Number} minimum worst_case
     * @private
     */
    _worstCaseComparator: function(modelArray) {
        var min = Number.MAX_VALUE;
        modelArray.forEach(function(e) {
            if (min > parseFloat(e.attributes.worst_case)) {
                min = parseFloat(e.attributes.worst_case);
            }
        });
        return min.toFixed(2);
    },

    /**
     * _bestCaseComparator() accepts modelArray for the collection
     * and gets the overall best_case value.
     * @return {Number} minimum best_case
     * @private
     */
    _bestCaseComparator: function(modelArray) {
        var MAX = 0;
        modelArray.forEach(function(e) {
            if (MAX < parseFloat(e.attributes.best_case)) {
                MAX = parseFloat(e.attributes.best_case);
            }
        });
        return MAX.toFixed(2);
    },

    /**
     * update dropdown css to active state
     */
    updateCss: function() {
        $('div.select2-drop.select2-drop-active').width('auto');
    },
})
