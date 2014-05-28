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
 * Sticky Rowaction does not disappear when user does not have access.
 *
 * It becomes disabled instead. This allows us to keep things lined up nicely
 * in Subpanel.
 *
 * @class View.Fields.Base.StickyRowactionField
 * @alias SUGAR.App.view.fields.BaseStickyRowactionField
 * @extends View.Fields.Base.RowactionField
 */
({
    extendsFrom: 'RowactionField',
    /**
     * @param options
     * @override
     */
    initialize: function(options) {
        this._super("initialize", [options]);
        this.type = 'rowaction';  //TODO Hack that loads rowaction templates.  I hope to remove this when SP-966 is fixed.
    },
    /**
     * We always render StickyRowactions and instead set disable class when the user has no access
     * @private
     */
    _render: function() {
        if(this.isDisabled()){
            if(_.isUndefined(this.def.css_class) || this.def.css_class.indexOf('disabled') === -1){
                this.def.css_class = (this.def.css_class) ? this.def.css_class + " disabled" : "disabled";
            }
            //Remove event listeners on this action since it is disabled
            this.undelegateEvents();
        }
        this._super("_render");
    },
    /**
     * Essentially the replacement of 'hasAccess' method for implementors of StickyRowactionField.
     * Used to determine if this rowaction should be rendered in a disabled state because the user lacks permission, etc.
     *
     * This is a default implementation disables when the user lacks access.
     * @return {boolean}
     */
    isDisabled: function(){
        return !this._super("hasAccess");
    },
    /**
     * Forces StickyRowaction to be rendered and visible in Actiondropdowns.
     * @returns {boolean} TRUE always
     */
    hasAccess: function(){
        return true;
    }

})
