({
    extendsFrom: 'BaselistView',

    addMultiSelectionAction: function(meta) {
        meta = app.view.views.BaselistView.prototype.addMultiSelectionAction.call(this, meta);
        if(meta.favorite) {
            _.each(meta.panels, function(panel){
                panel.fields[0].fields.push('favorite');
            });
        }
        return meta;
    }
})