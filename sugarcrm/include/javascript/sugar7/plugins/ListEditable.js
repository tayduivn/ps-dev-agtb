/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        /**
         * ListEditable plugin is for fields that use a list-edit template instead of the standard edit
         * during inline editing on list views
         */
        app.plugins.register('ListEditable', ['field'], {
            _loadTemplate: function() {
                //Invoke the original method first
                Object.getPrototypeOf(this)._loadTemplate.call(this);
                if (this.view.action === 'list' && _.contains(['edit', 'disabled'], this.tplName)) {
                    var tplName = 'list-' + this.tplName;
                    this.template = app.template.getField(this.type, tplName, this.module, this.tplName) ||
                        app.template.empty;
                    this.tplName = tplName;
                }
            }
        });
    })
})(SUGAR.App);
