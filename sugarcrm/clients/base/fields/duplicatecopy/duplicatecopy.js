({
    'events' : {
        'click input.copy' : 'copy'
    },
    copy: function() {
        var primary_record = this.view.primaryRecord;
        if (!primary_record) {
            return;
        }
        primary_record.set(this.name, this.model.get(this.name));
    },
    bindDomChange: function() {}
})
