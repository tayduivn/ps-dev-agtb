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

        // empty user to originally set/reset the context param
        var initUser = {'id': '','full_name': '','first_name': '','last_name': ''};
        this.context.set( "selectedUser" , initUser);

        // Hide the whole tree view until jsTree gets it's data back
        // if current user has reportees, then we'll show the tree view
        $('.view-tree').hide();
    },

    /**
     * Start the rendering of the JS Tree
     */
    render:function () {

        // only let this render once.  since if there is more than one view on a layout it renders twice
        if (this.rendered) return;

        app.view.View.prototype.render.call(this);

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
                        if( ( data instanceof Array && data[1].children.length > 0 ) ||
                            ( data.hasOwnProperty('children') && data.children.length > 0 ) ) {
                            $('.view-tree').show();
                        }
                    }
                }
            },
            "types" : {
                "types" : {
                    "types" : {
                        "parent_link" : {

                        },
                        "manager" : {

                        },
                        "my_opportunities" : {

                        },
                        "rep" : {

                        }
                    }
                }
            }
        }).on("select_node.jstree", function(event, data){
                var jsData = data.inst.get_json();
                var nodeType = jsData[0].attr.rel;
                var userData = jsData[0].metadata;

                // ONLY do something if this is a different user
                // My Opportunities will have the same user id as the current user, so allow that as well
                if( nodeType == "my_opportunities" || self.context.attributes.selectedUser.id != userData.id ) {

                    // if user clicked on a "My Opportunities" node
                    // set this flag true
                    if( nodeType == "my_opportunities") {
                        self.context.set("showManagerOpportunities", true);
                    } else if( self.context.attributes.showManagerOpportunities ) {
                        // resets back to false if user clicks  non-My-Opportunities node
                        // and showManagerOpportunities was previously set to true
                        // so we dont unnecessarily change the context when we dont need to
                        self.context.set("showManagerOpportunities", false);
                    }

                    var selectedUser = {
                        'id'            : userData.id,
                        'full_name'     : userData.full_name,
                        'first_name'    : userData.first_name,
                        'last_name'     : userData.last_name
                    };

                    // update context with selected user
                    self.context.set( "selectedUser" , selectedUser);

                    // Handle different types of nodes
                    switch(nodeType) {
                        case "parent_link":
                            var returnParentSuffix = '';

                            // selectedUser has the metadata of the currently-selected-user's parent
                            // if the currently-Logged-in user is going to be fetched next, do not return a parent link
                            // as we just want the currently-Logged-in user and who they report to but no parent link.
                            // Go no further up the tree
                            if(app.user.get('id') != selectedUser.id)  {
                                returnParentSuffix = '/1'
                            }

                            self.currentTreeUrl = self.reporteesEndpoint + selectedUser.id + returnParentSuffix;
                            self.rendered = false;
                            self.render();
                            break;

                        case "manager":
                            // add /1 to end of url to hit the reporteesWithParent endpoint
                            self.currentTreeUrl = self.reporteesEndpoint + selectedUser.id + '/1';
                            self.rendered = false;
                            self.render();
                            break;

                        case "my_opportunities":
                            // Anything special for My Opportunities
                            break;

                        case "rep":
                            // Anything special for the Rep
                            break;
                    }
                }
            });

        this.rendered = true;
    }
})