({
    extendsFrom: 'DupecheckListView',

    addRowActions: function(panel, options) {
        panel = app.view.views.BaselistView.prototype.addRowActions.call(this, panel, options);

        if (options.meta.showPreview === true) {
            panel.fields = panel.fields.concat({
                type: 'rowaction',
                css_class: 'btn',
                tooltip: 'LBL_PREVIEW',
                event: 'list:preview:fire',
                icon: 'icon-eye-open'
            });
        }

        return panel;
    }
})