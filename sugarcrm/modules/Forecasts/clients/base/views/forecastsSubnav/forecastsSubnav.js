/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ForecastsSubnavView
 * @alias SUGAR.App.layout.ForecastsSubnavView
 * @extends View.View
 */
({
    /**
     * Stores the full name of the user to display in subnav template
     */
    fullName:'',
    jsTree:{},
    reporteesEndpoint:'',
    currentTreeUrl:'',
    currentRootId:'',

    // Target id of the modal in SidecarView.tpl to pull up when settings cog icon is clicked
    modalTargetId: 'forecastSubnavSettingsModal',

    events: {
        "click #forecastSettings" : "handleForecastSettingsClick"
    },

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // grab current app user model locally
        var currentUser = app.user;

        this.fullName = currentUser.get('full_name');

        // Tree init
        this.reporteesEndpoint = app.api.serverUrl + "/Forecasts/reportees/";
        this.currentTreeUrl = this.reporteesEndpoint + currentUser.get('id');
        this.currentRootId = currentUser.get('id');
    },

    bindDataChange: function() {
        var self = this;
        app.view.View.prototype.bindDataChange.call(this);

        this.context.forecasts.on('change:selectedUser', this.checkRender, this);
    },

    /**
     * Handler function when user clicks the settings button in forecasts
     * @param e Event object
     */
    handleForecastSettingsClick: function(e) {
        this.layout._showConfigModal(false);
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

        this.fullName = selectedUser.full_name;

        // handle the case for user clicking MyOpportunities first
        if(selectedUser.showOpps) {
            var nodeId = 'jstree_node_myopps_' + selectedUser.id;
            this.selectJSTreeNode(nodeId)

            this.render();
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
        } else {
            // just update the title
            var hb = Handlebars.compile("{{str_format key module args}}");
            var text = hb({'key' : "LBL_FORECAST_TITLE", 'module' : 'Forecasts', 'args' : this.fullName});
            this.$el.find('h1').html(text);
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

    /**
     * Renders JSTree
     * @param ctx
     * @param options
     * @protected
     */
    _renderHtml : function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        var self = this;
        var treeData;

        app.api.call('read', self.currentTreeUrl, null, {
            success:function (data) {
                // make sure we're using an array
                // if the data coming from the endpoint is an array with one element
                // it gets converted to a JS object in the process of getting here
                if (!jQuery.isArray(data)) {
                    data = [ data ];
                }

                treeData = data;

                self.jsTree = $(".jstree-sugar").jstree({
                    "plugins":["json_data", "ui", "crrm", "types", "themes"],
                    "json_data":{
                        "data":treeData
                    },
                    "ui":{
                        // when the tree re-renders, initially select the root node
                        "initially_select":[ 'jstree_node_' + self.context.forecasts.get('selectedUser').id ]
                    },
                    "types":{
                        "types":{
                            "types":{
                                "parent_link":{},
                                "manager":{},
                                "my_opportunities":{},
                                "rep":{},
                                "root":{}
                            }
                        }
                    }
                }).on("select_node.jstree", function (event, data) {
                        var jsData = data.inst.get_json();
                        var nodeType = jsData[0].attr.rel;
                        var userData = jsData[0].metadata;
                        var contextUser = self.context.forecasts.get("selectedUser");

                        var showOpps = false;

                        // if user clicked on a "My Opportunities" node
                        // set this flag true
                        if (nodeType == "my_opportunities" || nodeType == "rep") {
                            showOpps = true
                        }

                        var selectedUser = {
                            'id':userData.id,
                            'full_name':userData.full_name,
                            'first_name':userData.first_name,
                            'last_name':userData.last_name,
                            'isManager':(nodeType == 'rep') ? false : true,
                            'showOpps':showOpps
                        };

                        // update context with selected user which will trigger checkRender
                        self.context.forecasts.set("selectedUser", selectedUser);
                    });

                if (treeData) {
                    var showTree = false;
                    var rootId = -1;

                    if (treeData.length == 1) {
                        // this case appears when "Parent" is not present

                        rootId = treeData[0].metadata.id;

                        if (treeData[0].children.length > 0) {
                            showTree = true;
                        }
                    }
                    else if (treeData.length == 2) {
                        // this case appears with a "Parent" link label in the return set

                        // always show tree if we have a Parent link in the set
                        // only happens when you've clicked another Manager link
                        showTree = true;

                        // treeData[0] is the Parent link, treeData[1] is our root user node
                        rootId = treeData[1].metadata.id;
                    }

                    self.currentRootId = rootId;
                    if (showTree) {
                        $('#forecastsTree').show();
                    }
                }

                // add proper class onto the tree
                $("#people").addClass("jstree-sugar");
            }
        });
    }

})