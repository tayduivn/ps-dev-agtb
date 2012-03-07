/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 1/31/12
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */

fixtures = typeof(fixtures) == "object" ? fixtures : {};
fixtures.metadata = {
    "Cases":{
        "primary_bean":"Case",
        "beans":{
            "Case":{
                "vardefs":{
                    "table":"cases",
                    "fields":{
                        "id":{
                            "name":"id",
                            "type":"id"
                        },
                        "case_number":{
                            "name":"case_number",
                            "type":"varchar"
                        },
                        "name":{
                            "name":"name",
                            "type":"varchar"
                        },
                        "description":{
                            "name":"description",
                            "type":"text"
                        },
                        "type":{
                            "name":"type",
                            "type":"varchar"
                        },
                        "status":{
                            "name":"status",
                            "type":"varchar"
                        },
                        "date_entered":{
                            "name":"date_entered",
                            "type":"varchar"
                        },
                        "created_by":{
                            "name":"created_by",
                            "type":"varchar"
                        },
                        "date_modified":{
                            "name":"date_modified",
                            "type":"varchar"
                        },
                        "modified_user_id":{
                            "name":"modified_user_id",
                            "type":"varchar"
                        }
                    }
                },
                "relationships":{

                }
            }
        },
        "views":{
            "editView":{
                "buttons":[
                    {
                        name:"save_button",
                        type:"button_save",
                        label:"Save",
                        value:"save",
                        primary:true,
                        events : {
                            click : ""
                        }
                    },
                    {
                        name:"cancel_button",
                        type:"button",
                        label:"Cancel",
                        value:"cancel",
                        route:{
                            action:"detail",
                            module: "Cases"
                        },
                        primary:false
                    }
                ],
                "panels":[
                    {
                        "label":"Details",
                        "fields":[
                            {name:"case_number", label:"Case Number", "class":"foo"},
                            {name:"name", label:"Name"},
                            {name:"status", label:"Status"},
                            {name:"description", label:"Description"}

                        ]
                    }
                ]

            },
            "detailView":{
                "buttons":[
                    {
                        name:"edit_button",
                        type:"button",
                        label:"Edit",
                        value:"edit",
                        route:{
                            action:"edit"
                        },
                        primary:true
                    }
                ],
                "panels":[
                    {
                        "label":"Details",
                        "fields":[
                            {name:"case_number", label:"Case Number", "class":"foo"},
                            {name:"name", label:"Name"},
                            {name:"status", label:"Status"},
                            {name:"description", label:"Description"}

                        ]
                    }
                ]
            },
            "quickCreate":{

            },
            //This is stored in a listviewdefs variable on the server, but its inconsistent with the rest of the app
            "listView":{
                "panels":[
                    {
                        "label":"LBL_PANEL_1",
                        "fields":[
                            {name:"case_number", label:"Case Number", "class":"foo"},
                            {name:"name", label:"Name"},
                            {name:"status", label:"Status"},
                            {type:"sugarField_actionsLink", label:"Actions"}
                        ]
                    }
                ]
            },
            //Subpanel layout defs
            "subpanelView":{

            }
        },
        //Layouts map an action to a lyout that defines a set of views and how to display them
        //Different clients will get different layouts for the same actions
        "layouts":{
            "edit":{
                //Default layout is a single view
                "type":"simple",
                "components":[
                    {view:"editView"}
                ]
            },
            "detail":{
                "type":"rows",
                "components":[
                    {view:"detailView"},
                    {view:"subpanelView"}
                ]
            },
            "list":{
                //Default layout is a single view
                "type":"simple",
                "components":[
                    {view:"listView"}
                ]
            },
            //Example of a sublayout. Two columns on the top and one view below that
            "sublayout":{
                "type":"rows",
                "components":[
                    {"layout":{
                        "type":"columns",
                        "components":[
                            {view:"editView"},
                            {view:"detailView"}
                        ]
                    }},
                    {"view":"subpanelView"}
                ]
            },
            //Layout with context switch. Edit view with related detail view
            "complexlayout":{
                "type":"columns",
                "components":[
                    {"view":"editView"},
                    {
                        "view":"detailView",
                        //Name of link to pull the new context from, In this case a single account
                        "context":"accounts"
                    }
                ]
            },
            //Layout that references another layout
            "detailplus":{
                "type":"fluid",
                "components":[
                    {view:"subpanelView",
                        size:2},
                    {layout:"edit",
                        size:6},
                    {layout:"detail",
                        size:3}
                ]
            }
        }
    },
    "Contacts":{
        "primary_bean":"Contact",
        "beans":{
            "Contact":{
                "vardefs":{
                    "table":"contacts",
                    "fields":{
                        "id":{
                            "name":"id",
                            "type":"id"
                        },
                        "first_name":{
                            "name":"first_name",
                            "type":"varchar"
                        },
                        "last_name":{
                            "name":"last_name",
                            "type":"varchar"
                        },
                        "phone_work":{
                            "name":"phone_work",
                            "type":"varchar"
                        },
                        "email1":{
                            "name":"email1",
                            "type":"varchar"
                        },
                        "full_name":{
                            "name":"full_name",
                            "type":"varchar",
                            "concat" : ["first_name", "last_name"]
                        }
                    }
                },
                "relationships":{

                }
            }
        },
        "views":{
            "editView":{
                "buttons":[
                    {
                        name:"save_button",
                        type:"button_save",
                        label:"Save",
                        value:"save",
                        primary:true
                    },
                    {
                        name:"cancel_button",
                        type:"button",
                        label:"Cancel",
                        value:"cancel",
                        route:{
                            action:"detail",
                            module: "Contacts"
                        },
                        events : {
                            //click : "SUGAR.App.myExtension.callback",
                            //drag: "",
                            foo : 'function(e){console.log(this)}'
                        },
                        primary:false
                    }
                ],
                "panels":[
                    {
                        "label":"Details",
                        "fields":[
                            {name:"first_name", label:"First Name", "class":"foo"},
                            {name:"last_name", label:"Last Name"},
                            {name:"phone_work", label:"Phone"},
                            {name:"email1", label:"Email"}
                        ]
                    }
                ]

            },
            "detailView":{
                "buttons":[
                    {
                        name:"edit_button",
                        type:"button",
                        label:"Edit",
                        value:"edit",
                        route:{
                            action:"edit"
                        },
                        primary:true
                    }
                ],
                "panels":[
                    {
                        "label":"Details",
                        "fields":[
                            {name:"first_name", label:"First Name"},
                            {name:"last_name", label:"Last Name"},
                            {name:"phone_work", label:"Phone"},
                            {name:"email1", label:"Email"},
                            {type:"sugarField_primaryAddress", label:"Address"}
                        ]
                    }
                ]
            },
            "quickCreate":{

            },
            //This is stored in a listviewdefs variable on the server, but its inconsistent with the rest of the app
            "listView":{
                "panels":[
                    {
                        "label":"LBL_PANEL_1",
                        "fields":[
                            {name:"first_name", label:"First Name"},
                            {name:"last_name", label:"Last Name"},
                            {name:"email1", label:"Email"},
                            {name:"phone_work", label:"Phone"},
                            {type:"sugarField_actionsLink", label:"Actions"}
                        ]
                    }
                ]
            },
            //Subpanel layout defs
            "subpanelView":{

            }
        },
        //Layouts map an action to a lyout that defines a set of views and how to display them
        //Different clients will get different layouts for the same actions
        "layouts":{
            "edit":{
                //Default layout is a single view
                "type":"simple",
                "components":[
                    {view:"editView"}
                ]
            },
            "detail":{
                "type":"rows",
                "components":[
                    {view:"detailView"},
                    {view:"subpanelView"}
                ]
            },
            "list":{
                //Default layout is a single view
                "type":"simple",
                "components":[
                    {view:"listView"}
                ]
            },
            //Example of a sublayout. Two columns on the top and one view below that
            "sublayout":{
                "type":"rows",
                "components":[
                    {"layout":{
                        "type":"columns",
                        "components":[
                            {view:"editView"},
                            {view:"detailView"}
                        ]
                    }},
                    {"view":"subpanelView"}
                ]
            },
            //Layout with context switch. Edit view with related detail view
            "complexlayout":{
                "type":"columns",
                "components":[
                    {"view":"editView"},
                    {
                        "view":"detailView",
                        //Name of link to pull the new context from, In this case a single account
                        "context":"accounts"
                    }
                ]
            },
            //Layout that references another layout
            "detailplus":{
                "type":"fluid",
                "components":[
                    {view:"subpanelView",
                        size:2},
                    {layout:"edit",
                        size:6},
                    {layout:"detail",
                        size:3}
                ]
            }
        }
    }
};