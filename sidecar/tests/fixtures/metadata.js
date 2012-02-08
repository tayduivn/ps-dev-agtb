/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 1/31/12
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */
fixtures.metadata = {
    "Contacts" : {
        "primary_bean" : "Contact",
        "beans" : {
            "Contact" : {
                "vardefs" : {
                    "table" : "contacts",
                    "fields" : {
                        "first_name" : {
                            "name" : "first_name",
                            "type" : "varchar"
                        },
                        "last_name" : {
                            "name" : "last_name",
                            "type" : "varchar"
                        }
                    }
                },
                "relationships" : {

                }
            }
        },
        "views" : {
            "EditView" : {
            },
            "DetailView" : {

            },
            "QuickCreate" : {

            },
            //This is stored in a listviewdefs variable on the server, but its inconsistent with the rest of the app
            "ListView" : {

            },
            //Subpanel layout defs
            "SubpanelView" : {

            }
        },
        //Layouts map an action to a lyout that defines a set of views and how to display them
        //Different clients will get different layouts for the same actions
        "layouts" : {
            "edit" : {
                //Default layout is a single view
                "type" : "simple",
                "components" : [
                    {view : "EditView"}
                ]
            },
            "detail" : {
                "components" : "rows",
                "views" : [
                    {view : "DetailView"},
                    {view : "SubpanelView"}
                ]
            },
            //Example of a sublayout. Two columns on the top and one view below that
            "sublayout" : {
                "type" : "rows",
                "components" : [
                    {"layout" : {
                        "type" : "columns",
                        "components" : [
                            {view : "ListView"},
                            {view : "DetailView"}
                        ]
                    }},
                    {"view" : "SubpanelView"}
                ]
            },
            //Layout with context switch. Edit view with related detail view
            "complexlayout" : {
                "type" : "columns",
                "components" : [
                    {"view" : "EditView"},
                    {
                        "view" : "DetailView",
                        //Name of link to pull the new context from, In this case a single account
                        "context" : "accounts"
                    }
                ]
            }
        }
    }
};