({
    events: {
        'click [data-dashletaction]': 'actionClicked'
    },
    extendsFrom: 'ButtonField',
    actionClicked: function (evt) {
        if(this.preventClick(evt) === false) {
            return;
        }
        var action = $(evt.currentTarget).data("dashletaction");
        this.view.trigger("dashletaction", action, evt);
    }
})
