({
//    extendsFrom :'RecordView',
//    className: 'settings',

    events: {
        //'click .sugar-cube': 'spinCube'
    },
    initialize: function(options) {
        app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        var self=this;
                var url = app.api.buildURL('pmse_Inbox', 'settings', null, options.params);
                app.api.call('READ', url, options.attributes, {
                    success: function (data) {
                        self.model.set(data);
                        app.alert.dismiss('upload');
                    }
                });
        this._super('initialize', [options]);
    }
})