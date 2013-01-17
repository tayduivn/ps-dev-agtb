/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    extendsFrom: "ListView",
    gTable: {},
    initialize: function(options) {
        var auditCollection;
        var self = this;

        options.meta.type = "list";

        // populating metadata for audit module
        if (options.context.parent) {
            this.baseModule = options.context.parent.get("module");
            this.baseRecord = options.context.parent.get("modelId");
        }

        this.auditedFields = _.chain(app.metadata.getModule(this.baseModule,"fields"))
                              .filter(function(o) {return o.audited;})
                              .map(function(o) {return app.lang.get(o.vname, self.baseModule)})
                              .value();
        // base field
        app.view.invokeParent(this, {
            type: 'view',
            name: 'list',
            method: 'initialize',
            args: [options]
        });

       // override the collection set up by our parent:
       // audit needs params to call itself
       auditCollection = this.context.get("collection");
       if (auditCollection) {
           _.extend(auditCollection, {
                   sync: function(method, model, options) {
                        options.params = this.getParams(options.params);
                        app.BeanCollection.prototype.sync.call(this, method, model, options);
                   },
                   getParams: function(params) {
                       params = params || {};
                       return _.extend(params, {
                           module: self.baseModule,
                           record: self.baseRecord
                       });
                   }
           });
       }
    },
    _render: function() {
        // we need to have the fields rendered before initializing the datatable
        // so that we get the correct row calculation -- this is why we are
        // overriding _render instead of _renderHtml
        app.view.View.prototype._render.call(this);

        // in case we retrieved no data default to header row
        var row = this.$(".datatable tbody tr:first");
        if (row.length === 0) {
            row = this.$(".datatable tr:first");
        }
        // get rid of the old table if necessary.
        this.destroyDataTable(this.gTable);

        this.gTable = this.$el.find(".datatable").dataTable({
            "bSort": false,
            "bFilter": false,
            "bInfo":false,
            "bPaginate": false,
            "sScrollY": (row.outerHeight() + 2) * 3,
            "bScrollCollapse": true
        });
    },
    loadData: function() {
        this.context.resetLoadFlag();
        app.view.View.prototype.loadData.call(this);
    },
    destroyDataTable: function (table) {
        if (table && _.isFunction(table,'fnDestroy')) {
            table.fnDestroy();
        }
    },

    /**
     * overriding component's remove method since we're connecting directly to an existing parent element.
     */
    remove: function() {
        return this.$el.empty();
    },

    /**
     * clean up the garbage
     * @private
     */
    _dispose: function() {
        this.destroyDataTable(this.gTable);
        app.view.invokeParent(this, {
            type: 'view',
            name: 'list',
            method: '_dispose'
        });
    }
})
