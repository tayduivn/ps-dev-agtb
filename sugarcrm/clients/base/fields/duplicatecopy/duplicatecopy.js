({
    'events' : {
        'click input.copy' : 'copy'
    },
    copy: function() {
        var primary_record = this.context.get("primaryRecord");
        primary_record.set(this.name, this.model.get(this.name));
    },
    bindDomChange: function() {}
})
