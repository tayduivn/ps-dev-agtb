({
    events: {
        'click .add-dashlet' : 'layoutClicked'
    },
    layoutClicked: function(evt) {
        var columns = $(evt.currentTarget).data('value');
        this.layout.addRow(columns);
        console.log(this.layout.layout);
    }
})
