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

    /***
     * Handles if data changes on the context.forecasts.tree model (and when tree first initializes)
     * Tree is not handled like other components on Forecasts as it uses a 3rdParty lib (jstree)
     * to handle it's data model. The event handler is added here only because bindDataChange is
     * where other event handlers are added in other models and it fires once during initialization
     */
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
        // handle the case for user clicking MyOpportunities first
        if(selectedUser.showOpps) {
            var nodeId = 'jstree_node_myopps_' + selectedUser.id;
            this.selectJSTreeNode(nodeId)

            // check before render if we're trying to re-render tree with a fresh root user
            // otherwise do not re-render tree
            // also make sure we're not re-rendering tree for a rep
        } else if(this.currentRootId != selectedUser.id) {
            if(selectedUser.isManager) {
                // if user is a manager we'll be re-rendering the tree
                // no need to re-render the tree if not a manager because the dataset
                // stays the same

                this.currentRootId = selectedUser.id;
                this.currentTreeUrl = this.reporteesEndpoint + selectedUser.id;
                this.rendered = false;
                this.render();
            } else {
                // user is not a manager but if this event is coming from the worksheets
                // we need to "select" the user on the tree to show they're selected

                // create node ID
                var nodeId = 'jstree_node_' + selectedUser.id;

                // select node only if it is not the already selected node
                if(this.jsTree.jstree('get_selected').attr('id') != nodeId)  {
                    this.selectJSTreeNode(nodeId)
                }
            }
        }
    },

    /***
     * Function that handles deselecting any selected nodes then selects the nodeId
     *
     * @param nodeId {String} the node id starting with "jstree_node_"
     */
    selectJSTreeNode: function(nodeId) {
        // jstree kept trying to hold on to the root node css staying selected when
        // user clicked a user's name from the worksheet, so explicitly causing a deselection
        this.jsTree.jstree('deselect_all');

        this.jsTree.jstree('select_node', '#' + nodeId);
    },

    /**
     * Renders JSTree
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
                                self.currentRootId = data[1].metadata.id;
                            } else {
                                self.currentRootId = data.metadata.id;
                            }
                        }
                    }
                }
            },
            "ui" : {
                // when the tree re-renders, initially select the root node
                "initially_select" : [ 'jstree_node_' + self.context.forecasts.get('selectedUser').id ]
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
                var contextUser = self.context.forecasts.get("selectedUser");

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