({

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    events: {
      //  'click [class*="orderBy"]': 'setOrderBy',
      //  'mouseenter tr': 'showActions',
      //  'mouseleave tr': 'hideActions'
    },
    _renderHtml: function() {
        debugger;
        app.view.View.prototype._renderHtml.call(this);
        // off prevents multiple bindings for each render
        this.layout.off("list:search:fire", null, this);
        this.layout.off("list:paginate:success", null, this);
        this.layout.on("list:search:fire", this.fireSearch, this);
        this.layout.on("list:paginate:success", this.render, this);
        this.layout.off("list:filter:toggled", null, this);
        this.layout.on("list:filter:toggled", this.filterToggled, this);

        // Dashboard layout injects shared context with limit: 5.
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;

        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;
        // define vars for use in loop, created outside loop
        var def = {};
        var name = '';
        var colWidth = '';

        for( var i = 0; i < fields.length; i++ )  {
            name = fields[i].name;
            // explicitly looking for column "name" instead of the first column
            // in case we add column rearranging
            if(name == "name") {
                colWidth = '40%';
            } else {
                colWidth = '10%';
            }
            def = {
                "sName": name,
                "aTargets": [ i ],
                "sWidth" : colWidth
            };
            columnDefs.push( def );
        }

        this.gTable = this.$(".view-quickcreate-list").dataTable(
            {
                "bAutoWidth": false,
                "aoColumnDefs": columnDefs,
                "bInfo":false,
                "bPaginate":false

            }
        );
    },
    /*
    filterToggled: function(isOpened) {
        this.filterOpened = isOpened;
    },
    fireSearch: function(term) {
        var options = {
            limit: this.limit || null,
            params: {
                q: term
            },
            fields: this.collection.fields || {}
        };
        this.collection.fetch(options);
    },

 */

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})

