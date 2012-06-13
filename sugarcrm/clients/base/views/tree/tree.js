/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.TreeView
 * @alias SUGAR.App.layout.TreeView
 * @extends View.View
 */
({

    rendered:false,

    jsTree:{},

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);

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
            "plugins":["themes", "json_data", "ui", "crrm"],
            "json_data" : {
                "ajax" : {
                    "url" : app.api.serverUrl + "/Forecasts/reportees/" + app.user.get('id'),
                    "success" : function(data)  {
                        // IF this user has children (is a manager/has reportees) then show the tree view
                        if( data.children.length > 0 )  {
                            $('.view-tree').show();
                        }
                    }
                }
            }
        }).on("select_node.jstree", function(event, data){
                jsData = data.inst.get_json();

                var selectedUser = {
                    'id' : jsData[0].metadata.id,
                    'full_name' : jsData[0].metadata.full_name,
                    'first_name' : jsData[0].metadata.first_name,
                    'last_name' : jsData[0].metadata.last_name
                };

                console.log("Tree Node " + selectedUser.full_name + " selected");

                // update context with selected user
                self.context.set( "selectedUser" , selectedUser);
            });

        this.rendered = true;
    }
})