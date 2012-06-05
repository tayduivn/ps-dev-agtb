/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.TreeView
 * @alias SUGAR.App.layout.TreeView
 * @extends View.View
 */
({

    rendered:false,

    primary_user:'',

    reportees:'',

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

        $(".jst").jstree({
            "plugins":["themes", "json_data", "ui", "crrm"],
            "themes":{
                "theme":"classic",
                "dots":false,
                "icons":true
            },
            "json_data" : {
                "ajax" : {
                    "url" : app.api.serverUrl + "/Forecasts/reportees/" + app.user.get('id'),
                }
            }
        }).on("select_node.jstree", this.treeNodeSelect);

        this.rendered = true;
    },

    /**
     * Event Handler for when a jsTree node is selected
     * @param event
     * @param data
     */
    treeNodeSelect:function (event, data) {
        jsData = data.inst.get_json();

        console.log("Tree Node " + jsData[0].metadata.id + " selected -- Event triggering temporarily disabled");
        // TEMPORARY triggering on app.events
        //this.dispatch.trigger('treeview:node_select', {'selected' : jsData[0].metadata.model, 'json' : jsData});
    }
})