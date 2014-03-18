/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    className: 'container-fluid',

    // forms jstree
    _renderHtml: function () {
        var self = this;

        this._super('_renderHtml');

        this.$('#people').jstree({
            "json_data" : {
                "data" : [
                    {
                        "data" : "Sabra Khan",
                        "state" : "open",
                        "metadata" : { id : 1 },
                        "children" : [
                            {"data" : "Mark Gibson","metadata" : { id : 2 }},
                            {"data" : "James Joplin","metadata" : { id : 3 }},
                            {"data" : "Terrence Li","metadata" : { id : 4 }},
                            {"data" : "Amy McCray",
                                "metadata" : { id : 5 },
                                "children" : [
                                    {"data" : "Troy McClure","metadata" : {id : 6}},
                                    {"data" : "James Kirk","metadata" : {id : 7}}
                                ]
                            }
                        ]
                    }
                ]
            },
            // "themes" : { "theme" : "default", "dots" : false },
            "plugins" : [ "json_data", "ui", "types" ]
        })
        .bind('loaded.jstree', function () {
            // do stuff when tree is loaded
            self.$('#people').addClass('jstree-sugar');
            self.$('#people > ul').addClass('list');
            self.$('#people > ul > li > a').addClass('jstree-clicked');
        })
        .bind('select_node.jstree', function (e, data) {
            data.inst.toggle_node(data.rslt.obj);
        });
    }
})
