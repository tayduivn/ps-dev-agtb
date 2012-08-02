({

    /**
     * Template to use when displaying clickable name links to a module + action + recordID
     */
    recordTemplate : _.template('<a href="index.php?module=<%= module %>&action=<%= action %>&record=<%= record_id %>"><%= linkText %></a>'),

    /**
     * Template to use when displaying simple values
     */
    valueTemplate :_.template('<%= value %>'),

    /**
     * Holds the record id for passing into recordTemplate
     */
    record_id: '',

    /**
     * Holds the module for passing into recordTemplate
     */
    module: '',

    /**
     * Holds the action for passing into recordTemplate
     */
    action: '',

    /**
     * Holds the link text for passing into recordTemplate
     */
    linkText: '',

    _render:function() {
        var tpl = this.valueTemplate;

        if(this.name == 'name') {
            tpl = this.recordTemplate;
            var route = this.def.route;
            this.record_id = this.model.get(route.recordID);
            this.module = route.module;
            this.action = route.action;
            this.linkText = this.model.get(this.name);
        }

        this.template = tpl;

        if (this.model instanceof Backbone.Model) {
            /**
             * Model property value.
             * @property {String}
             * @member View.Field
             */
            this.value = this.format(this.model.has(this.name) ? this.model.get(this.name) : "");
        }

        this.$el.html(this.template(this));
        this.unbindDom();
        this.bindDomChange();
        return this;
    }
})
