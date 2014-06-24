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
 * @class View.Fields.Base.Cases.CreateArticleActionField
 * @alias SUGAR.App.view.fields.BaseCasesCreateArticleActionField
 * @extends View.Fields.Base.RowactionField
 */
({
    extendsFrom: 'RowactionField',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.def.route = 'bwc/index.php?module=KBDocuments&action=EditView&case_id=' + this.model.id;
    },

    /**
     * @inheritDoc
     */
    _loadTemplate: function() {
        this.type = 'rowaction';
        this._super('_loadTemplate');
        this.type = this.def.type;
    }
})
