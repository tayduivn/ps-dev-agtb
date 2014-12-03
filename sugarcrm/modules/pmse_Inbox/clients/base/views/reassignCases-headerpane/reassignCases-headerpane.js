({
    extendsFrom: "HeaderpaneView",

    events: {
        "click [name=done_button]":   "_done",
        "click [name=cancel_button]": "_cancel"
    },

     /**
      * The user clicked the Done button so trigger an event to add selected recipients from the address book to the
      * target field and then close the drawer.
      *
      * @private
      */
     _done: function() {
         var userReassign = new Object();
         userReassign.flow_data=window.globalObjectUser;
         var attributes = userReassign;
//         console.log('OBJ',attributes);
         window.globalObjectUser=new Object();
         app.alert.show('saving', {level: 'process', title: 'LBL_SAVING', autoclose: false});
         url = app.api.buildURL('pmse_Inbox', 'reassignFlows', null, null);
         app.api.call('update', url, attributes, {
             success: function (data) {
                 app.alert.dismiss('saving');
                 app.drawer.close('saving');
             },
             error: function (err) {
             }
         });
     },

    /**
     * Close the drawer.
     *
     * @private
     */
    _cancel: function() {
        window.globalObjectUser=new Object();
        app.drawer.close();
    }
})
