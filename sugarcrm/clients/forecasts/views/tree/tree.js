/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.TreeView
 * @alias SUGAR.App.layout.TreeView
 * @extends View.View
 */
({

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
            this.context.forecasts.on("change:selectedUser", this.checkRender, this);
        }
    },

    /***
     * Function to give a final check before rendering to see if we really need to render
     * Any time the selectedUser changes on context.forecasts we run through this function to
     * see if we should render the tree again
     *
     * @param context
     * @param selectedUser {Object} the current selectedUser on the context
     */
    checkRender: function(context, selectedUser) {
        // check before render if we're trying to re-render tree with a fresh root user
        // otherwise do not re-render tree
        // also make sure we're not re-rendering tree for a rep
        if(this.currentRootId != selectedUser.id && selectedUser.isManager)  {
            this.currentRootId = selectedUser.id;
            this.currentTreeUrl = this.reporteesEndpoint + selectedUser.id;
            this.render();
        }
    },
    /**
     * Render JSTree
     */
    _render:function () {

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

                var showOpps = false;

                // if user clicked on a "My Opportunities" node
                // set this flag true
                if(nodeType == "my_opportunities") {
                    showOpps = true
                }

                var selectedUser = {
                    'id'            : userData.id,
                    'full_name'     : userData.full_name,
                    'first_name'    : userData.first_name,
                    'last_name'     : userData.last_name,
                    'isManager'     : (nodeType == 'rep') ? false : true,
                    'showOpps'      : showOpps
                };

                // update context with selected user which will trigger checkRender
                self.context.forecasts.set("selectedUser" , selectedUser);
            });

    }
})