var nomad_fixtures = {
    "modules": {
        "Teams": {
            "fields": {}
        },

        "TeamSets": {
            "fields": {}
        },
        "Accounts": {
            "fields": {}
        },
        "Calls": {
            "fields": {}
        },
        "Opportunities": {
            "fields": {
                "name": {
                    "name": "name",
                    "required": true
                },
                "account_name": {
                    "name": "account_name",
                    "rname": "name",
                    "id_name": "account_id",
                    "vname": "LBL_ACCOUNT_NAME",
                    "type": "relate",
                    "table": "accounts",
                    "join_name": "accounts",
                    "isnull": "true",
                    "module": "Accounts",
                    "dbType": "varchar",
                    "link": "accounts",
                    "len": 20,
                    "source": "non-db",
                    "unified_search": true,
                    "importable": "required"
                },
                "account_id": {
                    "name": "account_id",
                    "vname": "LBL_ACCOUNT_ID",
                    "type": "id",
                    "source": "non-db",
                    "audited": true
                },
                "contacts": {
                    "name": "contacts",
                    "type": "link",
                    "relationship": "opportunities_contacts"
                },
                "accounts": {
                    "name": "accounts",
                    "type": "link",
                    "relationship": "accounts_opportunities"
                },
                "calls": {
                    "name": "calls",
                    "type": "link",
                    "relationship": "opportunity_calls"
                }

            },
            "relationships": {
                "opportunities_contacts": {
                    "lhs_module": "Opportunities",
                    "lhs_link": "contacts",
                    "rhs_module": "Contacts",
                    "rhs_link": "opportunities",
                    "relationship_type": "many-to-many"
                },
                "accounts_opportunities": {
                    "lhs_module": "Accounts",
                    "lhs_table": "accounts",
                    "lhs_link": "opportunities",
                    "rhs_module": "Opportunities",
                    "rhs_table": "opportunities",
                    "rhs_link": "opportunities",
                    "relationship_type": "one-to-many"
                },
                "opportunity_calls": {
                    "lhs_module": "Opportunities",
                    "lhs_link": "calls",
                    "rhs_module": "Calls",
                    "rhs_link": "opportunities",
                    "relationship_type": "one-to-many"
                }

            },
            "views": {
                "detail": {
                    "meta": {
                        "panels": []
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "components": [
                            {"view": "detail"}
                        ]
                    }
                }
            }
        },
        "Contacts": {
            "fields": {
                "first_name": {
                    "name": "first_name",
                    "type": "varchar",
                    "len": 20
                },
                "last_name": {
                    "name": "last_name",
                    "type": "varchar",
                    "len": 20
                },
                "field_0": {
                    "name": "field_0",
                    "default": 100
                }

            },
            "relationships": {

            },
            "views": {
                "EditView": {
                },
                "DetailView": {

                },
                "QuickCreate": {

                },
                "ListView": {

                },
                "SubpanelView": {

                }
            },
            "layouts": {
                "edit": {
                    "meta": {
                        "type": "simple",
                        "components": [
                            {
                                "view": "EditView"
                            }
                        ]
                    }
                },
                "detail": {
                    "meta": {
                        "components": "rows",
                        "views": [
                            {
                                "view": "DetailView"
                            },
                            {
                                "view": "SubpanelView"
                            }
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "simple",
                        "components": [
                            {"view": "list"}
                        ]
                    }
                },
                "sublayout": {
                    "meta": {
                        "type": "rows",
                        "components": [
                            {
                                "layout": {
                                    "type": "columns",
                                    "components": [
                                        {
                                            "view": "ListView"
                                        },
                                        {
                                            "view": "DetailView"
                                        }
                                    ]
                                }
                            },
                            {
                                "view": "SubpanelView"
                            }
                        ]
                    }
                },
                "complexlayout": {
                    "meta": {
                        "type": "columns",
                        "components": [
                            {
                                "view": "EditView"
                            },
                            {
                                "view": "DetailView",
                                "context": "accounts"
                            }
                        ]
                    }
                }
            }
        }

    },
    'moduleList': {
        'Cases':'Cases',
        'Bugs':'Bugs',
        'Opportunities': 'Opportunities',
        '_hash':'dfl23asfd'
    },
    "_hash": "hash"
}
