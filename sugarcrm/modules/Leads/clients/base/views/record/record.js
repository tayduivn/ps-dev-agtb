({
    extendsFrom: 'RecordView',

    initialize: function(options) {
        _.bindAll(this);
        this.events['click .lead-convert'] = 'showConvert';

        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    render: function() {
        app.view.views.RecordView.prototype.render.call(this);
        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            //this.setLeadButtonStates();
        }, this);
    },

    showConvert: function() {
        var layout = app.view.createLayout({
            context: this.context,
            module: this.context.get("module"),
            name: "convert",
            layout: this.layout
        });

        $('.headerpane').parent().before(layout.$el);
        layout.render();
    },

    /**
     * Change the behavior of buttons depending on the state that they should be in
     */
    setLeadButtonStates: function() {
        var convertButton = this.$('.lead-convert'),
            convertedState = this.model.get('converted') == '1' ? true : false;

        if(convertedState) {
            convertButton.toggleClass('hide', true);
        }
    }

})
