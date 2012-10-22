({
    /**
     * The Current Active Panel Index
     */
    activePanel:-1,

    /**
     * This is a 0 base number, so 0 equals 1 panel
     */
    totalPanels:null,

    /**
     * All the admin panels
     */
    panels:[],

    navTabs:[],

    breadCrumbLabels: [],

    events:{
        'click [name=close_button]':'close',
        'click [name=save_button]':'save',
        'click [name=next_button]':'next',
        'click [name=previous_button]':'previous',
        'click .breadcrumb.two li a':'breadcrumb'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.breadCrumbLabels = this.layout.getBreadCrumbLabels();
    },

    /**
     * Handle the close button click, don't save anything
     *
     * //TODO: maybe add a dialog if something changed and you are closing the model
     * @param evt
     */
    close:function (evt) {
        this.layout.context.trigger("modal:close");
    },

    /**
     * Handle the Save button click.
     * @param evt
     */
    save:function (evt) {
        // If button is disabled, do nothing
        if(!$(evt.target).hasClass('disabled')) {
            this.model.save();
            this.layout.context.trigger("modal:close");
        }
    },


    /**
     * Handle the next button click.  It's only handled if the button doesn't have the disabled class on it.
     * @param evt
     */
    next:function (evt) {
        this.handleWizardStartScreen(evt);
        // only fire if the target is not disabled
        if ($(evt.target).hasClass('disabled') == false) {
            this.handleDirectionSwitch('next');
        }
    },

    /**
     * Handle the previous button click.  It's only handled if the button doesn't have the disabled class on it.
     * @param evt
     */
    previous:function (evt) {
        // only fire if the target is not disabled
        this.handleWizardStartScreen(evt);
        if ($(evt.target).hasClass('disabled') == false) {
            this.handleDirectionSwitch('previous');
        }
    },

    breadcrumb:function (evt) {
        // ignore the click if the crumb is already active
        if ($(evt.target).parent().is(".disabled") == true) {
            // figure out which crumb was checked
            var clickedCrumb = 0;
            _.each(this.navTabs, function (tab, index) {
                // figure out which tab has the a that was clicked
                if ($(tab).has(evt.toElement).length) {
                    clickedCrumb = index;
                }
            });

            if (clickedCrumb != this.activePanel) {
                this.switchPanel(clickedCrumb);
                this.switchNavigationTab(clickedCrumb);

                this.activePanel = clickedCrumb;
            }
        }
    },

    handleWizardStartScreen: function(evt) {
        // see if the modal wizard start page is show, if it, hide it
        var elParent = this.$el.parent();
        if(elParent.find('.modal-wizard-start').hasClass('show')) {
            elParent.find('.modal-wizard-start').toggleClass('hide show');
            elParent.find('.modal-navigation').toggleClass('hide show');
        }
    },

    /**
     * Implement the wizard functionality for the previous and next buttons
     * @param way   Which way to move the wizard.
     */
    handleDirectionSwitch:function (way) {
        // we need to know how many panels there are
        if (!_.isNumber(this.totalPanels)) {
            this.panels = this.$el.parent().find('div.modal-content').not('.modal-wizard-start');;
            this.totalPanels = this.panels.length - 1;

            this.navTabs = this.$el.parent().find('div.modal-navigation li');
        }

        var nextPanel = -1;

        // find the next panel
        if (way == "next") {
            nextPanel = this.activePanel + 1;
        } else {
            nextPanel = this.activePanel - 1;
        }

        // make sure that the next panel is not under 0 or over the total amount of panels
        if (nextPanel < 0) {
            nextPanel = 0;
        } else if (nextPanel > this.totalPanels) {
            // make sure we never go over the max panels
            nextPanel = this.totalPanels;
        }

        this.switchPanel(nextPanel);
        this.switchNavigationTab(nextPanel);

        this.activePanel = nextPanel;
    },

    /**
     * handle the switching of the panels
     * @param nextPanel
     */
    switchPanel:function (nextPanel) {
        // adjust the buttons accordingly
        if (nextPanel > 0 && nextPanel != this.totalPanels) {
            this.$el.find('[name=next_button]').removeClass('disabled');
            this.$el.find('[name=previous_button]').removeClass('disabled');
        } else if (nextPanel == 0) {
            this.$el.find('[name=previous_button]').addClass('disabled');
        } else if (nextPanel == this.totalPanels) {
            this.$el.find('[name=next_button]').addClass('disabled')
        }

        // hide the current active panel
        $(this.panels[this.activePanel]).toggleClass('show hide');
        // show the new panel
        $(this.panels[nextPanel]).toggleClass('show hide');
    },

    /**
     * handle the switching of the navigation crumbs
     * @param next
     */
    switchNavigationTab:function (next) {
        $(this.navTabs[this.activePanel]).toggleClass('active disabled');
        $(this.navTabs[next]).toggleClass('active disabled');
    }
})