/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.TreeView
 * @alias SUGAR.App.layout.TreeView
 * @extends View.View
 */
({

    rendered:false,

    jsTree:{},

    reporteesEndpoint:'',

    currentTreeUrl:'',

    currentRootId:'',

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);

        this.reporteesEndpoint = app.api.serverUrl + "/Forecasts/reportees/";
        this.currentTreeUrl = this.reporteesEndpoint + app.user.get('id');
        this.currentRootId = app.user.get('id');

        // Hide the whole tree view until jsTree gets it's data back
        // if current user has reportees, then we'll show the tree view
        $('.view-tree').hide();
    },

    bindDataChange: function() {
        if(this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", this.render, this);
        }
    },

    /**
     * Start the rendering of the JS Tree
     */
    _render:function () {

        // only let this render once.  since if there is more than one view on a layout it renders twice
//        if (this.rendered) return;

        app.view.View.prototype._render.call(this);

        var self = this;

        this.jsTree = $(".jstree-sugar").jstree({
            "plugins":["json_data", "ui", "crrm", "types", "themes"],
            "json_data" : {
                "ajax" : {
                    "url" : self.currentTreeUrl,
                    "success" : function(data)  {
                        // IF this user has children (is a manager/has reportees) then show the tree view
                        // 1st if line is true if Parent link has been returned
                        // 2nd if line is true if no Parent link has been returned
                        if( (data instanceof Array && data[1].children.length > 0) ||
                            (data.hasOwnProperty('children') && data.children.length > 0)) {
                            $('.view-tree').show();

                            //get id of current root user
                            if(data instanceof Array) {
                                self.rootUserId = data[1].metadata.id;
                            } else {
                                self.rootUserId = data.metadata.id;
                            }
                        }
                    }
                }
            },
            "types" : {
                "types" : {
                    "types" : {
                        "parent_link" : {},
                        "manager" : {},
                        "my_opportunities" : {},
                        "rep" : {},
                        "root" : {}
                    }
                }
            }
        }).on("select_node.jstree", function(event, data){
                var jsData = data.inst.get_json();
                var nodeType = jsData[0].attr.rel;
                var userData = jsData[0].metadata;

                // ONLY do something if this is a different user
                // My Opportunities will have the same user id as the current user, so allow that as well
                if(nodeType == "my_opportunities" || self.context.forecasts.get("selectedUser").id != userData.id ) {

                    // if user clicked on a "My Opportunities" node
                    // set this flag true
                    if(nodeType == "my_opportunities") {
                        self.context.forecasts.set("showManagerOpportunities", true);
                    } else if(self.context.forecasts.get("showManagerOpportunities")) {
                        // resets back to false if user clicks  non-My-Opportunities node
                        // and showManagerOpportunities was previously set to true
                        // so we dont unnecessarily change the context when we dont need to
                        self.context.forecasts.set("showManagerOpportunities", false);
                    }

                    var selectedUser = {
                        'id'            : userData.id,
                        'full_name'     : userData.full_name,
                        'first_name'    : userData.first_name,
                        'last_name'     : userData.last_name,
                        'isManager'     : (nodeType == 'root' || nodeType == 'manager') ? true : false
                    };

                    // update context with selected user
                    self.context.forecasts.set( "selectedUser" , selectedUser);

                    // Handle different types of nodes
                    switch(nodeType) {
                        case "root":
                            // If user clicks on the root node, we do not need to re-render tree
                            break;

                        case "parent_link":
                        case "manager":
                            // final check before render, no need to re-render if we've clicked back on the root
                            // the second level of users (managers underneath the logged-in/root manager) have
                            // the "manager" rel but they may be at the top ("root") of the tree
                            // and we dont need to re-render in that case either
                            if(self.currentRootId != selectedUser.id)  {
                                self.currentRootId = selectedUser.id;
                                self.currentTreeUrl = self.reporteesEndpoint + selectedUser.id;
                                self.rendered = false;
                                self.render();
                            }
                            break;

                        case "my_opportunities":
                            // Anything special for My Opportunities
                            break;

                        case "rep":
                            // Anything special for the Rep
                            break;
                    }
                } else if(self.context.forecasts.get("showManagerOpportunities")) {
                    // Case for user has selected My Opportunities, then clicks back on the manager user
                    self.context.forecasts.set("showManagerOpportunities", false)
                }
            });

        this.rendered = true;
    }
})