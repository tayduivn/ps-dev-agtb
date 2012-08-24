({
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
        if(this.name == 'name') {
            var route = this.def.route;
            this.record_id = this.model.get(route.recordID);
            this.module = route.module;
            this.action = route.action;
            this.linkText = this.model.get(this.name);
            // setting the viewName allows us to explicitly set the template to use
            this.options.viewName = 'link';
        }
        app.view.Field.prototype._render.call(this);
        return this;
    }
})
