(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.TreeView
     * @alias SUGAR.App.layout.TreeView
     * @extends View.View
     */
    app.view.views.TreeView = app.view.View.extend({


        rendered : false,

        primary_user : '',

        reportees : '',

        /**
         * Start the rendering of the JS Tree
         */
        render : function (){

            // only let this render once.  since if there is more than one view on a layout it renders twice
            if(this.rendered) return;
            app.view.View.prototype.render.call(this);

            app.events.register("treeview:node_select", this);

            this.primary_user = SUGAR.App.data.createBean('Users', { id: 'seed_jim_id'});
            this.primary_user.on('change', this.postUserFetch, this);
            this.primary_user.fetch({ fields: ['full_name', 'id', 'first_name', 'last_name'] });

            this.rendered = true;
        },

        /**
         * This is the success call for when it fetches the primary user from Sugar
         *
         * @param {Data.Bean} model
         * @param response
         */
        postUserFetch : function(model, response) {
            // load up the report to users
            this.reportees = app.data.createRelatedCollection(this.primary_user, "reportees");
            this.reportees.on('app:collection:fetch', this.postReporteesFetch, this);
            this.reportees.fetch({relate: true});
        },

        /**
         * Fetch handler event if the reportees collection changes
         *
         * @param {Data.BeanCollection} collection
         * @param response
         */
        postReporteesFetch : function(collection, response)
        {
            // success here, lets format the tree
            this.renderTree();
        },

        /**
         * Loop though all the Reportess and make a JSON array for the jsTree to consume.
         *
         * @param {String} parent_id
         * @return {Array}
         */
        findChildren : function(parent_id)
        {
            var children = [];

            _.each(this.reportees.models, function(reportee) {
                if(reportee.get('reports_to_id') == parent_id) {
                    var child = {
                        "data" : reportee.get('full_name'),
                        "metadata" : { model : reportee }
                    };

                    // check for children
                    var _reportee_children = this.findChildren(reportee.get('id'));

                    if(_reportee_children.length > 0) {
                        child.children = _reportee_children;
                    }

                    children.push(child);
                }
            }, this);

            return children;
        },

        /**
         * Render the JS Tree
         *
         * @private
         */
        renderTree : function()
        {
            var tree_data = { "data" : [
                {
                    "data" : this.primary_user.get('full_name'),
                    "children" : this.findChildren(this.primary_user.get('id')),
                    "metadata" : { model: this.primary_user },
                    "state" : "open"
                }
            ] };

            $("#jsTree").jstree({
                "plugins" : ["themes","json_data","ui","crrm"],
                "themes" : {
                            "theme" : "classic",
                            "dots" : false,
                            "icons" : true
                        },
                "json_data" : tree_data
            }).bind("select_node.jstree", this.treeNodeSelect);
        },

        treeNodeSelect: function(event, data)
        {
            jsData = data.inst.get_json();
            this.trigger('treeview:node_select', jsData[0].metadata.model);
        }
    });

})(SUGAR.App);