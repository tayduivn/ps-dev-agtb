({
    events: {
        'click [data-dashletaction]': 'actionClicked'
    },
    extendsFrom: 'ButtonField',
    /**
     * Trigger the function which is in the dashlet view
     *
     * @param {Window.Event}
     */
    actionClicked: function (evt) {
        if(this.preventClick(evt) === false) {
            return;
        }
        var action = $(evt.currentTarget).data("dashletaction");
        this.view.trigger("dashletaction", action, evt);
    }
})
