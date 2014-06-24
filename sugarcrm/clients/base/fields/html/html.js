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
 * @class View.Fields.Base.HtmlField
 * @alias SUGAR.App.view.fields.BaseHtmlField
 * @extends View.Field
 */
({
    fieldSelector: '.htmlareafield', //iframe selector

    /**
     * {@inheritdoc}
     *
     * The html area is always a readonly field.
     * (see htmleditable for an editable html field)
     */
    initialize: function(options) {
        options.def.readonly = true;
        app.view.Field.prototype.initialize.call(this, options);
    },

    /**
     * {@inheritdoc}
     *
     * Set the name of the field on the iframe as well as the contents
     *
     * @private
     */
    _render: function() {
        app.view.Field.prototype._render.call(this);

        this._getFieldElement().attr('name', this.name);
        this.setViewContent();
    },

    /**
     * Sets read only html content in the iframe
     */
    setViewContent: function(){
        var value = this.value || this.def.default_value;
        var field = this._getFieldElement();
        if(field && field.get(0) && !_.isEmpty(field.get(0).contentDocument)) {
            if(field.contents().find('body').length > 0){
                field.contents().find('body').html(value);
            }
        }
    },

    /**
     * Finds iframe element in the field template
     *
     * @return {HTMLElement} element from field template
     * @private
     */
    _getFieldElement: function() {
        return this.$el.find(this.fieldSelector);
    }

})
