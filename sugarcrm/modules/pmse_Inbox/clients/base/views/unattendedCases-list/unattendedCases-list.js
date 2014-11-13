({
    extendsFrom: 'RecordlistView',
    contextEvents: {
        "list:reassign:fire": "reassignCase"
    },

    reassignCase: function (model) {
        //console.log('Unattended Cases: ', model);
        //open drawer
        app.drawer.open({
            layout: 'reassignCases',
            context: {
                module: 'pmse_Inbox',
                parent: this.context,
                cas_id: model.get('cas_id'),
                unattended: true
            }
        });
    }
})