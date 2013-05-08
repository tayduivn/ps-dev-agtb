/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'HeaderpaneView',

    initialize: function(options) {
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);

        this.on('render', function() {
            this.getField('save_draft_button').setDisabled();
            this.getField('commit_button').setDisabled();
        }, this);
    },

    bindDataChange: function() {
        this.context.on('change:selectedUser', function(model, changed) {
            //if(_.isUndefined(model.previous('selectedUser')) || model.previous('selectedUser').id !== changed.id) {
                this.title = changed.full_name;
                if (!this.disposed) this.render();
            //}
        }, this);

        this.context.on('forecast:worksheet:dirty', function(worksheet_type) {
            console.log('forecast worksheet dirty: ', worksheet_type);
            this.getField('save_draft_button').setDisabled(false);
            this.getField('commit_button').setDisabled(false);
        }, this);

        app.view.views.HeaderpaneView.prototype.bindDataChange.call(this);
    },

    _renderHtml: function() {
        var user = this.context.get('selectedUser') || app.user.toJSON();
        this.title = this.title || user.full_name;

        app.view.View.prototype._renderHtml.call(this);
    }
})
