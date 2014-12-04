({
    extendsFrom: 'RecordlistView',
    contextEvents: {
        "list:reassign:fire": "reassignCase"
    },

    reassignCase: function (model) {
        //console.log('Unattended Cases: ', model);
        //open drawer
        var self=this;
        app.drawer.open({
            layout: 'reassignCases',
            context: {
                module: 'pmse_Inbox',
                parent: this.context,
                cas_id: model.get('cas_id'),
                unattended: true
            }

        },
            function(variables) {
                if(variables==='saving'){
                    self.reloadList();
                }
            });
    },
    reloadList: function() {
        var self = this;
        self.context.reloadData({
            recursive:false,
            error:function(error){
                console.log(error);
            }
        });
    }
})