({
    extendsFrom: 'DupecheckListView',
    additionalTableClasses: 'duplicates-selectedit',

    addActions:function () {
        if (this.actionsAdded) return;
        app.view.invokeParent(this, {type: 'view', name: 'dupecheck-list', method: 'addActions'});

        var firstRightColumn = this.rightColumns[0];
        if (firstRightColumn && _.isArray(firstRightColumn.fields)) {
            //Prepend Select and Edit action
            firstRightColumn.fields.unshift({
                type: 'rowaction',
                label: 'LBL_LISTVIEW_SELECT_AND_EDIT',
                css_class: 'btn btn-invisible btn-link',
                event: 'list:dupecheck-list-select-edit:fire'
            });
            this.rightColumns[0] = firstRightColumn;
        }
        this.actionsAdded = true;
    }
})
