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
    /**
     * @class View.SelectionListView
     * @alias SUGAR.App.view.views.SelectionListView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    initialize: function (options) {
        options.meta = options.meta || {};
        options.meta.selection = { type: 'single', label: ' ' };
        app.view.invoke(this, 'view', 'flex-list', 'initialize', {args:[options]});
        this.context.on("change:selection_model", this._selectModel, this);
    },
    _selectModel: function () {
        var model = this.context.get("selection_model");
        if (model) {
            var attributes = {
                id: model.id,
                value: model.get('name')
            };
            _.each(model.attributes, function (value, field) {
                if (app.acl.hasAccessToModel('view', model, field)) {
                    attributes[field] = attributes[field] || model.get(field);
                }
            }, this);
            this.context.unset("selection_model", {silent: true});
            this.context.off("change:selection_model", null, this);
            app.drawer.close(attributes);
        }
    }
})
