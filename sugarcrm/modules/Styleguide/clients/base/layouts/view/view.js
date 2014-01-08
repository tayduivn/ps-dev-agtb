/*********************************************************************************
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
 ********************************************************************************/
({
    plugins: ['Prettify'],
    data: [],
    page_name: '',
    page_doc: {},

    _placeComponent: function(component) {
        this.$('#styleguide').append(component.$el);
    },

    initialize: function(options) {
        this.page_name = this.options.context.get('page_name').split('_')[1];
        app.view.Layout.prototype.initialize.call(this, options);
    },

    _render: function() {
        app.view.Layout.prototype._render.call(this);

        var page_content = app.template.getView( this.page_name + '.' + this.page_name + '_doc', 'Styleguide');

        this.page_doc = app.view.createView({
                context: this.context,
                name: this.page_name,
                module: 'Styleguide',
                layout: this,
                model: this.model,
                readonly: true
            });

        this.$('#styleguide').append('<div class="container-fluid"></div>');
        this.$('#styleguide .container-fluid').append(page_content(this));
        this.$('#exampleView').append(this.page_doc.el);

        this.page_doc.render();
    }
})
