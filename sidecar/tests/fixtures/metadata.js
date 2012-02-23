/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 1/31/12
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */

fixtures = typeof(fixtures) == "object" ? fixtures : {};
fixtures.metadata = {
    "Contacts" : {
        "primary_bean" : "Contact",
        "beans" : {
            "Contact" : {
                "vardefs" : {
                    "table" : "contacts",
                    "fields" : {
                        "id" : {
                            "name" : "id",
                            "type" : "id"
                        },
                        "first_name" : {
                            "name" : "first_name",
                            "type" : "varchar"
                        },
                        "last_name" : {
                            "name" : "last_name",
                            "type" : "varchar"
                        },
                        "phone_work" : {
                            "name" : "phone_work",
                            "type" : "varchar"
                        },
                        "email1" : {
                            "name" : "email1",
                            "type" : "varchar"
                        }
                    }
                },
                "relationships" : {

                }
            }
        },
        "views" : {
            "editView" : {
                "panels" : [{
                    "label" : "LBL_PANEL_1",
                    "fields" : [
                        {name:"first_name", label:"First Name"},
						{name:"last_name", label:"Last Name"}
                    ]
                }]

            },
            "detailView" : {
                "panels" : [{
                    "label" : "LBL_PANEL_1",
                    "fields" : [
                        {name:"first_name", label:"First Name"},
                        {name:"last_name", label:"Last Name"}
                    ]
                }]
            },
            "quickCreate" : {

            },
            //This is stored in a listviewdefs variable on the server, but its inconsistent with the rest of the app
            "listView" : {
                "panels" : [{
                    "label" : "",
                    "fields" : [
                        {type:"sugarField_fullName", label:"Name"},
                        {name:"phone_work", label:"Phone"},
                        {name:"email1", label:"Email"},
                        {type:"sugarField_actionsLink", label:""}
                    ]
                }]
            },
            //Subpanel layout defs
            "subpanelView" : {

            }
        },
        //Layouts map an action to a lyout that defines a set of views and how to display them
        //Different clients will get different layouts for the same actions
        "layouts" : {
            "edit" : {
                //Default layout is a single view
                "type" : "simple",
                "components" : [
                    {view : "editView"}
                ]
            },
            "detail" : {
                "type" : "rows",
                "components" : [
                    {view : "detailView"},
                    {view : "subpanelView"}
                ]
            },
            "list" : {
                //Default layout is a single view
                "type" : "simple",
                "components" : [
                    {view : "listView"}
                ]
            },
            //Example of a sublayout. Two columns on the top and one view below that
            "sublayout" : {
                "type" : "rows",
                "components" : [
                    {"layout" : {
                        "type" : "columns",
                        "components" : [
                            {view : "editView"},
                            {view : "detailView"}
                        ]
                    }},
                    {"view" : "subpanelView"}
                ]
            },
            //Layout with context switch. Edit view with related detail view
            "complexlayout" : {
                "type" : "columns",
                "components" : [
                    {"view" : "editView"},
                    {
                        "view" : "detailView",
                        //Name of link to pull the new context from, In this case a single account
                        "context" : "accounts"
                    }
                ]
            },
            //Layout that references another layout
            "detailplus" : {
                "type" : "fluid",
                "components" : [
                    {view : "subpanelView",
                     size: 2},
                    {layout : "edit",
                    size: 6},
                    {layout: "detail",
                    size: 3}
                ]
            }
        }
    }
};