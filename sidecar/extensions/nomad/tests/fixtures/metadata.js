var nomad_fixtures = {
    "modules":{
        "Teams":{
            "fields":{}
        },

        "TeamSets":{
            "fields":{}
        },
        "Accounts":{
            "fields":{
                "id":{
                    "name":"id", "vname":"LBL_ID", "type":"id", "required":true, "reportable":true, "comment":"Unique identifier"
                },
                "name":{
                    "name":"name", "type":"name", "dbType":"varchar", "vname":"LBL_NAME", "len":150, "comment":"Name of the Company", "unified_search":true, "full_text_search":{
                        "boost":3
                    },
                    "audited":true, "required":true, "importable":"required", "merge_filter":"selected"
                },
                "date_entered":{
                    "name":"date_entered", "vname":"LBL_DATE_ENTERED", "type":"datetime", "group":"created_by_name", "comment":"Date record created", "enable_range_search":true, "options":"date_range_search_dom"
                },
                "date_modified":{
                    "name":"date_modified", "vname":"LBL_DATE_MODIFIED", "type":"datetime", "group":"modified_by_name", "comment":"Date record last modified", "enable_range_search":true, "options":"date_range_search_dom"
                },
                "modified_user_id":{
                    "name":"modified_user_id", "rname":"user_name", "id_name":"modified_user_id", "vname":"LBL_MODIFIED", "type":"assigned_user_name", "table":"users", "isnull":"false", "group":"modified_by_name", "dbType":"id", "reportable":true, "comment":"User who last modified record", "massupdate":false
                },
                "modified_by_name":{
                    "name":"modified_by_name", "vname":"LBL_MODIFIED_NAME", "type":"relate", "reportable":false, "source":"non-db", "rname":"user_name", "table":"users", "id_name":"modified_user_id", "module":"Users", "link":"modified_user_link", "duplicate_merge":"disabled", "massupdate":false
                },
                "created_by":{
                    "name":"created_by", "rname":"user_name", "id_name":"modified_user_id", "vname":"LBL_CREATED", "type":"assigned_user_name", "table":"users", "isnull":"false", "dbType":"id", "group":"created_by_name", "comment":"User who created record", "massupdate":false
                },
                "created_by_name":{
                    "name":"created_by_name", "vname":"LBL_CREATED", "type":"relate", "reportable":false, "link":"created_by_link", "rname":"user_name", "source":"non-db", "table":"users", "id_name":"created_by", "module":"Users", "duplicate_merge":"disabled", "importable":"false", "massupdate":false
                },
                "description":{
                    "name":"description", "vname":"LBL_DESCRIPTION", "type":"text", "comment":"Full text of the note", "rows":6, "cols":80
                },
                "deleted":{
                    "name":"deleted", "vname":"LBL_DELETED", "type":"bool", "default":"0", "reportable":false, "comment":"Record deletion indicator"
                },
                "created_by_link":{
                    "name":"created_by_link", "type":"link", "relationship":"accounts_created_by", "vname":"LBL_CREATED_BY_USER", "link_type":"one", "module":"Users", "bean_name":"User", "source":"non-db"
                },
                "modified_user_link":{
                    "name":"modified_user_link", "type":"link", "relationship":"accounts_modified_user", "vname":"LBL_MODIFIED_BY_USER", "link_type":"one", "module":"Users", "bean_name":"User", "source":"non-db"
                },
                "assigned_user_id":{
                    "name":"assigned_user_id", "rname":"user_name", "id_name":"assigned_user_id", "vname":"LBL_ASSIGNED_TO_ID", "group":"assigned_user_name", "type":"relate", "table":"users", "module":"Users", "reportable":true, "isnull":"false", "dbType":"id", "audited":true, "comment":"User ID assigned to record", "duplicate_merge":"disabled"
                },
                "assigned_user_name":{
                    "name":"assigned_user_name", "link":"assigned_user_link", "vname":"LBL_ASSIGNED_TO_NAME", "rname":"user_name", "type":"relate", "reportable":false, "source":"non-db", "table":"users", "id_name":"assigned_user_id", "module":"Users", "duplicate_merge":"disabled"
                },
                "assigned_user_link":{
                    "name":"assigned_user_link", "type":"link", "relationship":"accounts_assigned_user", "vname":"LBL_ASSIGNED_TO_USER", "link_type":"one", "module":"Users", "bean_name":"User", "source":"non-db", "duplicate_merge":"enabled", "rname":"user_name", "id_name":"assigned_user_id", "table":"users"
                },
                "team_id":{
                    "name":"team_id", "vname":"LBL_TEAM_ID", "group":"team_name", "reportable":false, "dbType":"id", "type":"team_list", "audited":true, "comment":"Team ID for the account"
                },
                "team_set_id":{
                    "name":"team_set_id", "rname":"id", "id_name":"team_set_id", "vname":"LBL_TEAM_SET_ID", "type":"id", "audited":true, "studio":"false", "dbType":"id"
                },
                "team_count":{
                    "name":"team_count", "rname":"team_count", "id_name":"team_id", "vname":"LBL_TEAMS", "join_name":"ts1", "table":"teams", "type":"relate", "required":"true", "isnull":"true", "module":"Teams", "link":"team_count_link", "massupdate":false, "dbType":"int", "source":"non-db", "importable":"false", "reportable":false, "duplicate_merge":"disabled", "studio":"false", "hideacl":true
                },
                "team_name":{
                    "name":"team_name", "db_concat_fields":["name", "name_2"], "sort_on":"tj.name", "join_name":"tj", "rname":"name", "id_name":"team_id", "vname":"LBL_TEAMS", "type":"relate", "required":"true", "table":"teams", "isnull":"true", "module":"Teams", "link":"team_link", "massupdate":false, "dbType":"varchar", "source":"non-db", "len":36, "custom_type":"teamset"
                },
                "team_link":{
                    "name":"team_link", "type":"link", "relationship":"accounts_team", "vname":"LBL_TEAMS_LINK", "link_type":"one", "module":"Teams", "bean_name":"Team", "source":"non-db", "duplicate_merge":"disabled", "studio":"false"
                },
                "team_count_link":{
                    "name":"team_count_link", "type":"link", "relationship":"accounts_team_count_relationship", "link_type":"one", "module":"Teams", "bean_name":"TeamSet", "source":"non-db", "duplicate_merge":"disabled", "reportable":false, "studio":"false"
                },
                "teams":{
                    "name":"teams", "type":"link", "relationship":"accounts_teams", "bean_filter_field":"team_set_id", "rhs_key_override":true, "source":"non-db", "vname":"LBL_TEAMS", "link_class":"TeamSetLink", "link_file":"modules\/Teams\/TeamSetLink.php", "studio":"false", "reportable":false
                },
                "account_type":{
                    "name":"account_type", "vname":"LBL_TYPE", "type":"enum", "options":"account_type_dom", "len":50, "comment":"The Company is of this type"
                },
                "industry":{
                    "name":"industry", "vname":"LBL_INDUSTRY", "type":"enum", "options":"industry_dom", "len":50, "comment":"The company belongs in this industry", "merge_filter":"enabled"
                },
                "annual_revenue":{
                    "name":"annual_revenue", "vname":"LBL_ANNUAL_REVENUE", "type":"varchar", "len":100, "comment":"Annual revenue for this company", "merge_filter":"enabled"
                },
                "phone_fax":{
                    "name":"phone_fax", "vname":"LBL_FAX", "type":"phone", "dbType":"varchar", "len":100, "unified_search":true, "full_text_search":{
                        "boost":1
                    },
                    "comment":"The fax phone number of this company"
                },
                "billing_address_street":{
                    "name":"billing_address_street", "vname":"LBL_BILLING_ADDRESS_STREET", "type":"varchar", "len":"150", "comment":"The street address used for billing address", "group":"billing_address", "merge_filter":"enabled"
                },
                "billing_address_street_2":{
                    "name":"billing_address_street_2", "vname":"LBL_BILLING_ADDRESS_STREET_2", "type":"varchar", "len":"150", "source":"non-db"
                },
                "billing_address_street_3":{
                    "name":"billing_address_street_3", "vname":"LBL_BILLING_ADDRESS_STREET_3", "type":"varchar", "len":"150", "source":"non-db"
                },
                "billing_address_street_4":{
                    "name":"billing_address_street_4", "vname":"LBL_BILLING_ADDRESS_STREET_4", "type":"varchar", "len":"150", "source":"non-db"
                },
                "billing_address_city":{
                    "name":"billing_address_city", "vname":"LBL_BILLING_ADDRESS_CITY", "type":"varchar", "len":"100", "comment":"The city used for billing address", "group":"billing_address", "merge_filter":"enabled"
                },
                "billing_address_state":{
                    "name":"billing_address_state", "vname":"LBL_BILLING_ADDRESS_STATE", "type":"varchar", "len":"100", "group":"billing_address", "comment":"The state used for billing address", "merge_filter":"enabled"
                },
                "billing_address_postalcode":{
                    "name":"billing_address_postalcode", "vname":"LBL_BILLING_ADDRESS_POSTALCODE", "type":"varchar", "len":"20", "group":"billing_address", "comment":"The postal code used for billing address", "merge_filter":"enabled"
                },
                "billing_address_country":{
                    "name":"billing_address_country", "vname":"LBL_BILLING_ADDRESS_COUNTRY", "type":"varchar", "group":"billing_address", "comment":"The country used for the billing address", "merge_filter":"enabled"
                },
                "rating":{
                    "name":"rating", "vname":"LBL_RATING", "type":"varchar", "len":100, "comment":"An arbitrary rating for this company for use in comparisons with others"
                },
                "phone_office":{
                    "name":"phone_office", "vname":"LBL_PHONE_OFFICE", "type":"phone", "dbType":"varchar", "len":100, "audited":true, "unified_search":true, "full_text_search":{
                        "boost":1
                    },
                    "comment":"The office phone number", "merge_filter":"enabled"
                },
                "phone_alternate":{
                    "name":"phone_alternate", "vname":"LBL_PHONE_ALT", "type":"phone", "group":"phone_office", "dbType":"varchar", "len":100, "unified_search":true, "full_text_search":{
                        "boost":1
                    },
                    "comment":"An alternate phone number", "merge_filter":"enabled"
                },
                "website":{
                    "name":"website", "vname":"LBL_WEBSITE", "type":"url", "dbType":"varchar", "len":255, "comment":"URL of website for the company"
                },
                "ownership":{
                    "name":"ownership", "vname":"LBL_OWNERSHIP", "type":"varchar", "len":100, "comment":""
                },
                "employees":{
                    "name":"employees", "vname":"LBL_EMPLOYEES", "type":"varchar", "len":10, "comment":"Number of employees, varchar to accomodate for both number (100) or range (50-100)"
                },
                "ticker_symbol":{
                    "name":"ticker_symbol", "vname":"LBL_TICKER_SYMBOL", "type":"varchar", "len":10, "comment":"The stock trading (ticker) symbol for the company", "merge_filter":"enabled"
                },
                "shipping_address_street":{
                    "name":"shipping_address_street", "vname":"LBL_SHIPPING_ADDRESS_STREET", "type":"varchar", "len":150, "group":"shipping_address", "comment":"The street address used for for shipping purposes", "merge_filter":"enabled"
                },
                "shipping_address_street_2":{
                    "name":"shipping_address_street_2", "vname":"LBL_SHIPPING_ADDRESS_STREET_2", "type":"varchar", "len":150, "source":"non-db"
                },
                "shipping_address_street_3":{
                    "name":"shipping_address_street_3", "vname":"LBL_SHIPPING_ADDRESS_STREET_3", "type":"varchar", "len":150, "source":"non-db"
                },
                "shipping_address_street_4":{
                    "name":"shipping_address_street_4", "vname":"LBL_SHIPPING_ADDRESS_STREET_4", "type":"varchar", "len":150, "source":"non-db"
                },
                "shipping_address_city":{
                    "name":"shipping_address_city", "vname":"LBL_SHIPPING_ADDRESS_CITY", "type":"varchar", "len":100, "group":"shipping_address", "comment":"The city used for the shipping address", "merge_filter":"enabled"
                },
                "shipping_address_state":{
                    "name":"shipping_address_state", "vname":"LBL_SHIPPING_ADDRESS_STATE", "type":"varchar", "len":100, "group":"shipping_address", "comment":"The state used for the shipping address", "merge_filter":"enabled"
                },
                "shipping_address_postalcode":{
                    "name":"shipping_address_postalcode", "vname":"LBL_SHIPPING_ADDRESS_POSTALCODE", "type":"varchar", "len":20, "group":"shipping_address", "comment":"The zip code used for the shipping address", "merge_filter":"enabled"
                },
                "shipping_address_country":{
                    "name":"shipping_address_country", "vname":"LBL_SHIPPING_ADDRESS_COUNTRY", "type":"varchar", "group":"shipping_address", "comment":"The country used for the shipping address", "merge_filter":"enabled"
                },
                "email1":{
                    "name":"email1", "vname":"LBL_EMAIL", "group":"email1", "type":"varchar", "function":{
                        "name":"getEmailAddressWidget", "returns":"html"
                    },
                    "source":"non-db", "studio":{
                        "editField":true, "searchview":false
                    }
                },
                "email_addresses_primary":{
                    "name":"email_addresses_primary", "type":"link", "relationship":"accounts_email_addresses_primary", "source":"non-db", "vname":"LBL_EMAIL_ADDRESS_PRIMARY", "duplicate_merge":"disabled", "studio":{
                        "formula":false
                    }
                },
                "email_addresses":{
                    "name":"email_addresses", "type":"link", "relationship":"accounts_email_addresses", "source":"non-db", "vname":"LBL_EMAIL_ADDRESSES", "reportable":false, "unified_search":true, "rel_fields":{
                        "primary_address":{
                            "type":"bool"
                        }
                    },
                    "studio":{
                        "formula":false
                    }
                },
                "parent_id":{
                    "name":"parent_id", "vname":"LBL_PARENT_ACCOUNT_ID", "type":"id", "required":false, "reportable":false, "audited":true, "comment":"Account ID of the parent of this account"
                },
                "sic_code":{
                    "name":"sic_code", "vname":"LBL_SIC_CODE", "type":"varchar", "len":10, "comment":"SIC code of the account", "merge_filter":"enabled"
                },
                "parent_name":{
                    "name":"parent_name", "rname":"name", "id_name":"parent_id", "vname":"LBL_MEMBER_OF", "type":"relate", "isnull":"true", "module":"Accounts", "table":"accounts", "massupdate":false, "source":"non-db", "len":36, "link":"member_of", "unified_search":true, "importable":"true"
                },
                "members":{
                    "name":"members", "type":"link", "relationship":"member_accounts", "module":"Accounts", "bean_name":"Account", "source":"non-db", "vname":"LBL_MEMBERS"
                },
                "member_of":{
                    "name":"member_of", "type":"link", "relationship":"member_accounts", "module":"Accounts", "bean_name":"Account", "link_type":"one", "source":"non-db", "vname":"LBL_MEMBER_OF", "side":"right"
                },
                "email_opt_out":{
                    "name":"email_opt_out", "vname":"LBL_EMAIL_OPT_OUT", "source":"non-db", "type":"bool", "massupdate":false, "studio":"false"
                },
                "invalid_email":{
                    "name":"invalid_email", "vname":"LBL_INVALID_EMAIL", "source":"non-db", "type":"bool", "massupdate":false, "studio":"false"
                },
                "cases":{
                    "name":"cases", "type":"link", "relationship":"account_cases", "module":"Cases", "bean_name":"aCase", "source":"non-db", "vname":"LBL_CASES"
                },
                "email":{
                    "name":"email", "type":"email", "query_type":"default", "source":"non-db", "operator":"subquery", "subquery":"SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE", "db_field":["id"], "vname":"LBL_ANY_EMAIL", "studio":{
                        "visible":false, "searchview":true
                    }
                },
                "tasks":{
                    "name":"tasks", "type":"link", "relationship":"account_tasks", "module":"Tasks", "bean_name":"Task", "source":"non-db", "vname":"LBL_TASKS"
                },
                "notes":{
                    "name":"notes", "type":"link", "relationship":"account_notes", "module":"Notes", "bean_name":"Note", "source":"non-db", "vname":"LBL_NOTES"
                },
                "meetings":{
                    "name":"meetings", "type":"link", "relationship":"account_meetings", "module":"Meetings", "bean_name":"Meeting", "source":"non-db", "vname":"LBL_MEETINGS"
                },
                "calls":{
                    "name":"calls", "type":"link", "relationship":"account_calls", "module":"Calls", "bean_name":"Call", "source":"non-db", "vname":"LBL_CALLS"
                },
                "emails":{
                    "name":"emails", "type":"link", "relationship":"emails_accounts_rel", "module":"Emails", "bean_name":"Email", "source":"non-db", "vname":"LBL_EMAILS", "studio":{
                        "formula":false
                    }
                },
                "documents":{
                    "name":"documents", "type":"link", "relationship":"documents_accounts", "source":"non-db", "vname":"LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "bugs":{
                    "name":"bugs", "type":"link", "relationship":"accounts_bugs", "module":"Bugs", "bean_name":"Bug", "source":"non-db", "vname":"LBL_BUGS"
                },
                "contacts":{
                    "name":"contacts", "type":"link", "relationship":"accounts_contacts", "module":"Contacts", "bean_name":"Contact", "source":"non-db", "vname":"LBL_CONTACTS"
                },
                "opportunities":{
                    "name":"opportunities", "type":"link", "relationship":"accounts_opportunities", "module":"Opportunities", "bean_name":"Opportunity", "source":"non-db", "vname":"LBL_OPPORTUNITY"
                },
                "quotes":{
                    "name":"quotes", "type":"link", "relationship":"quotes_billto_accounts", "source":"non-db", "module":"Quotes", "bean_name":"Quote", "ignore_role":true, "vname":"LBL_QUOTES"
                },
                "quotes_shipto":{
                    "name":"quotes_shipto", "type":"link", "relationship":"quotes_shipto_accounts", "module":"Quotes", "bean_name":"Quote", "source":"non-db", "vname":"LBL_QUOTES_SHIP_TO"
                },
                "project":{
                    "name":"project", "type":"link", "relationship":"projects_accounts", "module":"Project", "bean_name":"Project", "source":"non-db", "vname":"LBL_PROJECTS"
                },
                "leads":{
                    "name":"leads", "type":"link", "relationship":"account_leads", "module":"Leads", "bean_name":"Lead", "source":"non-db", "vname":"LBL_LEADS"
                },
                "campaigns":{
                    "name":"campaigns", "type":"link", "relationship":"account_campaign_log", "module":"CampaignLog", "bean_name":"CampaignLog", "source":"non-db", "vname":"LBL_CAMPAIGNLOG", "studio":{
                        "formula":false
                    }
                },
                "campaign_accounts":{
                    "name":"campaign_accounts", "type":"link", "vname":"LBL_CAMPAIGNS", "relationship":"campaign_accounts", "source":"non-db"
                },
                "products":{
                    "name":"products", "type":"link", "relationship":"products_accounts", "source":"non-db", "vname":"LBL_PRODUCTS"
                },
                "contracts":{
                    "name":"contracts", "type":"link", "relationship":"account_contracts", "source":"non-db", "vname":"LBL_CONTRACTS"
                },
                "campaign_id":{
                    "name":"campaign_id", "comment":"Campaign that generated Account", "vname":"LBL_CAMPAIGN_ID", "rname":"id", "id_name":"campaign_id", "type":"id", "table":"campaigns", "isnull":"true", "module":"Campaigns", "reportable":false, "massupdate":false, "duplicate_merge":"disabled"
                },
                "campaign_name":{
                    "name":"campaign_name", "rname":"name", "vname":"LBL_CAMPAIGN", "type":"relate", "reportable":false, "source":"non-db", "table":"campaigns", "id_name":"campaign_id", "link":"campaign_accounts", "module":"Campaigns", "duplicate_merge":"disabled", "comment":"The first campaign name for Account (Meta-data only)"
                },
                "prospect_lists":{
                    "name":"prospect_lists", "type":"link", "relationship":"prospect_list_accounts", "module":"ProspectLists", "source":"non-db", "vname":"LBL_PROSPECT_LIST"
                },
                "image_field_c":{
                    "required":false, "source":"custom_fields", "name":"image_field_c", "vname":"LBL_IMAGE_FIELD", "type":"image", "massupdate":"0", "default":null, "comments":"", "help":"", "importable":"true", "duplicate_merge":"disabled", "duplicate_merge_dom_value":"0", "audited":false, "reportable":true, "unified_search":false, "merge_filter":"disabled", "calculated":false, "len":255, "size":"20", "studio":"visible", "dbType":"varchar", "border":"", "width":"120", "height":"", "id":"Accountsimage_field_c", "custom_module":"Accounts"
                }
            },
            "relationships":{
                "accounts_modified_user":{
                    "lhs_module":"Users", "lhs_table":"users", "lhs_key":"id", "rhs_module":"Accounts", "rhs_table":"accounts", "rhs_key":"modified_user_id", "relationship_type":"one-to-many"
                },
                "accounts_created_by":{
                    "lhs_module":"Users", "lhs_table":"users", "lhs_key":"id", "rhs_module":"Accounts", "rhs_table":"accounts", "rhs_key":"created_by", "relationship_type":"one-to-many"
                },
                "accounts_assigned_user":{
                    "lhs_module":"Users", "lhs_table":"users", "lhs_key":"id", "rhs_module":"Accounts", "rhs_table":"accounts", "rhs_key":"assigned_user_id", "relationship_type":"one-to-many"
                },
                "accounts_team_count_relationship":{
                    "lhs_module":"Teams", "lhs_table":"team_sets", "lhs_key":"id", "rhs_module":"Accounts", "rhs_table":"accounts", "rhs_key":"team_set_id", "relationship_type":"one-to-many"
                },
                "accounts_teams":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"team_set_id", "rhs_module":"Teams", "rhs_table":"teams", "rhs_key":"id", "relationship_type":"many-to-many", "join_table":"team_sets_teams", "join_key_lhs":"team_set_id", "join_key_rhs":"team_id"
                },
                "accounts_team":{
                    "lhs_module":"Teams", "lhs_table":"teams", "lhs_key":"id", "rhs_module":"Accounts", "rhs_table":"accounts", "rhs_key":"team_id", "relationship_type":"one-to-many"
                },
                "accounts_email_addresses":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"EmailAddresses", "rhs_table":"email_addresses", "rhs_key":"id", "relationship_type":"many-to-many", "join_table":"email_addr_bean_rel", "join_key_lhs":"bean_id", "join_key_rhs":"email_address_id", "relationship_role_column":"bean_module", "relationship_role_column_value":"Accounts"
                },
                "accounts_email_addresses_primary":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"EmailAddresses", "rhs_table":"email_addresses", "rhs_key":"id", "relationship_type":"many-to-many", "join_table":"email_addr_bean_rel", "join_key_lhs":"bean_id", "join_key_rhs":"email_address_id", "relationship_role_column":"primary_address", "relationship_role_column_value":"1"
                },
                "member_accounts":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Accounts", "rhs_table":"accounts", "rhs_key":"parent_id", "relationship_type":"one-to-many"
                },
                "account_cases":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Cases", "rhs_table":"cases", "rhs_key":"account_id", "relationship_type":"one-to-many"
                },
                "account_tasks":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Tasks", "rhs_table":"tasks", "rhs_key":"parent_id", "relationship_type":"one-to-many", "relationship_role_column":"parent_type", "relationship_role_column_value":"Accounts"
                },
                "account_notes":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Notes", "rhs_table":"notes", "rhs_key":"parent_id", "relationship_type":"one-to-many", "relationship_role_column":"parent_type", "relationship_role_column_value":"Accounts"
                },
                "account_meetings":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Meetings", "rhs_table":"meetings", "rhs_key":"parent_id", "relationship_type":"one-to-many", "relationship_role_column":"parent_type", "relationship_role_column_value":"Accounts"
                },
                "account_calls":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Calls", "rhs_table":"calls", "rhs_key":"parent_id", "relationship_type":"one-to-many", "relationship_role_column":"parent_type", "relationship_role_column_value":"Accounts"
                },
                "account_emails":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Emails", "rhs_table":"emails", "rhs_key":"parent_id", "relationship_type":"one-to-many", "relationship_role_column":"parent_type", "relationship_role_column_value":"Accounts"
                },
                "account_leads":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"Leads", "rhs_table":"leads", "rhs_key":"account_id", "relationship_type":"one-to-many"
                },
                "account_campaign_log":{
                    "lhs_module":"Accounts", "lhs_table":"accounts", "lhs_key":"id", "rhs_module":"CampaignLog", "rhs_table":"campaign_log", "rhs_key":"target_id", "relationship_type":"one-to-many"
                }
            },
            "views":{
                "detail":{
                    "meta":{
                        "templateMeta":{
                            "maxColumns":"1", "widths":[
                                {"label":"10", "field":"30"}
                            ]
                        },
                        "panels":[
                            {"label":"LBL_PANEL_1", "fields":[
                                {"name":"name", "displayParams":{"required":true, "wireless_edit_only":true}},
                                "phone_office",
                                {"name":"website", "displayParams":{"type":"link"}},
                                "email1",
                                "billing_address_street",
                                "billing_address_city",
                                "billing_address_state",
                                "billing_address_postalcode",
                                "billing_address_country",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit":{
                    "meta":{
                        "templateMeta":{
                            "maxColumns":"1", "widths":[
                                {"label":"10", "field":"30"}
                            ]
                        },
                        "panels":[
                            {"label":"LBL_PANEL_1", "fields":[
                                {"name":"name", "displayParams":{"required":true, "wireless_edit_only":true}},
                                "phone_office",
                                {"name":"website", "displayParams":{"type":"link"}},
                                "email1",
                                "billing_address_street",
                                "billing_address_city",
                                "billing_address_state",
                                "billing_address_postalcode",
                                "billing_address_country",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list":{
                    "meta":{
                        "panels":[
                            {"label":"LBL_PANEL_1", "fields":[
                                {"name":"name", "label":"LBL_ACCOUNT_NAME", "link":true, "default":true, "enabled":true, "width":"40"},
                                {"name":"billing_address_city", "label":"LBL_CITY", "default":true, "enabled":true, "width":"10"},
                                {"name":"phone_office", "label":"LBL_PHONE", "default":true, "enabled":true, "width":"10"},
                                {"name":"account_type", "label":"LBL_TYPE", "enabled":true, "width":"10"},
                                {"name":"industry", "label":"LBL_INDUSTRY", "enabled":true, "width":"10"},
                                {"name":"annual_revenue", "label":"LBL_ANNUAL_REVENUE", "enabled":true, "width":"10"},
                                {"name":"phone_fax", "label":"LBL_PHONE_FAX", "enabled":true, "width":"10"},
                                {"name":"billing_address_street", "label":"LBL_BILLING_ADDRESS_STREET", "enabled":true, "width":"15"},
                                {"name":"billing_address_state", "label":"LBL_BILLING_ADDRESS_STATE", "enabled":true, "width":"7"},
                                {"name":"billing_address_postalcode", "label":"LBL_BILLING_ADDRESS_POSTALCODE", "enabled":true, "width":"10"},
                                {"name":"billing_address_country", "label":"LBL_BILLING_ADDRESS_COUNTRY", "enabled":true, "width":"10"},
                                {"name":"shipping_address_street", "label":"LBL_SHIPPING_ADDRESS_STREET", "enabled":true, "width":"15"},
                                {"name":"shipping_address_city", "label":"LBL_SHIPPING_ADDRESS_CITY", "enabled":true, "width":"10"},
                                {"name":"shipping_address_state", "label":"LBL_SHIPPING_ADDRESS_STATE", "enabled":true, "width":"7"},
                                {"name":"shipping_address_postalcode", "label":"LBL_SHIPPING_ADDRESS_POSTALCODE", "enabled":true, "width":"10"},
                                {"name":"shipping_address_country", "label":"LBL_SHIPPING_ADDRESS_COUNTRY", "enabled":true, "width":"10"},
                                {"name":"phone_alternate", "label":"LBL_PHONE_ALTERNATE", "enabled":true, "width":"10"},
                                {"name":"website", "label":"LBL_WEBSITE", "enabled":true, "width":"10"},
                                {"name":"ownership", "label":"LBL_OWNERSHIP", "enabled":true, "width":"10"},
                                {"name":"employees", "label":"LBL_EMPLOYEES", "enabled":true, "width":"10"},
                                {"name":"ticker_symbol", "label":"LBL_TICKER_SYMBOL", "enabled":true, "width":"10"},
                                {"name":"team_name", "label":"LBL_TEAM", "default":true, "enabled":true, "width":"2"},
                                {"name":"assigned_user_name", "label":"LBL_ASSIGNED_USER_NAME", "default":true, "enabled":true, "width":"2"}
                            ]}
                        ]
                    }
                },
                "search":{
                    "meta":{
                        "templateMeta":{
                            "maxColumns":"1", "widths":{
                                "label":"10", "field":"30"
                            }
                        },
                        "layout":{
                            "basic_search":["name"]
                        }
                    }
                }
            },
            "layouts":{
                "detail":{
                    "meta":{
                        "type":"detail", "components":[
                            {"view":"detail"}
                        ]
                    }
                },
                "edit":{
                    "meta":{
                        "type":"edit", "components":[
                            {"view":"edit"}
                        ]
                    }
                },
                "list":{
                    "meta":{
                        "type":"list", "components":[
                            {"view":"list"}
                        ]
                    }
                }
            },
            "_hash":"9027b88d2e215068b0bd961653ec7782"
        },
        "Calls":{
            "fields":{}
        },
        "Opportunities":{
            "fields":{
                "name":{
                    "name":"name",
                    "required":true
                },
                "account_name":{
                    "name":"account_name",
                    "rname":"name",
                    "id_name":"account_id",
                    "vname":"LBL_ACCOUNT_NAME",
                    "type":"relate",
                    "table":"accounts",
                    "join_name":"accounts",
                    "isnull":"true",
                    "module":"Accounts",
                    "dbType":"varchar",
                    "link":"accounts",
                    "len":20,
                    "source":"non-db",
                    "unified_search":true,
                    "importable":"required"
                },
                "account_id":{
                    "name":"account_id",
                    "vname":"LBL_ACCOUNT_ID",
                    "type":"id",
                    "source":"non-db",
                    "audited":true
                },
                "contacts":{
                    "name":"contacts",
                    "type":"link",
                    "relationship":"opportunities_contacts"
                },
                "accounts":{
                    "name":"accounts",
                    "type":"link",
                    "relationship":"accounts_opportunities"
                },
                "calls":{
                    "name":"calls",
                    "type":"link",
                    "relationship":"opportunity_calls"
                }

            },
            "relationships":{
                "opportunities_contacts":{
                    "lhs_module":"Opportunities",
                    "lhs_link":"contacts",
                    "rhs_module":"Contacts",
                    "rhs_link":"opportunities",
                    "relationship_type":"many-to-many"
                },
                "accounts_opportunities":{
                    "lhs_module":"Accounts",
                    "lhs_table":"accounts",
                    "lhs_link":"opportunities",
                    "rhs_module":"Opportunities",
                    "rhs_table":"opportunities",
                    "rhs_link":"opportunities",
                    "relationship_type":"one-to-many"
                },
                "opportunity_calls":{
                    "lhs_module":"Opportunities",
                    "lhs_link":"calls",
                    "rhs_module":"Calls",
                    "rhs_link":"opportunities",
                    "relationship_type":"one-to-many"
                }

            },
            "views":{
                "detail":{
                    "meta":{
                        "panels":[]
                    }
                }
            },
            "layouts":{
                "detail":{
                    "meta":{
                        "components":[
                            {
                                "view":"detail"
                            }
                        ]
                    }
                }
            }
        },
        "Contacts":{
            "fields":{
                "first_name":{
                    "name":"first_name",
                    "type":"varchar",
                    "len":20
                },
                "last_name":{
                    "name":"last_name",
                    "type":"varchar",
                    "len":20
                },
                "field_0":{
                    "name":"field_0",
                    "default":100
                }

            },
            "relationships":{

            },
            "views":{
                "EditView":{},
                "DetailView":{

                },
                "QuickCreate":{

                },
                "ListView":{

                },
                "SubpanelView":{

                }
            },
            "layouts":{
                "edit":{
                    "meta":{
                        "type":"simple",
                        "components":[
                            {
                                "view":"EditView"
                            }
                        ]
                    }
                },
                "detail":{
                    "meta":{
                        "components":"rows",
                        "views":[
                            {
                                "view":"DetailView"
                            },
                            {
                                "view":"SubpanelView"
                            }
                        ]
                    }
                },
                "list":{
                    "meta":{
                        "type":"simple",
                        "components":[
                            {
                                "view":"list"
                            }
                        ]
                    }
                },
                "sublayout":{
                    "meta":{
                        "type":"rows",
                        "components":[
                            {
                                "layout":{
                                    "type":"columns",
                                    "components":[
                                        {
                                            "view":"ListView"
                                        },
                                        {
                                            "view":"DetailView"
                                        }
                                    ]
                                }
                            },
                            {
                                "view":"SubpanelView"
                            }
                        ]
                    }
                },
                "complexlayout":{
                    "meta":{
                        "type":"columns",
                        "components":[
                            {
                                "view":"EditView"
                            },
                            {
                                "view":"DetailView",
                                "context":"accounts"
                            }
                        ]
                    }
                }
            }
        }

    },
    'moduleList':{
        Accounts: "Accounts",
        Calls: "Calls",
        Cases: "Cases",
        Contacts: "Contacts",
        Employees: "Employees",
        Leads: "Leads",
        Meetings: "Meetings",
        Opportunities: "Opportunities",
        Reports: "Reports",
        Tasks: "Tasks",
        '_hash':'dfl23asfd'
    },
    "_hash":"hash"
};