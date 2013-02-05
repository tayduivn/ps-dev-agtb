({
    'events' : {
        'click input.copy' : 'copy'
    },
    copy: function() {
        var primary_model = this.context.get("primary_model");
        primary_model.set(this.name, this.model.get(this.name));
    },
    bindDomChange: function() {}
})
