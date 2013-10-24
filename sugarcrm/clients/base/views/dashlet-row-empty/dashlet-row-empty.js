/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    events: {
        'click .add-dashlet' : 'layoutClicked',
        'click .add-row.empty' : 'addClicked'
    },
    originalTemplate: null,
    columnOptions: [],
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.model = this.layout.context.get("model");

        this.model.on("setMode", this.setMode, this);
        this.originalTemplate = this.template;
        this.setMode(this.model.mode);
        this.columnOptions = [];
        _.times(this.model.maxRowColumns, function(index) {
            var n = index + 1;
            this.columnOptions.push({
                index: n,
                label: (n > 1) ?
                    app.lang.get('LBL_DASHBOARD_ADD_' + n + '_COLUMNS', this.module) :
                    app.lang.get('LBL_DASHBOARD_ADD_' + n + '_COLUMN', this.module)
            });
        }, this);
    },
    addClicked: function(evt) {
        var self = this;
        this._addRowTimer = setTimeout(function() {
            self.addRow(1);
        }, 100);
    },
    layoutClicked: function(evt) {
        var columns = $(evt.currentTarget).data('value');
        var addRow = _.bind(this.addRow, this);
        _.delay(addRow, 0, columns);
    },
    addRow: function(columns) {
        this.layout.addRow(columns);
        if(this._addRowTimer) {
            clearTimeout(this._addRowTimer);
        }
    },
    setMode: function(model) {
        if(model === 'edit') {
            this.template = this.originalTemplate;
        } else {
            this.template = app.template.empty;
        }
        this.render();
    },
    _dispose: function() {
        this.model.off("setMode", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
