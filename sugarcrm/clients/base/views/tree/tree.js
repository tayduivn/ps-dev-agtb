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
        // IF the above line is the only thing in this function,
        // we can remove the whole function
        // Leaving function here in case we need to add more to initialize FOR NOW!
    },

    /**
     * Start the rendering of the JS Tree
     */
    render:function () {

        // only let this render once.  since if there is more than one view on a layout it renders twice
        if (this.rendered) return;

        app.view.View.prototype.render.call(this);

        var self = this;

        this.jsTree = $(".jst").jstree({
            "plugins":["themes", "json_data", "ui", "crrm"],
            "json_data" : {
                "ajax" : {
                    "url" : app.api.serverUrl + "/Forecasts/reportees/" + app.user.get('id'),
                    "success" : function(data)  {
                        // IF this user has no children (is not a manager) then hide the tree
                        if( data.children.length == 0 )  {
                            self.jsTree.hide();
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