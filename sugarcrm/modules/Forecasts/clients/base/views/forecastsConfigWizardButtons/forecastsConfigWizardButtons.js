/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Events Triggered
 *
 * modal:close
 *      on: layout.context
 *      by: close()
 *      when: the user closes the modal
 *
 * modal:close
 *      on: layout.context
 *      by: save()
 *      when: the new config model has saved successfully
 */
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

    // hash table to get the class for the breadcrumb styling
    breadCrumbClassTable: {
        2: 'two',
        3: 'three',
        4: 'four',
        5: 'five'
    },

    // the holder for the breadcrumb class used to style the chevrons
    breadCrumbClass: "four",

    events:{
        'click [name=close_button]':'close',
        'click [name=done_button]':'save',
        'click [name=next_button]':'next',
        'click [name=start_button]':'start',
        'click [name=previous_button]':'previous',
        'click .breadcrumb.two li a':'breadcrumb'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.breadCrumbLabels = this.layout.getBreadCrumbLabels();
        this.breadCrumbClass = _.has(this.breadCrumbClassTable, this.breadCrumbLabels.length) ?
            this.breadCrumbClassTable[this.breadCrumbLabels.length] :
            this.breadCrumbClass;
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

    start : function(evt) {
        // hide the start button
        $(evt.target).addClass('hide');

        this.$el.parents('div.modal-body').addClass('with-nav');

        this.$el.find('a[name=next_button]').toggleClass('hide show');
        this.$el.find('a[name=previous_button]').toggleClass('hide show');

        this.next(evt);
    },

    /**
     * Handle the Save button click.
     * @param evt
     */
    save:function (evt) {
        // If button is disabled, do nothing
        if(!$(evt.target).hasClass('disabled')) {
            // make it so you can click save again.
            $(evt.target).addClass('disabled');
            var self = this;
            
            this.model.set({
                is_setup:true,
                show_forecasts_commit_warnings: true
            });

            // push this model back to the main config model
            this.context.get('model').set({config: this.model.toJSON()});

            this.context.set({saveClicked: true }, {silent:true});

            this.context.get('model').save({}, {
                success: function() {
                    var url = app.api.buildURL("Forecasts/init");
                    app.api.call('GET', url, null, {success: function(forecastData) {
                        // get default selections for filter and ranges
                        app.defaultSelections = forecastData.defaultSelections;
                        app.initData = forecastData.initData;
                        // only trigger modal close after save api call has returned
                        self.layout.context.trigger("modal:close");
                    }});
                }
            });
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
        evt.preventDefault();
        // ignore the click if the crumb is already active
        if ($(evt.target).parent().is(".active,.disabled") == false) {
            // get the index of the clicked crumb
            var clickedCrumb = $(evt.target).data('index');

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
            if(this.$el.find('[name=done_button]').hasClass('show')) {
                this.$el.find('[name=done_button]').toggleClass('hide show');
            }
            if(this.$el.find('[name=next_button ]').hasClass('hide')) {
                this.$el.find('[name=next_button]').toggleClass('hide show');
            }
        } else if (nextPanel == 0) {
            this.$el.find('[name=previous_button]').addClass('disabled');
        } else if (nextPanel == this.totalPanels) {
            this.$el.find('[name=next_button]').toggleClass('hide show');
            this.$el.find('[name=done_button]').toggleClass('hide show');
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
        $(this.navTabs[next]).removeClass('disabled');

        $(this.navTabs[this.activePanel]).toggleClass('active').addClass('highlight');
        $(this.navTabs[next]).toggleClass('active');
    }
})
