({
    /**
     * The Current Active Panel Index
     */
    activePanel: 0,

    /**
     * This is a 0 base number, so 0 equals 1 panel
     */
    totalPanels: null,

    /**
     * All the admin panels
     */
    panels : [],

    events: {
        'click [name=close_button]' : 'close',
        'click [name=ok_button]' : 'ok',
        'click [name=next_button]' : 'next',
        'click [name=previous_button]' : 'previous'
    },

    /**
     * Handle the close button click, don't save anything
     *
     * //TODO: maybe add a dialog if something changed and you are closing the model
     * @param evt
     */
    close: function(evt) {
        this.layout.context.trigger("modal:close");
    },

    /**
     * Handle the OK button click.
     * @param evt
     */
    ok: function(evt) {
        this.model.save();
        this.layout.context.trigger("modal:close");
    },


    /**
     * Handle the next button click.  It's only handled if the button doesn't have the disabled class on it.
     * @param evt
     */
    next: function(evt) {
        // only fire if the target is not disabled
        if($(evt.target).hasClass('disabled') == false) {
            this.handleDirectionSwitch('next');
        }
    },

    /**
     * Handle the previous button click.  It's only handled if the button doesn't have the disabled class on it.
     * @param evt
     */
    previous: function(evt) {
        // only fire if the target is not disabled
        if($(evt.target).hasClass('disabled') == false) {
            this.handleDirectionSwitch('previous');
        }
    },

    /**
     * Implement the wizard functionality for the previous and next buttons
     * @param way   Which way to move the wizard.
     */
    handleDirectionSwitch: function(way) {
        // we need to know how many panels there are
        if(!_.isNumber(self.totalPanels)) {
            this.panels = this.$el.parent().find('div.modal-content');
            this.totalPanels = this.panels.length-1;
        }

        var nextPanel = -1;

        // find the next panel
        if(way == "next") {
            nextPanel = this.activePanel+1;
        } else {
            nextPanel = this.activePanel-1;
        }

        // make sure that the next panel is not under 0 or over the total amount of panels
        if(nextPanel < 0) {
            nextPanel = 0;
        } else if(nextPanel > this.totalPanels) {
            // make sure we never go over the max panels
            nextPanel = this.totalPanels;
        }

        if(nextPanel > 0 && nextPanel != this.totalPanels) {
            this.$el.find('[name=next_button]').removeClass('disabled');
            this.$el.find('[name=previous_button]').removeClass('disabled');
        } else if(nextPanel == 0) {
            this.$el.find('[name=previous_button]').addClass('disabled');
        } else if(nextPanel == this.totalPanels) {
            this.$el.find('[name=next_button]').addClass('disabled')
        }

        // hide the current active panel
        $(this.panels[this.activePanel]).toggleClass('show hide');
        // show the new panel
        $(this.panels[nextPanel]).toggleClass('show hide');

        this.activePanel = nextPanel;
    }
})