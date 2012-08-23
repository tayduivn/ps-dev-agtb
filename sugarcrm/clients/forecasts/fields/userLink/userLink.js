({
    /**
     * Attach a click event to <a class="worksheetManagerLink"> field
     */
    events : { 'click a.worksheetManagerLink' : 'linkClicked' },

    /**
     * Holds the user_id for passing into userTemplate
     */
    uid: '',

    /**
     * Contains CSS to hide the popover icon since render gets called after the logic checks
     */
    popoverVisibilityStyle: 'style="display:none;"',

    /**
     * Title for the popover
     */
    popoverTitleName: '',

    _render:function() {
        var self = this;
        if(this.name == 'name') {
            var commitDateStr = this.context.forecasts.committed.models[0].get('date_entered');
            var commitDate = app.forecasts.utils.parseDBDate(commitDateStr);

            var fieldDateStr = this.model.get('date_entered');
            var fieldDate = app.forecasts.utils.parseDBDate(fieldDateStr)

            // if fieldDate is newer than the forecast commitDate, then we want to show the field
            var showFieldAlert = (fieldDate.getTime() > commitDate.getTime());

            // if this is not an Opps link, blank the style string to allow the popover icon to be visible
            if((this.model.get('user_id') != this.context.forecasts.get('selectedUser').id) && showFieldAlert) {
                this.popoverVisibilityStyle = '';

                // add event listener so we can hide popover if manager clicks commit button
                this.context.forecasts.on('change:commitForecastFlag', this.hideIcon, this);
            }
            this.uid = this.model.get('user_id');
            this.popoverTitleName = this.model.get('name');

            // setting the viewName allows us to explicitly set the template to use
            this.options.viewName = 'userLink';
        }
        app.view.Field.prototype._render.call(this);
        $('[rel="clickoverBottom"]').clickover({
            onShown : this.popoverIconClicked,
            placement: 'bottom',
            timePeriod : this.model.get('timeperiod_id'), //need timeperiod in the manager model,
            userId : this.uid,
            dateEntered : commitDate,
            template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content" id="popover-content-' + this.uid + '"><p></p></div></div></div>'
        });
        return this;
    },

    /**
     * Handle a user link being clicked
     * @param event
     */
    linkClicked: function(event) {
        var uid = $(event.target).data('uid');
        var self = this;
        var selectedUser = {
            id: '',
            full_name: '',
            first_name: '',
            last_name: '',
            isManager: false,
            showOpps: this.model.get("show_opps")
        };

        var options = {
            dataType: 'json',
            context: selectedUser,
            success: function(data) {
                selectedUser.id = data.id;
                selectedUser.full_name = data.full_name;
                selectedUser.first_name = data.first_name;
                selectedUser.last_name = data.last_name;
                selectedUser.isManager = data.isManager;

                self.context.forecasts.set({selectedUser : selectedUser})
            }
        };

        myURL = app.api.buildURL('Forecasts', 'user/' + uid);
        app.api.call('read', myURL, null, options);
    },

    /**
     * Event handler when popoverIcon is clicked,
     * @param event
     * @return {*}
     */
    popoverIconClicked: function(event) {
        var options = {
            timeperiod_id : this.options.timePeriod,
            user_id : this.options.userId
        }
        var myURL = app.api.buildURL('Forecasts', 'committed', null, options);

        return app.api.call('read',
            myURL,
            null,
            {
                success : function(data) {
                    var userId = '',
                        commitDate = new Date(this.options.dateEntered),
                        newestModel = {},
                        oldestModel = {},
                        len = data.length;

                    // using for because you can't break out of _.each
                    for(var i = 0; i < len; i++) {
                        var entry = data[i];
                        userId = entry.user_id;

                        //if first model, put it in newestModel
                        if(i == 0) {
                            newestModel = new Backbone.Model(entry);
                            continue;
                        }

                        var entryDate = app.forecasts.utils.parseDBDate(entry.date_entered);

                        // check for the first model equal to or past the forecast commit date
                        // we want the last commit just before the whole forecast was committed
                        if(entryDate <= commitDate) {
                            oldestModel = new Backbone.Model(entry);
                            break;
                        }
                    }

                    // Begin creating output HTML
                    var outputHTML = "<article>",
                        output = {};

                    if(!_.isEmpty(oldestModel) && !_.isEmpty(newestModel)) {
                        output = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                        outputHTML += output.text + "<br><date>" + output.text2 + "</date></article>";
                    } else {
                        outputHTML += "No Data</article>";
                    }

                    $('[id="popover-content-' + userId + '"]').html(outputHTML);
                }
            },
            { context : this }
        );
    },

    /**
     * Hides popover icon and removes event listener
     */
    hideIcon: function(){
        // hide icons
        $('.pull-right').hide();

        // remove event listener
        this.context.forecasts.off('change:commitForecastFlag', this.hideIcon);
    }
})