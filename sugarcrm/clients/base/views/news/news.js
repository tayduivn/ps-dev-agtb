/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ('Company') that Company is bound by
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
    plugins: ['Dashlet'],
    initialize: function (o) {
        app.view.View.prototype.initialize.call(this, o);
        if (this.context.parent.parent && this.context.parent.parent.get('model')) {
            this.targetModel = this.context.parent.parent.get('model');
            this.targetModel.on('change', this.loadData, this);
        }
    },

    loadData: function (options) {
        var name, limit;

        if (_.isUndefined(this.targetModel)) {
            return;
        }

        name = this.targetModel.get('account_name') ||
            this.targetModel.get('name') ||
            this.targetModel.get('full_name');
        if (!name) {
            return;
        }

        limit = parseInt(this.model.get('limit') || 8, 10);
        $.ajax({
            url: 'https://ajax.googleapis.com/ajax/services/search/news?v=1.0&q=' +
                name.toLowerCase() + '&rsz=' + limit,
            dataType: 'jsonp',
            success: function (data) {
                if (this.disposed) {
                    return;
                }
                _.extend(this, data);
                this.render();
            },
            context: this,
            complete: options ? options.complete : null
        });
    },

    _dispose: function () {
        if (this.targetModel) {
            this.targetModel.off('change', this.loadData, this);
        }
        app.view.View.prototype._dispose.call(this);
    }
})
