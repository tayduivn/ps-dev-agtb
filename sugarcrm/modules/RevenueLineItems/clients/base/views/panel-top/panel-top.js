({
    extendsFrom: 'PanelTopView',
    
    /**
     * {@inheritdoc}
     */
    initialize: function(options){
        app.view.invokeParent(this, {type: 'view', name: 'panel-top', method: 'initialize', args: [options]});
        if (this.parentModule == "Accounts") {
            this.meta.buttons = _.filter(this.meta.buttons, function(item){
                if (item.type != "actiondropdown") {
                    return true;
                }
                return false;
            });
        }
    },
    /**
     * {@inheritdoc}
     */
    createRelatedClicked: function(event) {
        // close RLI warning alert
        app.alert.dismiss('opp-rli-create');

        app.view.invokeParent(this, {type: 'view', name: 'panel-top', method: 'createRelatedClicked', args: [event]});
    }
})