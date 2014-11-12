({
    extendsFrom: 'RecordlistView',

    contextEvents: {
        "list:process:fire": "showCase"
    },

    showCase: function (model) {
        //console.log('Child: ', child);
        var url = model.module + '/' + model.id + '/layout/show-case/' + model.get('flow_id');
        App.router.navigate(url , {trigger: true, replace: true });
    }
})