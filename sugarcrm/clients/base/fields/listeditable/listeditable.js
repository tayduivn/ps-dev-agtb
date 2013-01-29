({
    _loadTemplate: function() {
        app.view.Field.prototype._loadTemplate.call(this);
        if(this.view.action === 'list' && _.indexOf(['edit', 'disabled'], this.tplName) >= 0 ) {
            var tplName = 'list-' + this.tplName;
            this.template = app.template.getField(this.type, tplName, this.module, this.tplName) ||
                            app.template.empty;
            this.tplName = tplName;
        }
    }
})
