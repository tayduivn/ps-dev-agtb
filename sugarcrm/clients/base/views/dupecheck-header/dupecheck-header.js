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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.DupecheckHeaderView
 * @alias SUGAR.App.view.views.BaseDupecheckHeaderView
 * @extends View.View
 */
({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('dupecheck:collection:reset', this.updateCount, this);
     },

    updateCount: function() {
        var translatedString = app.lang.get(
            'LBL_DUPLICATES_FOUND',
            this.module,
            {'duplicateCount': this.context.get('collection').length}
        );
        this.$('span.duplicate_count').text(translatedString);
    }
})
