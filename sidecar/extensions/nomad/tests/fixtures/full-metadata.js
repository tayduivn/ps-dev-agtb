var full_meta = {
    "modules": {
        "Accounts": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "type": "name", "dbType": "varchar", "vname": "LBL_NAME", "len": 150, "comment": "Name of the Company", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "audited": true, "required": true, "importable": "required", "merge_filter": "selected"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "accounts_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "accounts_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "accounts_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "accounts_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "accounts_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "accounts_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "account_type": {
                    "name": "account_type", "vname": "LBL_TYPE", "type": "enum", "options": "account_type_dom", "len": 50, "comment": "The Company is of this type"
                },
                "industry": {
                    "name": "industry", "vname": "LBL_INDUSTRY", "type": "enum", "options": "industry_dom", "len": 50, "comment": "The company belongs in this industry", "merge_filter": "enabled"
                },
                "annual_revenue": {
                    "name": "annual_revenue", "vname": "LBL_ANNUAL_REVENUE", "type": "varchar", "len": 100, "comment": "Annual revenue for this company", "merge_filter": "enabled"
                },
                "phone_fax": {
                    "name": "phone_fax", "vname": "LBL_FAX", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "The fax phone number of this company"
                },
                "billing_address_street": {
                    "name": "billing_address_street", "vname": "LBL_BILLING_ADDRESS_STREET", "type": "varchar", "len": "150", "comment": "The street address used for billing address", "group": "billing_address", "merge_filter": "enabled"
                },
                "billing_address_street_2": {
                    "name": "billing_address_street_2", "vname": "LBL_BILLING_ADDRESS_STREET_2", "type": "varchar", "len": "150", "source": "non-db"
                },
                "billing_address_street_3": {
                    "name": "billing_address_street_3", "vname": "LBL_BILLING_ADDRESS_STREET_3", "type": "varchar", "len": "150", "source": "non-db"
                },
                "billing_address_street_4": {
                    "name": "billing_address_street_4", "vname": "LBL_BILLING_ADDRESS_STREET_4", "type": "varchar", "len": "150", "source": "non-db"
                },
                "billing_address_city": {
                    "name": "billing_address_city", "vname": "LBL_BILLING_ADDRESS_CITY", "type": "varchar", "len": "100", "comment": "The city used for billing address", "group": "billing_address", "merge_filter": "enabled"
                },
                "billing_address_state": {
                    "name": "billing_address_state", "vname": "LBL_BILLING_ADDRESS_STATE", "type": "varchar", "len": "100", "group": "billing_address", "comment": "The state used for billing address", "merge_filter": "enabled"
                },
                "billing_address_postalcode": {
                    "name": "billing_address_postalcode", "vname": "LBL_BILLING_ADDRESS_POSTALCODE", "type": "varchar", "len": "20", "group": "billing_address", "comment": "The postal code used for billing address", "merge_filter": "enabled"
                },
                "billing_address_country": {
                    "name": "billing_address_country", "vname": "LBL_BILLING_ADDRESS_COUNTRY", "type": "varchar", "group": "billing_address", "comment": "The country used for the billing address", "merge_filter": "enabled"
                },
                "rating": {
                    "name": "rating", "vname": "LBL_RATING", "type": "varchar", "len": 100, "comment": "An arbitrary rating for this company for use in comparisons with others"
                },
                "phone_office": {
                    "name": "phone_office", "vname": "LBL_PHONE_OFFICE", "type": "phone", "dbType": "varchar", "len": 100, "audited": true, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "The office phone number", "merge_filter": "enabled"
                },
                "phone_alternate": {
                    "name": "phone_alternate", "vname": "LBL_PHONE_ALT", "type": "phone", "group": "phone_office", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "An alternate phone number", "merge_filter": "enabled"
                },
                "website": {
                    "name": "website", "vname": "LBL_WEBSITE", "type": "url", "dbType": "varchar", "len": 255, "comment": "URL of website for the company"
                },
                "ownership": {
                    "name": "ownership", "vname": "LBL_OWNERSHIP", "type": "varchar", "len": 100, "comment": ""
                },
                "employees": {
                    "name": "employees", "vname": "LBL_EMPLOYEES", "type": "varchar", "len": 10, "comment": "Number of employees, varchar to accomodate for both number (100) or range (50-100)"
                },
                "ticker_symbol": {
                    "name": "ticker_symbol", "vname": "LBL_TICKER_SYMBOL", "type": "varchar", "len": 10, "comment": "The stock trading (ticker) symbol for the company", "merge_filter": "enabled"
                },
                "shipping_address_street": {
                    "name": "shipping_address_street", "vname": "LBL_SHIPPING_ADDRESS_STREET", "type": "varchar", "len": 150, "group": "shipping_address", "comment": "The street address used for for shipping purposes", "merge_filter": "enabled"
                },
                "shipping_address_street_2": {
                    "name": "shipping_address_street_2", "vname": "LBL_SHIPPING_ADDRESS_STREET_2", "type": "varchar", "len": 150, "source": "non-db"
                },
                "shipping_address_street_3": {
                    "name": "shipping_address_street_3", "vname": "LBL_SHIPPING_ADDRESS_STREET_3", "type": "varchar", "len": 150, "source": "non-db"
                },
                "shipping_address_street_4": {
                    "name": "shipping_address_street_4", "vname": "LBL_SHIPPING_ADDRESS_STREET_4", "type": "varchar", "len": 150, "source": "non-db"
                },
                "shipping_address_city": {
                    "name": "shipping_address_city", "vname": "LBL_SHIPPING_ADDRESS_CITY", "type": "varchar", "len": 100, "group": "shipping_address", "comment": "The city used for the shipping address", "merge_filter": "enabled"
                },
                "shipping_address_state": {
                    "name": "shipping_address_state", "vname": "LBL_SHIPPING_ADDRESS_STATE", "type": "varchar", "len": 100, "group": "shipping_address", "comment": "The state used for the shipping address", "merge_filter": "enabled"
                },
                "shipping_address_postalcode": {
                    "name": "shipping_address_postalcode", "vname": "LBL_SHIPPING_ADDRESS_POSTALCODE", "type": "varchar", "len": 20, "group": "shipping_address", "comment": "The zip code used for the shipping address", "merge_filter": "enabled"
                },
                "shipping_address_country": {
                    "name": "shipping_address_country", "vname": "LBL_SHIPPING_ADDRESS_COUNTRY", "type": "varchar", "group": "shipping_address", "comment": "The country used for the shipping address", "merge_filter": "enabled"
                },
                "email1": {
                    "name": "email1", "vname": "LBL_EMAIL", "group": "email1", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "studio": {
                        "editField": true, "searchview": false
                    }
                },
                "email_addresses_primary": {
                    "name": "email_addresses_primary", "type": "link", "relationship": "accounts_email_addresses_primary", "source": "non-db", "vname": "LBL_EMAIL_ADDRESS_PRIMARY", "duplicate_merge": "disabled", "studio": {
                        "formula": false
                    }
                },
                "email_addresses": {
                    "name": "email_addresses", "type": "link", "relationship": "accounts_email_addresses", "source": "non-db", "vname": "LBL_EMAIL_ADDRESSES", "reportable": false, "unified_search": true, "rel_fields": {
                        "primary_address": {
                            "type": "bool"
                        }
                    },
                    "studio": {
                        "formula": false
                    }
                },
                "parent_id": {
                    "name": "parent_id", "vname": "LBL_PARENT_ACCOUNT_ID", "type": "id", "required": false, "reportable": false, "audited": true, "comment": "Account ID of the parent of this account"
                },
                "sic_code": {
                    "name": "sic_code", "vname": "LBL_SIC_CODE", "type": "varchar", "len": 10, "comment": "SIC code of the account", "merge_filter": "enabled"
                },
                "parent_name": {
                    "name": "parent_name", "rname": "name", "id_name": "parent_id", "vname": "LBL_MEMBER_OF", "type": "relate", "isnull": "true", "module": "Accounts", "table": "accounts", "massupdate": false, "source": "non-db", "len": 36, "link": "member_of", "unified_search": true, "importable": "true"
                },
                "members": {
                    "name": "members", "type": "link", "relationship": "member_accounts", "module": "Accounts", "bean_name": "Account", "source": "non-db", "vname": "LBL_MEMBERS"
                },
                "member_of": {
                    "name": "member_of", "type": "link", "relationship": "member_accounts", "module": "Accounts", "bean_name": "Account", "link_type": "one", "source": "non-db", "vname": "LBL_MEMBER_OF", "side": "right"
                },
                "email_opt_out": {
                    "name": "email_opt_out", "vname": "LBL_EMAIL_OPT_OUT", "source": "non-db", "type": "bool", "massupdate": false, "studio": "false"
                },
                "invalid_email": {
                    "name": "invalid_email", "vname": "LBL_INVALID_EMAIL", "source": "non-db", "type": "bool", "massupdate": false, "studio": "false"
                },
                "cases": {
                    "name": "cases", "type": "link", "relationship": "account_cases", "module": "Cases", "bean_name": "aCase", "source": "non-db", "vname": "LBL_CASES"
                },
                "email": {
                    "name": "email", "type": "email", "query_type": "default", "source": "non-db", "operator": "subquery", "subquery": "SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE", "db_field": ["id"], "vname": "LBL_ANY_EMAIL", "studio": {
                        "visible": false, "searchview": true
                    }
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "account_tasks", "module": "Tasks", "bean_name": "Task", "source": "non-db", "vname": "LBL_TASKS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "account_notes", "module": "Notes", "bean_name": "Note", "source": "non-db", "vname": "LBL_NOTES"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "account_meetings", "module": "Meetings", "bean_name": "Meeting", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "account_calls", "module": "Calls", "bean_name": "Call", "source": "non-db", "vname": "LBL_CALLS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_accounts_rel", "module": "Emails", "bean_name": "Email", "source": "non-db", "vname": "LBL_EMAILS", "studio": {
                        "formula": false
                    }
                },
                "documents": {
                    "name": "documents", "type": "link", "relationship": "documents_accounts", "source": "non-db", "vname": "LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "bugs": {
                    "name": "bugs", "type": "link", "relationship": "accounts_bugs", "module": "Bugs", "bean_name": "Bug", "source": "non-db", "vname": "LBL_BUGS"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "accounts_contacts", "module": "Contacts", "bean_name": "Contact", "source": "non-db", "vname": "LBL_CONTACTS"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "accounts_opportunities", "module": "Opportunities", "bean_name": "Opportunity", "source": "non-db", "vname": "LBL_OPPORTUNITY"
                },
                "quotes": {
                    "name": "quotes", "type": "link", "relationship": "quotes_billto_accounts", "source": "non-db", "module": "Quotes", "bean_name": "Quote", "ignore_role": true, "vname": "LBL_QUOTES"
                },
                "quotes_shipto": {
                    "name": "quotes_shipto", "type": "link", "relationship": "quotes_shipto_accounts", "module": "Quotes", "bean_name": "Quote", "source": "non-db", "vname": "LBL_QUOTES_SHIP_TO"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_accounts", "module": "Project", "bean_name": "Project", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "account_leads", "module": "Leads", "bean_name": "Lead", "source": "non-db", "vname": "LBL_LEADS"
                },
                "campaigns": {
                    "name": "campaigns", "type": "link", "relationship": "account_campaign_log", "module": "CampaignLog", "bean_name": "CampaignLog", "source": "non-db", "vname": "LBL_CAMPAIGNLOG", "studio": {
                        "formula": false
                    }
                },
                "campaign_accounts": {
                    "name": "campaign_accounts", "type": "link", "vname": "LBL_CAMPAIGNS", "relationship": "campaign_accounts", "source": "non-db"
                },
                "products": {
                    "name": "products", "type": "link", "relationship": "products_accounts", "source": "non-db", "vname": "LBL_PRODUCTS"
                },
                "contracts": {
                    "name": "contracts", "type": "link", "relationship": "account_contracts", "source": "non-db", "vname": "LBL_CONTRACTS"
                },
                "campaign_id": {
                    "name": "campaign_id", "comment": "Campaign that generated Account", "vname": "LBL_CAMPAIGN_ID", "rname": "id", "id_name": "campaign_id", "type": "id", "table": "campaigns", "isnull": "true", "module": "Campaigns", "reportable": false, "massupdate": false, "duplicate_merge": "disabled"
                },
                "campaign_name": {
                    "name": "campaign_name", "rname": "name", "vname": "LBL_CAMPAIGN", "type": "relate", "reportable": false, "source": "non-db", "table": "campaigns", "id_name": "campaign_id", "link": "campaign_accounts", "module": "Campaigns", "duplicate_merge": "disabled", "comment": "The first campaign name for Account (Meta-data only)"
                },
                "prospect_lists": {
                    "name": "prospect_lists", "type": "link", "relationship": "prospect_list_accounts", "module": "ProspectLists", "source": "non-db", "vname": "LBL_PROSPECT_LIST"
                },
                "currency_id": {
                    "required": false, "source": "custom_fields", "name": "currency_id", "vname": "LBL_CURRENCY", "type": "currency_id", "massupdate": "0", "default": null, "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "len": "36", "size": "20", "dbType": "id", "studio": "visible", "function": {
                        "name": "getCurrencyDropDown", "returns": "html"
                    },
                    "id": "Accountscurrency_id", "custom_module": "Accounts"
                },
                "field1_c": {
                    "required": false, "source": "custom_fields", "name": "field1_c", "vname": "LBL_FIELD1", "type": "bool", "massupdate": "0", "default": "0", "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "len": "255", "size": "20", "id": "Accountsfield1_c", "custom_module": "Accounts"
                },
                "field2_c": {
                    "required": false, "source": "custom_fields", "name": "field2_c", "vname": "LBL_FIELD2", "type": "currency", "massupdate": "0", "default": "", "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "len": "26", "size": "20", "enable_range_search": false, "precision": 6, "id": "Accountsfield2_c", "custom_module": "Accounts"
                },
                "field3_c": {
                    "required": false, "source": "custom_fields", "name": "field3_c", "vname": "LBL_FIELD3", "type": "decimal", "massupdate": "0", "default": "", "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "len": "18", "size": "20", "enable_range_search": false, "precision": "8", "id": "Accountsfield3_c", "custom_module": "Accounts"
                },
                "field4_c": {
                    "required": false, "source": "custom_fields", "name": "field4_c", "vname": "LBL_FIELD4", "type": "float", "massupdate": "0", "default": "", "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "len": "18", "size": "20", "enable_range_search": false, "precision": "8", "id": "Accountsfield4_c", "custom_module": "Accounts"
                },
                "field_multi_c": {
                    "required": false, "source": "custom_fields", "name": "field_multi_c", "vname": "LBL_FIELD_MULTI", "type": "multienum", "massupdate": "0", "default": null, "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "size": "20", "options": "call_status_dom", "studio": "visible", "dependency": "", "isMultiSelect": true, "id": "Accountsfield_multi_c", "custom_module": "Accounts"
                },
                "field_radio_c": {
                    "required": false, "source": "custom_fields", "name": "field_radio_c", "vname": "LBL_FIELD_RADIO", "type": "radioenum", "massupdate": "0", "default": "Planned", "comments": "", "help": "", "importable": "true", "duplicate_merge": "disabled", "duplicate_merge_dom_value": "0", "audited": false, "reportable": true, "unified_search": false, "merge_filter": "disabled", "calculated": false, "len": 100, "size": "20", "options": "call_status_dom", "studio": "visible", "dbType": "enum", "separator": "<br>", "id": "Accountsfield_radio_c", "custom_module": "Accounts"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "phone_office",
                                {"name": "website", "displayParams": {"type": "link"}},
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
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "phone_office",
                                {"name": "website", "displayParams": {"type": "link"}},
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
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_ACCOUNT_NAME", "link": true, "default": true, "enabled": true, "width": "40"},
                                {"name": "billing_address_city", "label": "LBL_CITY", "default": true, "enabled": true, "width": "10"},
                                {"name": "phone_office", "label": "LBL_PHONE", "default": true, "enabled": true, "width": "10"},
                                {"name": "account_type", "label": "LBL_TYPE", "enabled": true, "width": "10"},
                                {"name": "industry", "label": "LBL_INDUSTRY", "enabled": true, "width": "10"},
                                {"name": "annual_revenue", "label": "LBL_ANNUAL_REVENUE", "enabled": true, "width": "10"},
                                {"name": "phone_fax", "label": "LBL_PHONE_FAX", "enabled": true, "width": "10"},
                                {"name": "billing_address_street", "label": "LBL_BILLING_ADDRESS_STREET", "enabled": true, "width": "15"},
                                {"name": "billing_address_state", "label": "LBL_BILLING_ADDRESS_STATE", "enabled": true, "width": "7"},
                                {"name": "billing_address_postalcode", "label": "LBL_BILLING_ADDRESS_POSTALCODE", "enabled": true, "width": "10"},
                                {"name": "billing_address_country", "label": "LBL_BILLING_ADDRESS_COUNTRY", "enabled": true, "width": "10"},
                                {"name": "shipping_address_street", "label": "LBL_SHIPPING_ADDRESS_STREET", "enabled": true, "width": "15"},
                                {"name": "shipping_address_city", "label": "LBL_SHIPPING_ADDRESS_CITY", "enabled": true, "width": "10"},
                                {"name": "shipping_address_state", "label": "LBL_SHIPPING_ADDRESS_STATE", "enabled": true, "width": "7"},
                                {"name": "shipping_address_postalcode", "label": "LBL_SHIPPING_ADDRESS_POSTALCODE", "enabled": true, "width": "10"},
                                {"name": "shipping_address_country", "label": "LBL_SHIPPING_ADDRESS_COUNTRY", "enabled": true, "width": "10"},
                                {"name": "phone_alternate", "label": "LBL_PHONE_ALTERNATE", "enabled": true, "width": "10"},
                                {"name": "website", "label": "LBL_WEBSITE", "enabled": true, "width": "10"},
                                {"name": "ownership", "label": "LBL_OWNERSHIP", "enabled": true, "width": "10"},
                                {"name": "employees", "label": "LBL_EMPLOYEES", "enabled": true, "width": "10"},
                                {"name": "ticker_symbol", "label": "LBL_TICKER_SYMBOL", "enabled": true, "width": "10"},
                                {"name": "team_name", "label": "LBL_TEAM", "default": true, "enabled": true, "width": "2"},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_USER_NAME", "default": true, "enabled": true, "width": "2"}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "443bbfc4d0d8656473cebcf9d85015d5"
        },
        "Contacts": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "rname": "name", "vname": "LBL_NAME", "type": "name", "link": true, "fields": ["first_name", "last_name"], "sort_on": "last_name", "source": "non-db", "group": "last_name", "len": "255", "db_concat_fields": ["first_name", "last_name"], "importable": "false"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "contacts_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "contacts_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "contacts_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "rname": "user_name", "id_name": "assigned_user_id", "table": "users", "duplicate_merge": "enabled"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "contacts_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "contacts_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "contacts_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "salutation": {
                    "name": "salutation", "vname": "LBL_SALUTATION", "type": "enum", "options": "salutation_dom", "massupdate": false, "len": "255", "comment": "Contact salutation (e.g., Mr, Ms)"
                },
                "first_name": {
                    "name": "first_name", "vname": "LBL_FIRST_NAME", "type": "varchar", "len": "100", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "First name of the contact", "merge_filter": "selected"
                },
                "last_name": {
                    "name": "last_name", "vname": "LBL_LAST_NAME", "type": "varchar", "len": "100", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "Last name of the contact", "merge_filter": "selected", "required": true, "importable": "required"
                },
                "full_name": {
                    "name": "full_name", "rname": "full_name", "vname": "LBL_NAME", "type": "fullname", "fields": ["first_name", "last_name"], "sort_on": "last_name", "source": "non-db", "group": "last_name", "len": "510", "db_concat_fields": ["first_name", "last_name"], "studio": {
                        "listview": false
                    }
                },
                "title": {
                    "name": "title", "vname": "LBL_TITLE", "type": "varchar", "len": "100", "comment": "The title of the contact"
                },
                "department": {
                    "name": "department", "vname": "LBL_DEPARTMENT", "type": "varchar", "len": "255", "comment": "The department of the contact", "merge_filter": "enabled"
                },
                "do_not_call": {
                    "name": "do_not_call", "vname": "LBL_DO_NOT_CALL", "type": "bool", "default": "0", "audited": true, "comment": "An indicator of whether contact can be called"
                },
                "phone_home": {
                    "name": "phone_home", "vname": "LBL_HOME_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Home phone number of the contact", "merge_filter": "enabled"
                },
                "email": {
                    "name": "email", "type": "email", "query_type": "default", "source": "non-db", "operator": "subquery", "subquery": "SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE", "db_field": ["id"], "vname": "LBL_ANY_EMAIL", "studio": {
                        "visible": false, "searchview": true
                    }
                },
                "phone_mobile": {
                    "name": "phone_mobile", "vname": "LBL_MOBILE_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Mobile phone number of the contact", "merge_filter": "enabled"
                },
                "phone_work": {
                    "name": "phone_work", "vname": "LBL_OFFICE_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "audited": true, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Work phone number of the contact", "merge_filter": "enabled"
                },
                "phone_other": {
                    "name": "phone_other", "vname": "LBL_OTHER_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Other phone number for the contact", "merge_filter": "enabled"
                },
                "phone_fax": {
                    "name": "phone_fax", "vname": "LBL_FAX_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Contact fax number", "merge_filter": "enabled"
                },
                "email1": {
                    "name": "email1", "vname": "LBL_EMAIL_ADDRESS", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "group": "email1", "merge_filter": "enabled", "studio": {
                        "editField": true, "searchview": false, "popupsearch": false
                    }
                },
                "email2": {
                    "name": "email2", "vname": "LBL_OTHER_EMAIL_ADDRESS", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "group": "email2", "merge_filter": "enabled", "studio": "false"
                },
                "invalid_email": {
                    "name": "invalid_email", "vname": "LBL_INVALID_EMAIL", "source": "non-db", "type": "bool", "massupdate": false, "studio": "false"
                },
                "email_opt_out": {
                    "name": "email_opt_out", "vname": "LBL_EMAIL_OPT_OUT", "source": "non-db", "type": "bool", "massupdate": false, "studio": "false"
                },
                "primary_address_street": {
                    "name": "primary_address_street", "vname": "LBL_PRIMARY_ADDRESS_STREET", "type": "varchar", "len": "150", "group": "primary_address", "comment": "Street address for primary address", "merge_filter": "enabled"
                },
                "primary_address_street_2": {
                    "name": "primary_address_street_2", "vname": "LBL_PRIMARY_ADDRESS_STREET_2", "type": "varchar", "len": "150", "source": "non-db"
                },
                "primary_address_street_3": {
                    "name": "primary_address_street_3", "vname": "LBL_PRIMARY_ADDRESS_STREET_3", "type": "varchar", "len": "150", "source": "non-db"
                },
                "primary_address_city": {
                    "name": "primary_address_city", "vname": "LBL_PRIMARY_ADDRESS_CITY", "type": "varchar", "len": "100", "group": "primary_address", "comment": "City for primary address", "merge_filter": "enabled"
                },
                "primary_address_state": {
                    "name": "primary_address_state", "vname": "LBL_PRIMARY_ADDRESS_STATE", "type": "varchar", "len": "100", "group": "primary_address", "comment": "State for primary address", "merge_filter": "enabled"
                },
                "primary_address_postalcode": {
                    "name": "primary_address_postalcode", "vname": "LBL_PRIMARY_ADDRESS_POSTALCODE", "type": "varchar", "len": "20", "group": "primary_address", "comment": "Postal code for primary address", "merge_filter": "enabled"
                },
                "primary_address_country": {
                    "name": "primary_address_country", "vname": "LBL_PRIMARY_ADDRESS_COUNTRY", "type": "varchar", "group": "primary_address", "comment": "Country for primary address", "merge_filter": "enabled"
                },
                "alt_address_street": {
                    "name": "alt_address_street", "vname": "LBL_ALT_ADDRESS_STREET", "type": "varchar", "len": "150", "group": "alt_address", "comment": "Street address for alternate address", "merge_filter": "enabled"
                },
                "alt_address_street_2": {
                    "name": "alt_address_street_2", "vname": "LBL_ALT_ADDRESS_STREET_2", "type": "varchar", "len": "150", "source": "non-db"
                },
                "alt_address_street_3": {
                    "name": "alt_address_street_3", "vname": "LBL_ALT_ADDRESS_STREET_3", "type": "varchar", "len": "150", "source": "non-db"
                },
                "alt_address_city": {
                    "name": "alt_address_city", "vname": "LBL_ALT_ADDRESS_CITY", "type": "varchar", "len": "100", "group": "alt_address", "comment": "City for alternate address", "merge_filter": "enabled"
                },
                "alt_address_state": {
                    "name": "alt_address_state", "vname": "LBL_ALT_ADDRESS_STATE", "type": "varchar", "len": "100", "group": "alt_address", "comment": "State for alternate address", "merge_filter": "enabled"
                },
                "alt_address_postalcode": {
                    "name": "alt_address_postalcode", "vname": "LBL_ALT_ADDRESS_POSTALCODE", "type": "varchar", "len": "20", "group": "alt_address", "comment": "Postal code for alternate address", "merge_filter": "enabled"
                },
                "alt_address_country": {
                    "name": "alt_address_country", "vname": "LBL_ALT_ADDRESS_COUNTRY", "type": "varchar", "group": "alt_address", "comment": "Country for alternate address", "merge_filter": "enabled"
                },
                "assistant": {
                    "name": "assistant", "vname": "LBL_ASSISTANT", "type": "varchar", "len": "75", "unified_search": true, "full_text_search": {
                        "boost": 2
                    },
                    "comment": "Name of the assistant of the contact", "merge_filter": "enabled"
                },
                "assistant_phone": {
                    "name": "assistant_phone", "vname": "LBL_ASSISTANT_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "group": "assistant", "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Phone number of the assistant of the contact", "merge_filter": "enabled"
                },
                "email_addresses_primary": {
                    "name": "email_addresses_primary", "type": "link", "relationship": "contacts_email_addresses_primary", "source": "non-db", "vname": "LBL_EMAIL_ADDRESS_PRIMARY", "duplicate_merge": "disabled"
                },
                "email_addresses": {
                    "name": "email_addresses", "type": "link", "relationship": "contacts_email_addresses", "module": "EmailAddress", "bean_name": "EmailAddress", "source": "non-db", "vname": "LBL_EMAIL_ADDRESSES", "reportable": false, "rel_fields": {
                        "primary_address": {
                            "type": "bool"
                        }
                    },
                    "unified_search": true
                },
                "picture": {
                    "name": "picture", "vname": "LBL_PICTURE_FILE", "type": "image", "dbtype": "varchar", "massupdate": false, "reportable": false, "comment": "Picture file", "len": "255", "width": "120", "height": "", "border": ""
                },
                "email_and_name1": {
                    "name": "email_and_name1", "rname": "email_and_name1", "vname": "LBL_NAME", "type": "varchar", "source": "non-db", "len": "510", "importable": "false"
                },
                "lead_source": {
                    "name": "lead_source", "vname": "LBL_LEAD_SOURCE", "type": "enum", "options": "lead_source_dom", "len": "255", "comment": "How did the contact come about", "merge_filter": "enabled"
                },
                "account_name": {
                    "name": "account_name", "rname": "name", "id_name": "account_id", "vname": "LBL_ACCOUNT_NAME", "join_name": "accounts", "type": "relate", "link": "accounts", "table": "accounts", "isnull": "true", "module": "Accounts", "dbType": "varchar", "len": "255", "source": "non-db", "unified_search": true
                },
                "account_id": {
                    "name": "account_id", "rname": "id", "id_name": "account_id", "vname": "LBL_ACCOUNT_ID", "type": "relate", "table": "accounts", "isnull": "true", "module": "Accounts", "dbType": "id", "reportable": false, "source": "non-db", "massupdate": false, "duplicate_merge": "disabled", "hideacl": true
                },
                "opportunity_role_fields": {
                    "name": "opportunity_role_fields", "rname": "id", "relationship_fields": {
                        "id": "opportunity_role_id", "contact_role": "opportunity_role"
                    },
                    "vname": "LBL_ACCOUNT_NAME", "type": "relate", "link": "opportunities", "link_type": "relationship_info", "join_link_name": "opportunities_contacts", "source": "non-db", "importable": "false", "duplicate_merge": "disabled", "studio": false
                },
                "opportunity_role_id": {
                    "name": "opportunity_role_id", "type": "varchar", "source": "non-db", "vname": "LBL_OPPORTUNITY_ROLE_ID", "studio": {
                        "listview": false
                    }
                },
                "opportunity_role": {
                    "name": "opportunity_role", "type": "enum", "source": "non-db", "vname": "LBL_OPPORTUNITY_ROLE", "options": "opportunity_relationship_type_dom"
                },
                "reports_to_id": {
                    "name": "reports_to_id", "vname": "LBL_REPORTS_TO_ID", "type": "id", "required": false, "reportable": false, "comment": "The contact this contact reports to"
                },
                "report_to_name": {
                    "name": "report_to_name", "rname": "last_name", "id_name": "reports_to_id", "vname": "LBL_REPORTS_TO", "type": "relate", "link": "reports_to_link", "table": "contacts", "isnull": "true", "module": "Contacts", "dbType": "varchar", "len": "id", "reportable": false, "source": "non-db"
                },
                "birthdate": {
                    "name": "birthdate", "vname": "LBL_BIRTHDATE", "massupdate": false, "type": "date", "comment": "The birthdate of the contact"
                },
                "portal_name": {
                    "name": "portal_name", "vname": "LBL_PORTAL_NAME", "type": "varchar", "len": "255", "group": "portal", "comment": "Name as it appears in the portal"
                },
                "portal_active": {
                    "name": "portal_active", "vname": "LBL_PORTAL_ACTIVE", "type": "bool", "default": "0", "group": "portal", "comment": "Indicator whether this contact is a portal user"
                },
                "portal_password": {
                    "name": "portal_password", "vname": "LBL_USER_PASSWORD", "type": "password", "dbType": "varchar", "len": "255", "group": "portal", "reportable": false, "studio": {
                        "listview": false
                    }
                },
                "portal_password1": {
                    "name": "portal_password1", "vname": "LBL_USER_PASSWORD", "type": "varchar", "source": "non-db", "len": "255", "group": "portal", "reportable": false, "importable": "false", "studio": {
                        "listview": false
                    }
                },
                "portal_app": {
                    "name": "portal_app", "vname": "LBL_PORTAL_APP", "type": "varchar", "group": "portal", "len": "255", "comment": "Reference to the portal"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "accounts_contacts", "link_type": "one", "source": "non-db", "vname": "LBL_ACCOUNT", "duplicate_merge": "disabled"
                },
                "reports_to_link": {
                    "name": "reports_to_link", "type": "link", "relationship": "contact_direct_reports", "link_type": "one", "side": "right", "source": "non-db", "vname": "LBL_REPORTS_TO"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "opportunities_contacts", "source": "non-db", "module": "Opportunities", "bean_name": "Opportunity", "vname": "LBL_OPPORTUNITIES"
                },
                "bugs": {
                    "name": "bugs", "type": "link", "relationship": "contacts_bugs", "source": "non-db", "vname": "LBL_BUGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "calls_contacts", "source": "non-db", "vname": "LBL_CALLS"
                },
                "cases": {
                    "name": "cases", "type": "link", "relationship": "contacts_cases", "source": "non-db", "vname": "LBL_CASES"
                },
                "direct_reports": {
                    "name": "direct_reports", "type": "link", "relationship": "contact_direct_reports", "source": "non-db", "vname": "LBL_DIRECT_REPORTS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_contacts_rel", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "documents": {
                    "name": "documents", "type": "link", "relationship": "documents_contacts", "source": "non-db", "vname": "LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "contact_leads", "source": "non-db", "vname": "LBL_LEADS"
                },
                "products": {
                    "name": "products", "type": "link", "relationship": "contact_products", "source": "non-db", "vname": "LBL_PRODUCTS_TITLE"
                },
                "contracts": {
                    "name": "contracts", "type": "link", "vname": "LBL_CONTRACTS", "relationship": "contracts_contacts", "source": "non-db"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "meetings_contacts", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "contact_notes", "source": "non-db", "vname": "LBL_NOTES"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_contacts", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "project_resource": {
                    "name": "project_resource", "type": "link", "relationship": "projects_contacts_resources", "source": "non-db", "vname": "LBL_PROJECTS_RESOURCES"
                },
                "quotes": {
                    "name": "quotes", "type": "link", "relationship": "quotes_contacts_shipto", "source": "non-db", "ignore_role": "true", "module": "Quotes", "bean_name": "Quote", "vname": "LBL_QUOTES_SHIP_TO"
                },
                "billing_quotes": {
                    "name": "billing_quotes", "type": "link", "relationship": "quotes_contacts_billto", "source": "non-db", "ignore_role": "true", "module": "Quotes", "bean_name": "Quote", "vname": "LBL_QUOTES_BILL_TO"
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "contact_tasks", "source": "non-db", "vname": "LBL_TASKS"
                },
                "tasks_parent": {
                    "name": "tasks_parent", "type": "link", "relationship": "contact_tasks_parent", "source": "non-db", "vname": "LBL_TASKS", "reportable": false
                },
                "user_sync": {
                    "name": "user_sync", "type": "link", "relationship": "contacts_users", "source": "non-db", "vname": "LBL_USER_SYNC"
                },
                "campaign_id": {
                    "name": "campaign_id", "comment": "Campaign that generated lead", "vname": "LBL_CAMPAIGN_ID", "rname": "id", "id_name": "campaign_id", "type": "id", "table": "campaigns", "isnull": "true", "module": "Campaigns", "massupdate": false, "duplicate_merge": "disabled"
                },
                "campaign_name": {
                    "name": "campaign_name", "rname": "name", "vname": "LBL_CAMPAIGN", "type": "relate", "link": "campaign_contacts", "isnull": "true", "reportable": false, "source": "non-db", "table": "campaigns", "id_name": "campaign_id", "module": "Campaigns", "duplicate_merge": "disabled", "comment": "The first campaign name for Contact (Meta-data only)"
                },
                "campaigns": {
                    "name": "campaigns", "type": "link", "relationship": "contact_campaign_log", "module": "CampaignLog", "bean_name": "CampaignLog", "source": "non-db", "vname": "LBL_CAMPAIGNLOG"
                },
                "campaign_contacts": {
                    "name": "campaign_contacts", "type": "link", "vname": "LBL_CAMPAIGN_CONTACT", "relationship": "campaign_contacts", "source": "non-db"
                },
                "c_accept_status_fields": {
                    "name": "c_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "calls", "link_type": "relationship_info", "source": "non-db", "importable": "false", "duplicate_merge": "disabled", "studio": false
                },
                "m_accept_status_fields": {
                    "name": "m_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "meetings", "link_type": "relationship_info", "source": "non-db", "importable": "false", "hideacl": true, "duplicate_merge": "disabled", "studio": false
                },
                "accept_status_id": {
                    "name": "accept_status_id", "type": "varchar", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "studio": {
                        "listview": false
                    }
                },
                "accept_status_name": {
                    "massupdate": false, "name": "accept_status_name", "type": "enum", "studio": "false", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "options": "dom_meeting_accept_status", "importable": "false"
                },
                "prospect_lists": {
                    "name": "prospect_lists", "type": "link", "relationship": "prospect_list_contacts", "module": "ProspectLists", "source": "non-db", "vname": "LBL_PROSPECT_LIST"
                },
                "sync_contact": {
                    "massupdate": false, "name": "sync_contact", "vname": "LBL_SYNC_CONTACT", "type": "bool", "source": "non-db", "comment": "Synch to outlook?  (Meta-Data only)", "studio": "true"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "first_name", "customCode": "{html_options name=\"salutation\" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name=\"first_name\" size=\"15\" maxlength=\"25\" type=\"text\" value=\"{$fields.first_name.value}\">", "displayParams": {"wireless_edit_only": true}},
                                {"name": "last_name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "title",
                                "account_name",
                                "phone_work",
                                "phone_mobile",
                                "email1",
                                "primary_address_street",
                                "primary_address_city",
                                "primary_address_state",
                                "primary_address_postalcode",
                                "primary_address_country",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "first_name", "customCode": "{html_options name=\"salutation\" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name=\"first_name\" size=\"15\" maxlength=\"25\" type=\"text\" value=\"{$fields.first_name.value}\">", "displayParams": {"wireless_edit_only": true}},
                                {"name": "last_name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "title",
                                "account_name",
                                "phone_work",
                                "phone_mobile",
                                "email1",
                                "primary_address_street",
                                "primary_address_city",
                                "primary_address_state",
                                "primary_address_postalcode",
                                "primary_address_country",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true, "related_fields": ["first_name", "last_name", "salutation"]},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["last_name", "first_name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "1cde7d906aa5d6fb880d03a986490a89"
        },
        "Bugs": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_SUBJECT", "type": "name", "link": true, "dbType": "varchar", "len": 255, "audited": true, "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "The short description of the bug", "merge_filter": "selected", "required": true, "importable": "required"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "bugs_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "bugs_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "bugs_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "bugs_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "bugs_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "bugs_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "bug_number": {
                    "name": "bug_number", "vname": "LBL_NUMBER", "type": "int", "readonly": true, "len": 11, "required": true, "auto_increment": true, "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "Visual unique identifier", "duplicate_merge": "disabled", "disable_num_format": true
                },
                "type": {
                    "name": "type", "vname": "LBL_TYPE", "type": "enum", "options": "bug_type_dom", "len": 255, "comment": "The type of issue (ex: issue, feature)", "merge_filter": "enabled"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "options": "bug_status_dom", "len": 100, "audited": true, "comment": "The status of the issue", "merge_filter": "enabled"
                },
                "priority": {
                    "name": "priority", "vname": "LBL_PRIORITY", "type": "enum", "options": "bug_priority_dom", "len": 100, "audited": true, "comment": "An indication of the priorty of the issue", "merge_filter": "enabled"
                },
                "resolution": {
                    "name": "resolution", "vname": "LBL_RESOLUTION", "type": "enum", "options": "bug_resolution_dom", "len": 255, "audited": true, "comment": "An indication of how the issue was resolved", "merge_filter": "enabled"
                },
                "system_id": {
                    "name": "system_id", "vname": "LBL_SYSTEM_ID", "type": "int", "comment": "The offline client device that created the bug"
                },
                "work_log": {
                    "name": "work_log", "vname": "LBL_WORK_LOG", "type": "text", "comment": "Free-form text used to denote activities of interest"
                },
                "found_in_release": {
                    "name": "found_in_release", "type": "enum", "function": "getReleaseDropDown", "vname": "LBL_FOUND_IN_RELEASE", "reportable": false, "comment": "The software or service release that manifested the bug", "duplicate_merge": "disabled", "audited": true, "studio": {
                        "fields": "false", "listview": false, "wirelesslistview": false
                    },
                    "massupdate": true
                },
                "release_name": {
                    "name": "release_name", "rname": "name", "vname": "LBL_FOUND_IN_RELEASE", "type": "relate", "dbType": "varchar", "group": "found_in_release", "reportable": false, "source": "non-db", "table": "releases", "merge_filter": "enabled", "id_name": "found_in_release", "module": "Releases", "link": "release_link", "massupdate": false, "studio": {
                        "editview": false, "detailview": false, "quickcreate": false, "basic_search": false, "advanced_search": false, "wirelesseditview": false, "wirelessdetailview": false, "wirelesslistview": "visible", "wireless_basic_search": false, "wireless_advanced_search": false
                    }
                },
                "fixed_in_release": {
                    "name": "fixed_in_release", "type": "enum", "function": "getReleaseDropDown", "vname": "LBL_FIXED_IN_RELEASE", "reportable": false, "comment": "The software or service release that corrected the bug", "duplicate_merge": "disabled", "audited": true, "studio": {
                        "fields": "false", "listview": false, "wirelesslistview": false
                    },
                    "massupdate": true
                },
                "fixed_in_release_name": {
                    "name": "fixed_in_release_name", "rname": "name", "group": "fixed_in_release", "id_name": "fixed_in_release", "vname": "LBL_FIXED_IN_RELEASE", "type": "relate", "table": "releases", "isnull": "false", "massupdate": false, "module": "Releases", "dbType": "varchar", "len": 36, "source": "non-db", "link": "fixed_in_release_link", "studio": {
                        "editview": false, "detailview": false, "quickcreate": false, "basic_search": false, "advanced_search": false, "wirelesseditview": false, "wirelessdetailview": false, "wirelesslistview": "visible", "wireless_basic_search": false, "wireless_advanced_search": false
                    }
                },
                "source": {
                    "name": "source", "vname": "LBL_SOURCE", "type": "enum", "options": "source_dom", "len": 255, "comment": "An indicator of how the bug was entered (ex: via web, email, etc.)"
                },
                "product_category": {
                    "name": "product_category", "vname": "LBL_PRODUCT_CATEGORY", "type": "enum", "options": "product_category_dom", "len": 255, "comment": "Where the bug was discovered (ex: Accounts, Contacts, Leads)"
                },
                "portal_viewable": {
                    "name": "portal_viewable", "vname": "LBL_SHOW_IN_PORTAL", "type": "bool", "default": 0, "reportable": false
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "bug_tasks", "source": "non-db", "vname": "LBL_TASKS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "bug_notes", "source": "non-db", "vname": "LBL_NOTES"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "bug_meetings", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "bug_calls", "source": "non-db", "vname": "LBL_CALLS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_bugs_rel", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "documents": {
                    "name": "documents", "type": "link", "relationship": "documents_bugs", "source": "non-db", "vname": "LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "contacts_bugs", "source": "non-db", "vname": "LBL_CONTACTS"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "accounts_bugs", "source": "non-db", "vname": "LBL_ACCOUNTS"
                },
                "cases": {
                    "name": "cases", "type": "link", "relationship": "cases_bugs", "source": "non-db", "vname": "LBL_CASES"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_bugs", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "release_link": {
                    "name": "release_link", "type": "link", "relationship": "bugs_release", "vname": "LBL_FOUND_IN_RELEASE", "link_type": "one", "module": "Releases", "bean_name": "Release", "source": "non-db"
                },
                "fixed_in_release_link": {
                    "name": "fixed_in_release_link", "type": "link", "relationship": "bugs_fixed_in_release", "vname": "LBL_FIXED_IN_RELEASE", "link_type": "one", "module": "Releases", "bean_name": "Release", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["bug_number", "priority", "status", {"name": "name", "label": "LBL_SUBJECT"}, "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "bug_number", "displayParams": {"required": false, "wireless_detail_only": true}},
                                "priority",
                                "status",
                                {"name": "name", "label": "LBL_SUBJECT"},
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "bug_number", "width": "5", "label": "LBL_NUMBER", "link": true, "default": true, "enabled": true},
                                {"name": "name", "width": "32", "label": "LBL_SUBJECT", "default": true, "enabled": true, "link": true},
                                {"name": "status", "width": "10", "label": "LBL_STATUS", "default": true, "enabled": true},
                                {"name": "priority", "width": "10", "label": "LBL_PRIORITY", "default": true, "enabled": true},
                                {"name": "resolution", "width": "10", "label": "LBL_RESOLUTION", "default": true, "enabled": true},
                                {"name": "team_name", "width": "9", "label": "LBL_TEAM", "default": true, "enabled": true},
                                {"name": "assigned_user_name", "width": "9", "label": "LBL_ASSIGNED_USER_NAME", "default": true, "enabled": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "591f0ac87632085b59bb909cd345eddf"
        },
        "Opportunities": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_OPPORTUNITY_NAME", "type": "name", "dbType": "varchar", "len": "50", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "Name of the opportunity", "merge_filter": "selected", "importable": "required", "required": true
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "opportunities_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "opportunities_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "opportunities_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "opportunities_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "opportunities_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "opportunities_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "opportunity_type": {
                    "name": "opportunity_type", "vname": "LBL_TYPE", "type": "enum", "options": "opportunity_type_dom", "len": "255", "audited": true, "comment": "Type of opportunity (ex: Existing, New)", "merge_filter": "enabled"
                },
                "account_name": {
                    "name": "account_name", "rname": "name", "id_name": "account_id", "vname": "LBL_ACCOUNT_NAME", "type": "relate", "table": "accounts", "join_name": "accounts", "isnull": "true", "module": "Accounts", "dbType": "varchar", "link": "accounts", "len": "255", "source": "non-db", "unified_search": true, "required": true, "importable": "required"
                },
                "account_id": {
                    "name": "account_id", "vname": "LBL_ACCOUNT_ID", "type": "id", "source": "non-db", "audited": true
                },
                "campaign_id": {
                    "name": "campaign_id", "comment": "Campaign that generated lead", "vname": "LBL_CAMPAIGN_ID", "rname": "id", "type": "id", "dbType": "id", "table": "campaigns", "isnull": "true", "module": "Campaigns", "reportable": false, "massupdate": false, "duplicate_merge": "disabled"
                },
                "campaign_name": {
                    "name": "campaign_name", "rname": "name", "id_name": "campaign_id", "vname": "LBL_CAMPAIGN", "type": "relate", "link": "campaign_opportunities", "isnull": "true", "table": "campaigns", "module": "Campaigns", "source": "non-db"
                },
                "campaign_opportunities": {
                    "name": "campaign_opportunities", "type": "link", "vname": "LBL_CAMPAIGN_OPPORTUNITY", "relationship": "campaign_opportunities", "source": "non-db"
                },
                "lead_source": {
                    "name": "lead_source", "vname": "LBL_LEAD_SOURCE", "type": "enum", "options": "lead_source_dom", "len": "50", "comment": "Source of the opportunity", "merge_filter": "enabled"
                },
                "amount": {
                    "name": "amount", "vname": "LBL_AMOUNT", "type": "currency", "dbType": "double", "comment": "Unconverted amount of the opportunity", "importable": "required", "duplicate_merge": "1", "required": true, "options": "numeric_range_search_dom", "enable_range_search": true
                },
                "amount_usdollar": {
                    "name": "amount_usdollar", "vname": "LBL_AMOUNT_USDOLLAR", "type": "currency", "group": "amount", "dbType": "double", "disable_num_format": true, "duplicate_merge": "0", "audited": true, "comment": "Formatted amount of the opportunity", "studio": {
                        "wirelesseditview": false, "wirelessdetailview": false, "editview": false, "detailview": false, "quickcreate": false
                    }
                },
                "currency_id": {
                    "name": "currency_id", "type": "id", "group": "currency_id", "vname": "LBL_CURRENCY", "function": {
                        "name": "getCurrencyDropDown", "returns": "html"
                    },
                    "reportable": false, "comment": "Currency used for display purposes"
                },
                "currency_name": {
                    "name": "currency_name", "rname": "name", "id_name": "currency_id", "vname": "LBL_CURRENCY_NAME", "type": "relate", "isnull": "true", "table": "currencies", "module": "Currencies", "source": "non-db", "function": {
                        "name": "getCurrencyNameDropDown", "returns": "html"
                    },
                    "studio": "false", "duplicate_merge": "disabled"
                },
                "currency_symbol": {
                    "name": "currency_symbol", "rname": "symbol", "id_name": "currency_id", "vname": "LBL_CURRENCY_SYMBOL", "type": "relate", "isnull": "true", "table": "currencies", "module": "Currencies", "source": "non-db", "function": {
                        "name": "getCurrencySymbolDropDown", "returns": "html"
                    },
                    "studio": "false", "duplicate_merge": "disabled"
                },
                "date_closed": {
                    "name": "date_closed", "vname": "LBL_DATE_CLOSED", "type": "date", "audited": true, "comment": "Expected or actual date the oppportunity will close", "importable": "required", "required": true, "enable_range_search": true, "options": "date_range_search_dom"
                },
                "next_step": {
                    "name": "next_step", "vname": "LBL_NEXT_STEP", "type": "varchar", "len": "100", "comment": "The next step in the sales process", "merge_filter": "enabled"
                },
                "sales_stage": {
                    "name": "sales_stage", "vname": "LBL_SALES_STAGE", "type": "enum", "options": "sales_stage_dom", "len": "255", "audited": true, "comment": "Indication of progression towards closure", "merge_filter": "enabled", "importable": "required", "required": true
                },
                "probability": {
                    "name": "probability", "vname": "LBL_PROBABILITY", "type": "int", "dbType": "double", "audited": true, "comment": "The probability of closure", "validation": {
                        "type": "range", "min": 0, "max": 100
                    },
                    "merge_filter": "enabled"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "accounts_opportunities", "source": "non-db", "link_type": "one", "module": "Accounts", "bean_name": "Account", "vname": "LBL_ACCOUNTS"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "opportunities_contacts", "source": "non-db", "module": "Contacts", "bean_name": "Contact", "rel_fields": {
                        "contact_role": {
                            "type": "enum", "options": "opportunity_relationship_type_dom"
                        }
                    },
                    "vname": "LBL_CONTACTS"
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "opportunity_tasks", "source": "non-db", "vname": "LBL_TASKS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "opportunity_notes", "source": "non-db", "vname": "LBL_NOTES"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "opportunity_meetings", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "opportunity_calls", "source": "non-db", "vname": "LBL_CALLS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_opportunities_rel", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "documents": {
                    "name": "documents", "type": "link", "relationship": "documents_opportunities", "source": "non-db", "vname": "LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "quotes": {
                    "name": "quotes", "type": "link", "relationship": "quotes_opportunities", "source": "non-db", "vname": "LBL_QUOTES"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_opportunities", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "opportunity_leads", "source": "non-db", "vname": "LBL_LEADS"
                },
                "campaigns": {
                    "name": "campaigns", "type": "link", "relationship": "opportunities_campaign", "module": "CampaignLog", "bean_name": "CampaignLog", "source": "non-db", "vname": "LBL_CAMPAIGNS", "reportable": false
                },
                "campaign_link": {
                    "name": "campaign_link", "type": "link", "relationship": "opportunities_campaign", "vname": "LBL_CAMPAIGNS", "link_type": "one", "module": "Campaigns", "bean_name": "Campaign", "source": "non-db", "reportable": false
                },
                "currencies": {
                    "name": "currencies", "type": "link", "relationship": "opportunity_currencies", "source": "non-db", "vname": "LBL_CURRENCIES"
                },
                "contracts": {
                    "name": "contracts", "type": "link", "vname": "LBL_CONTRACTS", "relationship": "contracts_opportunities", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "amount",
                                "account_name",
                                "date_closed",
                                "sales_stage",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "amount",
                                "account_name",
                                "date_closed",
                                "sales_stage",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "width": "30", "label": "LBL_LIST_OPPORTUNITY_NAME", "link": true, "default": true, "enabled": true},
                                {"name": "sales_stage", "width": "10", "label": "LBL_LIST_SALES_STAGE", "default": true, "enabled": true},
                                {"name": "amount_usdollar", "width": "10", "label": "LBL_LIST_AMOUNT_USDOLLAR", "align": "right", "default": true, "enabled": true, "currency_format": true},
                                {"name": "opportunity_type", "width": "15", "label": "LBL_TYPE", "default": false},
                                {"name": "lead_source", "width": "15", "label": "LBL_LEAD_SOURCE", "default": false},
                                {"name": "next_step", "width": "10", "label": "LBL_NEXT_STEP", "default": false},
                                {"name": "probability", "width": "10", "label": "LBL_PROBABILITY", "default": false},
                                {"name": "date_closed", "width": "10", "label": "LBL_LIST_DATE_CLOSED", "default": true, "enabled": true},
                                {"name": "date_entered", "width": "10", "label": "LBL_DATE_ENTERED", "default": false},
                                {"name": "created_by_name", "width": "10", "label": "LBL_CREATED", "default": false},
                                {"name": "team_name", "width": "5", "label": "LBL_LIST_TEAM", "default": true, "enabled": true},
                                {"name": "assigned_user_name", "width": "5", "label": "LBL_LIST_ASSIGNED_USER", "default": true, "enabled": true},
                                {"name": "modified_by_name", "width": "5", "label": "LBL_MODIFIED", "default": false}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "19e94ad4deaa2b1a69c96a213a42a27d"
        },
        "Leads": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "rname": "name", "vname": "LBL_NAME", "type": "name", "link": true, "fields": ["first_name", "last_name"], "sort_on": "last_name", "source": "non-db", "group": "last_name", "len": "255", "db_concat_fields": ["first_name", "last_name"], "importable": "false"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "leads_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "leads_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "leads_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "leads_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "leads_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "leads_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "salutation": {
                    "name": "salutation", "vname": "LBL_SALUTATION", "type": "enum", "options": "salutation_dom", "massupdate": false, "len": "255", "comment": "Contact salutation (e.g., Mr, Ms)"
                },
                "first_name": {
                    "name": "first_name", "vname": "LBL_FIRST_NAME", "type": "varchar", "len": "100", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "First name of the contact", "merge_filter": "selected"
                },
                "last_name": {
                    "name": "last_name", "vname": "LBL_LAST_NAME", "type": "varchar", "len": "100", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "Last name of the contact", "merge_filter": "selected", "required": true, "importable": "required"
                },
                "full_name": {
                    "name": "full_name", "rname": "full_name", "vname": "LBL_NAME", "type": "fullname", "fields": ["first_name", "last_name"], "sort_on": "last_name", "source": "non-db", "group": "last_name", "len": "510", "db_concat_fields": ["first_name", "last_name"], "studio": {
                        "listview": false
                    }
                },
                "title": {
                    "name": "title", "vname": "LBL_TITLE", "type": "varchar", "len": "100", "comment": "The title of the contact"
                },
                "department": {
                    "name": "department", "vname": "LBL_DEPARTMENT", "type": "varchar", "len": "100", "comment": "Department the lead belongs to", "merge_filter": "enabled"
                },
                "do_not_call": {
                    "name": "do_not_call", "vname": "LBL_DO_NOT_CALL", "type": "bool", "default": "0", "audited": true, "comment": "An indicator of whether contact can be called"
                },
                "phone_home": {
                    "name": "phone_home", "vname": "LBL_HOME_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Home phone number of the contact", "merge_filter": "enabled"
                },
                "email": {
                    "name": "email", "type": "email", "query_type": "default", "source": "non-db", "operator": "subquery", "subquery": "SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE", "db_field": ["id"], "vname": "LBL_ANY_EMAIL", "studio": {
                        "visible": false, "searchview": true
                    }
                },
                "phone_mobile": {
                    "name": "phone_mobile", "vname": "LBL_MOBILE_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Mobile phone number of the contact", "merge_filter": "enabled"
                },
                "phone_work": {
                    "name": "phone_work", "vname": "LBL_OFFICE_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "audited": true, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Work phone number of the contact", "merge_filter": "enabled"
                },
                "phone_other": {
                    "name": "phone_other", "vname": "LBL_OTHER_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Other phone number for the contact", "merge_filter": "enabled"
                },
                "phone_fax": {
                    "name": "phone_fax", "vname": "LBL_FAX_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Contact fax number", "merge_filter": "enabled"
                },
                "email1": {
                    "name": "email1", "vname": "LBL_EMAIL_ADDRESS", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "group": "email1", "merge_filter": "enabled", "studio": {
                        "editField": true, "searchview": false, "popupsearch": false
                    }
                },
                "email2": {
                    "name": "email2", "vname": "LBL_OTHER_EMAIL_ADDRESS", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "group": "email2", "merge_filter": "enabled", "studio": "false"
                },
                "invalid_email": {
                    "name": "invalid_email", "vname": "LBL_INVALID_EMAIL", "source": "non-db", "type": "bool", "massupdate": false, "studio": "false"
                },
                "email_opt_out": {
                    "name": "email_opt_out", "vname": "LBL_EMAIL_OPT_OUT", "source": "non-db", "type": "bool", "massupdate": false, "studio": "false"
                },
                "primary_address_street": {
                    "name": "primary_address_street", "vname": "LBL_PRIMARY_ADDRESS_STREET", "type": "varchar", "len": "150", "group": "primary_address", "comment": "Street address for primary address", "merge_filter": "enabled"
                },
                "primary_address_street_2": {
                    "name": "primary_address_street_2", "vname": "LBL_PRIMARY_ADDRESS_STREET_2", "type": "varchar", "len": "150", "source": "non-db"
                },
                "primary_address_street_3": {
                    "name": "primary_address_street_3", "vname": "LBL_PRIMARY_ADDRESS_STREET_3", "type": "varchar", "len": "150", "source": "non-db"
                },
                "primary_address_city": {
                    "name": "primary_address_city", "vname": "LBL_PRIMARY_ADDRESS_CITY", "type": "varchar", "len": "100", "group": "primary_address", "comment": "City for primary address", "merge_filter": "enabled"
                },
                "primary_address_state": {
                    "name": "primary_address_state", "vname": "LBL_PRIMARY_ADDRESS_STATE", "type": "varchar", "len": "100", "group": "primary_address", "comment": "State for primary address", "merge_filter": "enabled"
                },
                "primary_address_postalcode": {
                    "name": "primary_address_postalcode", "vname": "LBL_PRIMARY_ADDRESS_POSTALCODE", "type": "varchar", "len": "20", "group": "primary_address", "comment": "Postal code for primary address", "merge_filter": "enabled"
                },
                "primary_address_country": {
                    "name": "primary_address_country", "vname": "LBL_PRIMARY_ADDRESS_COUNTRY", "type": "varchar", "group": "primary_address", "comment": "Country for primary address", "merge_filter": "enabled"
                },
                "alt_address_street": {
                    "name": "alt_address_street", "vname": "LBL_ALT_ADDRESS_STREET", "type": "varchar", "len": "150", "group": "alt_address", "comment": "Street address for alternate address", "merge_filter": "enabled"
                },
                "alt_address_street_2": {
                    "name": "alt_address_street_2", "vname": "LBL_ALT_ADDRESS_STREET_2", "type": "varchar", "len": "150", "source": "non-db"
                },
                "alt_address_street_3": {
                    "name": "alt_address_street_3", "vname": "LBL_ALT_ADDRESS_STREET_3", "type": "varchar", "len": "150", "source": "non-db"
                },
                "alt_address_city": {
                    "name": "alt_address_city", "vname": "LBL_ALT_ADDRESS_CITY", "type": "varchar", "len": "100", "group": "alt_address", "comment": "City for alternate address", "merge_filter": "enabled"
                },
                "alt_address_state": {
                    "name": "alt_address_state", "vname": "LBL_ALT_ADDRESS_STATE", "type": "varchar", "len": "100", "group": "alt_address", "comment": "State for alternate address", "merge_filter": "enabled"
                },
                "alt_address_postalcode": {
                    "name": "alt_address_postalcode", "vname": "LBL_ALT_ADDRESS_POSTALCODE", "type": "varchar", "len": "20", "group": "alt_address", "comment": "Postal code for alternate address", "merge_filter": "enabled"
                },
                "alt_address_country": {
                    "name": "alt_address_country", "vname": "LBL_ALT_ADDRESS_COUNTRY", "type": "varchar", "group": "alt_address", "comment": "Country for alternate address", "merge_filter": "enabled"
                },
                "assistant": {
                    "name": "assistant", "vname": "LBL_ASSISTANT", "type": "varchar", "len": "75", "unified_search": true, "full_text_search": {
                        "boost": 2
                    },
                    "comment": "Name of the assistant of the contact", "merge_filter": "enabled"
                },
                "assistant_phone": {
                    "name": "assistant_phone", "vname": "LBL_ASSISTANT_PHONE", "type": "phone", "dbType": "varchar", "len": 100, "group": "assistant", "unified_search": true, "full_text_search": {
                        "boost": 1
                    },
                    "comment": "Phone number of the assistant of the contact", "merge_filter": "enabled"
                },
                "email_addresses_primary": {
                    "name": "email_addresses_primary", "type": "link", "relationship": "leads_email_addresses_primary", "source": "non-db", "vname": "LBL_EMAIL_ADDRESS_PRIMARY", "duplicate_merge": "disabled"
                },
                "email_addresses": {
                    "name": "email_addresses", "type": "link", "relationship": "leads_email_addresses", "source": "non-db", "vname": "LBL_EMAIL_ADDRESSES", "reportable": false, "rel_fields": {
                        "primary_address": {
                            "type": "bool"
                        }
                    }
                },
                "converted": {
                    "name": "converted", "vname": "LBL_CONVERTED", "type": "bool", "default": "0", "comment": "Has Lead been converted to a Contact (and other Sugar objects)"
                },
                "refered_by": {
                    "name": "refered_by", "vname": "LBL_REFERED_BY", "type": "varchar", "len": "100", "comment": "Identifies who refered the lead", "merge_filter": "enabled"
                },
                "lead_source": {
                    "name": "lead_source", "vname": "LBL_LEAD_SOURCE", "type": "enum", "options": "lead_source_dom", "len": "100", "audited": true, "comment": "Lead source (ex: Web, print)", "merge_filter": "enabled"
                },
                "lead_source_description": {
                    "name": "lead_source_description", "vname": "LBL_LEAD_SOURCE_DESCRIPTION", "type": "text", "group": "lead_source", "comment": "Description of the lead source"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "len": "100", "options": "lead_status_dom", "audited": true, "comment": "Status of the lead", "merge_filter": "enabled"
                },
                "status_description": {
                    "name": "status_description", "vname": "LBL_STATUS_DESCRIPTION", "type": "text", "group": "status", "comment": "Description of the status of the lead"
                },
                "reports_to_id": {
                    "name": "reports_to_id", "vname": "LBL_REPORTS_TO_ID", "type": "id", "reportable": false, "comment": "ID of Contact the Lead reports to"
                },
                "report_to_name": {
                    "name": "report_to_name", "rname": "name", "id_name": "reports_to_id", "vname": "LBL_REPORTS_TO", "type": "relate", "table": "contacts", "isnull": "true", "module": "Contacts", "dbType": "varchar", "len": "id", "source": "non-db", "reportable": false, "massupdate": false
                },
                "reports_to_link": {
                    "name": "reports_to_link", "type": "link", "relationship": "lead_direct_reports", "link_type": "one", "side": "right", "source": "non-db", "vname": "LBL_REPORTS_TO", "reportable": false
                },
                "reportees": {
                    "name": "reportees", "type": "link", "relationship": "lead_direct_reports", "link_type": "many", "side": "left", "source": "non-db", "vname": "LBL_REPORTS_TO", "reportable": false
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "contact_leads", "module": "Contacts", "source": "non-db", "vname": "LBL_CONTACTS", "reportable": false
                },
                "account_name": {
                    "name": "account_name", "vname": "LBL_ACCOUNT_NAME", "type": "varchar", "len": "255", "unified_search": true, "full_text_search": 1, "comment": "Account name for lead"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "account_leads", "link_type": "one", "source": "non-db", "vname": "LBL_ACCOUNT", "duplicate_merge": "disabled"
                },
                "account_description": {
                    "name": "account_description", "vname": "LBL_ACCOUNT_DESCRIPTION", "type": "text", "group": "account_name", "unified_search": true, "full_text_search": 1, "comment": "Description of lead account"
                },
                "contact_id": {
                    "name": "contact_id", "type": "id", "reportable": false, "vname": "LBL_CONTACT_ID", "comment": "If converted, Contact ID resulting from the conversion"
                },
                "contact": {
                    "name": "contact", "type": "link", "link_type": "one", "relationship": "contact_leads", "source": "non-db", "vname": "LBL_LEADS", "reportable": false
                },
                "account_id": {
                    "name": "account_id", "type": "id", "reportable": false, "vname": "LBL_ACCOUNT_ID", "comment": "If converted, Account ID resulting from the conversion"
                },
                "opportunity_id": {
                    "name": "opportunity_id", "type": "id", "reportable": false, "vname": "LBL_OPPORTUNITY_ID", "comment": "If converted, Opportunity ID resulting from the conversion"
                },
                "opportunity": {
                    "name": "opportunity", "type": "link", "link_type": "one", "relationship": "opportunity_leads", "source": "non-db", "vname": "LBL_OPPORTUNITIES"
                },
                "opportunity_name": {
                    "name": "opportunity_name", "vname": "LBL_OPPORTUNITY_NAME", "type": "varchar", "len": "255", "comment": "Opportunity name associated with lead"
                },
                "opportunity_amount": {
                    "name": "opportunity_amount", "vname": "LBL_OPPORTUNITY_AMOUNT", "type": "varchar", "group": "opportunity_name", "len": "50", "comment": "Amount of the opportunity"
                },
                "campaign_id": {
                    "name": "campaign_id", "type": "id", "reportable": false, "vname": "LBL_CAMPAIGN_ID", "comment": "Campaign that generated lead"
                },
                "campaign_name": {
                    "name": "campaign_name", "rname": "name", "id_name": "campaign_id", "vname": "LBL_CAMPAIGN", "type": "relate", "link": "campaign_leads", "table": "campaigns", "isnull": "true", "module": "Campaigns", "source": "non-db"
                },
                "campaign_leads": {
                    "name": "campaign_leads", "type": "link", "vname": "LBL_CAMPAIGN_LEAD", "relationship": "campaign_leads", "source": "non-db"
                },
                "c_accept_status_fields": {
                    "name": "c_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "calls", "link_type": "relationship_info", "source": "non-db", "importable": "false", "duplicate_merge": "disabled", "studio": false
                },
                "m_accept_status_fields": {
                    "name": "m_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "meetings", "link_type": "relationship_info", "source": "non-db", "importable": "false", "hideacl": true, "duplicate_merge": "disabled", "studio": false
                },
                "accept_status_id": {
                    "name": "accept_status_id", "type": "varchar", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "studio": {
                        "listview": false
                    }
                },
                "accept_status_name": {
                    "massupdate": false, "name": "accept_status_name", "type": "enum", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "options": "dom_meeting_accept_status", "importable": "false"
                },
                "webtolead_email1": {
                    "name": "webtolead_email1", "vname": "LBL_EMAIL_ADDRESS", "type": "email", "len": "100", "source": "non-db", "comment": "Main email address of lead", "importable": "false", "studio": "false"
                },
                "webtolead_email2": {
                    "name": "webtolead_email2", "vname": "LBL_OTHER_EMAIL_ADDRESS", "type": "email", "len": "100", "source": "non-db", "comment": "Secondary email address of lead", "importable": "false", "studio": "false"
                },
                "webtolead_email_opt_out": {
                    "name": "webtolead_email_opt_out", "vname": "LBL_EMAIL_OPT_OUT", "type": "bool", "source": "non-db", "comment": "Indicator signaling if lead elects to opt out of email campaigns", "importable": "false", "massupdate": false, "studio": "false"
                },
                "webtolead_invalid_email": {
                    "name": "webtolead_invalid_email", "vname": "LBL_INVALID_EMAIL", "type": "bool", "source": "non-db", "comment": "Indicator that email address for lead is invalid", "importable": "false", "massupdate": false, "studio": "false"
                },
                "birthdate": {
                    "name": "birthdate", "vname": "LBL_BIRTHDATE", "massupdate": false, "type": "date", "comment": "The birthdate of the contact"
                },
                "portal_name": {
                    "name": "portal_name", "vname": "LBL_PORTAL_NAME", "type": "varchar", "len": "255", "group": "portal", "comment": "Portal user name when lead created via lead portal", "studio": "false"
                },
                "portal_app": {
                    "name": "portal_app", "vname": "LBL_PORTAL_APP", "type": "varchar", "group": "portal", "len": "255", "comment": "Portal application that resulted in created of lead", "studio": "false"
                },
                "website": {
                    "name": "website", "vname": "LBL_WEBSITE", "type": "url", "dbType": "varchar", "len": 255, "comment": "URL of website for the company"
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "lead_tasks", "source": "non-db", "vname": "LBL_TASKS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "lead_notes", "source": "non-db", "vname": "LBL_NOTES"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "meetings_leads", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "calls_leads", "source": "non-db", "vname": "LBL_CALLS"
                },
                "oldmeetings": {
                    "name": "oldmeetings", "type": "link", "relationship": "lead_meetings", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "oldcalls": {
                    "name": "oldcalls", "type": "link", "relationship": "lead_calls", "source": "non-db", "vname": "LBL_CALLS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_leads_rel", "source": "non-db", "unified_search": true, "vname": "LBL_EMAILS"
                },
                "campaigns": {
                    "name": "campaigns", "type": "link", "relationship": "lead_campaign_log", "module": "CampaignLog", "bean_name": "CampaignLog", "source": "non-db", "vname": "LBL_CAMPAIGNLOG"
                },
                "prospect_lists": {
                    "name": "prospect_lists", "type": "link", "relationship": "prospect_list_leads", "module": "ProspectLists", "source": "non-db", "vname": "LBL_PROSPECT_LIST"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "first_name", "customCode": "{html_options name=\"salutation\" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name=\"first_name\" size=\"25\" maxlength=\"25\" type=\"text\" value=\"{$fields.first_name.value}\">", "displayParams": {"wireless_edit_only": true}},
                                {"name": "last_name", "displayParams": {"wireless_edit_only": true}},
                                "title",
                                "account_name",
                                "phone_work",
                                "phone_mobile",
                                "email1",
                                "primary_address_street",
                                "primary_address_city",
                                "primary_address_state",
                                "primary_address_postalcode",
                                "primary_address_country",
                                "status",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "first_name", "customCode": "{html_options name=\"salutation\" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name=\"first_name\" size=\"25\" maxlength=\"25\" type=\"text\" value=\"{$fields.first_name.value}\">", "displayParams": {"wireless_edit_only": true}},
                                {"name": "last_name", "displayParams": {"wireless_edit_only": true}},
                                "title",
                                "account_name",
                                "phone_work",
                                "phone_mobile",
                                "email1",
                                "primary_address_street",
                                "primary_address_city",
                                "primary_address_state",
                                "primary_address_postalcode",
                                "primary_address_country",
                                "status",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true, "related_fields": ["first_name", "last_name", "salutation"]},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["last_name", "first_name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "7aebdcdc3846317fe6d0b2d07348d2ce"
        },
        "Reports": {
            "fields": {
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "reports_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "reports_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "reports_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": false
                },
                "name": {
                    "name": "name", "vname": "LBL_NAME", "type": "varchar", "len": "255", "required": true
                },
                "module": {
                    "name": "module", "vname": "LBL_MODULE", "type": "varchar", "len": "36", "required": true
                },
                "report_type": {
                    "name": "report_type", "vname": "LBL_REPORT_TYPE", "type": "varchar", "len": "36", "required": true
                },
                "content": {
                    "name": "content", "vname": "LBL_CONTENT", "type": "longtext"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "reportable": false
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "massupdate": false, "reportable": false
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "reportable": false
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "vname": "LBL_ASSIGNED_TO_NAME", "type": "relate", "link": "assigned_user_link", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "report_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id"
                },
                "is_published": {
                    "name": "is_published", "vname": "LBL_IS_PUBLISHED", "type": "bool", "default": 0, "required": true
                },
                "last_run_date": {
                    "name": "last_run_date", "rname": "date_modified", "id_name": "date_modified", "vname": "LBL_REPORT_LAST_RUN_DATE", "type": "relate", "dbType": "datetime", "table": "report_cache", "isnull": "true", "module": "Report", "reportable": false, "source": "non-db", "massupdate": false, "duplicate_merge": "disabled", "hideacl": true, "width": "15"
                },
                "chart_type": {
                    "name": "chart_type", "vname": "LBL_CHART_TYPE", "type": "varchar", "required": true, "default": "none", "len": 36
                },
                "schedule_type": {
                    "name": "schedule_type", "vname": "LBL_SCHEDULE_TYPE", "type": "varchar", "len": "3", "default": "pro"
                },
                "favorite": {
                    "name": "favorite", "vname": "LBL_FAVORITE", "type": "bool", "required": false, "reportable": false
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "9da0a08fe9904563aa1973ec40e5d483"
        },
        "Quotes": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_QUOTE_NAME", "dbType": "varchar", "type": "name", "len": "50", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "importable": "required", "required": true
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "quotes_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "quotes_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "quotes_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "quotes_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "quotes_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "quotes_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "shipper_id": {
                    "name": "shipper_id", "vname": "LBL_SHIPPER_ID", "type": "id", "required": false, "do_report": false, "reportable": false
                },
                "shipper_name": {
                    "name": "shipper_name", "rname": "name", "id_name": "shipper_id", "join_name": "shippers", "type": "relate", "link": "shippers", "table": "shippers", "isnull": "true", "module": "Shippers", "dbType": "varchar", "len": "255", "vname": "LBL_SHIPPING_PROVIDER", "source": "non-db", "comment": "Shipper Name"
                },
                "shippers": {
                    "name": "shippers", "type": "link", "relationship": "shipper_quotes", "vname": "LBL_SHIPPING_PROVIDER", "source": "non-db"
                },
                "currency_id": {
                    "name": "currency_id", "vname": "LBL_CURRENCY_ID", "type": "id", "required": false, "do_report": false, "reportable": false
                },
                "taxrate_id": {
                    "name": "taxrate_id", "vname": "LBL_TAXRATE_ID", "type": "id", "required": false, "do_report": false, "reportable": false
                },
                "show_line_nums": {
                    "name": "show_line_nums", "vname": "LBL_SHOW_LINE_NUMS", "type": "bool", "default": 1, "hideacl": true, "reportable": false, "massupdate": false
                },
                "calc_grand_total": {
                    "name": "calc_grand_total", "vname": "LBL_CALC_GRAND", "type": "bool", "reportable": false, "default": 1, "hideacl": true, "massupdate": false
                },
                "quote_type": {
                    "name": "quote_type", "vname": "LBL_QUOTE_TYPE", "type": "varchar", "len": 100
                },
                "date_quote_expected_closed": {
                    "name": "date_quote_expected_closed", "vname": "LBL_DATE_QUOTE_EXPECTED_CLOSED", "type": "date", "audited": true, "reportable": true, "importable": "required", "required": true, "enable_range_search": true, "options": "date_range_search_dom"
                },
                "original_po_date": {
                    "name": "original_po_date", "vname": "LBL_ORIGINAL_PO_DATE", "type": "date", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "payment_terms": {
                    "name": "payment_terms", "vname": "LBL_PAYMENT_TERMS", "type": "enum", "options": "payment_terms", "len": "128"
                },
                "date_quote_closed": {
                    "name": "date_quote_closed", "massupdate": false, "vname": "LBL_DATE_QUOTE_CLOSED", "type": "date", "reportable": false, "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_order_shipped": {
                    "name": "date_order_shipped", "massupdate": false, "vname": "LBL_LIST_DATE_QUOTE_CLOSED", "type": "date", "reportable": false, "enable_range_search": true, "options": "date_range_search_dom"
                },
                "order_stage": {
                    "name": "order_stage", "vname": "LBL_ORDER_STAGE", "type": "enum", "options": "order_stage_dom", "massupdate": false, "len": 100
                },
                "quote_stage": {
                    "name": "quote_stage", "vname": "LBL_QUOTE_STAGE", "type": "enum", "options": "quote_stage_dom", "len": 100, "audited": true, "importable": "required", "required": true
                },
                "purchase_order_num": {
                    "name": "purchase_order_num", "vname": "LBL_PURCHASE_ORDER_NUM", "type": "varchar", "len": "50"
                },
                "quote_num": {
                    "name": "quote_num", "vname": "LBL_QUOTE_NUM", "type": "int", "auto_increment": true, "required": true, "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "disable_num_format": true, "enable_range_search": true, "options": "numeric_range_search_dom"
                },
                "subtotal": {
                    "name": "subtotal", "vname": "LBL_SUBTOTAL", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "subtotal_usdollar": {
                    "name": "subtotal_usdollar", "group": "subtotal", "vname": "LBL_SUBTOTAL_USDOLLAR", "dbType": "decimal", "type": "currency", "len": "26,6", "audited": true
                },
                "shipping": {
                    "name": "shipping", "vname": "LBL_SHIPPING", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "shipping_usdollar": {
                    "name": "shipping_usdollar", "vname": "LBL_SHIPPING_USDOLLAR", "group": "shipping", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "discount": {
                    "name": "discount", "vname": "LBL_DISCOUNT_TOTAL", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "deal_tot": {
                    "name": "deal_tot", "vname": "LBL_DEAL_TOT", "dbType": "decimal", "type": "decimal", "len": "26,2"
                },
                "deal_tot_usdollar": {
                    "name": "deal_tot_usdollar", "vname": "LBL_DEAL_TOT_USDOLLAR", "dbType": "decimal", "type": "decimal", "len": "26,2"
                },
                "new_sub": {
                    "name": "new_sub", "vname": "LBL_NEW_SUB", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "new_sub_usdollar": {
                    "name": "new_sub_usdollar", "vname": "LBL_NEW_SUB", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "tax": {
                    "name": "tax", "vname": "LBL_TAX", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "tax_usdollar": {
                    "name": "tax_usdollar", "vname": "LBL_TAX_USDOLLAR", "dbType": "decimal", "group": "tax", "type": "currency", "len": "26,6", "audited": true
                },
                "total": {
                    "name": "total", "vname": "LBL_TOTAL", "dbType": "decimal", "type": "currency", "len": "26,6"
                },
                "total_usdollar": {
                    "name": "total_usdollar", "vname": "LBL_TOTAL_USDOLLAR", "dbType": "decimal", "group": "total", "type": "currency", "len": "26,6", "audited": true, "enable_range_search": true, "options": "numeric_range_search_dom"
                },
                "billing_address_street": {
                    "name": "billing_address_street", "vname": "LBL_BILLING_ADDRESS_STREET", "type": "varchar", "group": "billing_address", "len": "150"
                },
                "billing_address_city": {
                    "name": "billing_address_city", "vname": "LBL_BILLING_ADDRESS_CITY", "type": "varchar", "group": "billing_address", "len": "100"
                },
                "billing_address_state": {
                    "name": "billing_address_state", "vname": "LBL_BILLING_ADDRESS_STATE", "type": "varchar", "group": "billing_address", "len": "100"
                },
                "billing_address_postalcode": {
                    "name": "billing_address_postalcode", "vname": "LBL_BILLING_ADDRESS_POSTAL_CODE", "type": "varchar", "group": "billing_address", "len": "20"
                },
                "billing_address_country": {
                    "name": "billing_address_country", "vname": "LBL_BILLING_ADDRESS_COUNTRY", "type": "varchar", "group": "billing_address", "len": "100"
                },
                "shipping_address_street": {
                    "name": "shipping_address_street", "vname": "LBL_SHIPPING_ADDRESS_STREET", "type": "varchar", "group": "shipping_address", "len": "150"
                },
                "shipping_address_city": {
                    "name": "shipping_address_city", "vname": "LBL_SHIPPING_ADDRESS_CITY", "type": "varchar", "group": "shipping_address", "len": "100"
                },
                "shipping_address_state": {
                    "name": "shipping_address_state", "vname": "LBL_SHIPPING_ADDRESS_STATE", "type": "varchar", "group": "shipping_address", "len": "100"
                },
                "shipping_address_postalcode": {
                    "name": "shipping_address_postalcode", "vname": "LBL_SHIPPING_ADDRESS_POSTAL_CODE", "type": "varchar", "group": "shipping_address", "len": "20"
                },
                "shipping_address_country": {
                    "name": "shipping_address_country", "vname": "LBL_SHIPPING_ADDRESS_COUNTRY", "type": "varchar", "group": "shipping_address", "len": "100"
                },
                "system_id": {
                    "name": "system_id", "vname": "LBL_SYSTEM_ID", "type": "int"
                },
                "shipping_account_name": {
                    "name": "shipping_account_name", "rname": "name", "id_name": "shipping_account_id", "vname": "LBL_SHIPPING_ACCOUNT_NAME", "type": "relate", "table": "shipping_accounts", "isnull": "true", "group": "shipping_address", "link": "shipping_accounts", "module": "Accounts", "source": "non-db"
                },
                "shipping_account_id": {
                    "name": "shipping_account_id", "type": "id", "group": "shipping_address", "vname": "LBL_SHIPPING_ACCOUNT_ID", "source": "non-db"
                },
                "shipping_contact_name": {
                    "name": "shipping_contact_name", "rname": "last_name", "id_name": "shipping_contact_id", "vname": "LBL_SHIPPING_CONTACT_NAME", "type": "relate", "group": "shipping_address", "link": "shipping_contacts", "table": "shipping_contacts", "isnull": "true", "module": "Contacts", "source": "non-db"
                },
                "shipping_contact_id": {
                    "name": "shipping_contact_id", "rname": "last_name", "group": "shipping_address", "id_name": "shipping_contact_id", "vname": "LBL_SHIPPING_CONTACT_ID", "type": "relate", "link": "shipping_contacts", "table": "shipping_contacts", "isnull": "true", "module": "Contacts", "source": "non-db", "massupdate": false
                },
                "account_name": {
                    "name": "account_name", "rname": "name", "id_name": "account_id", "vname": "LBL_ACCOUNT_NAME", "type": "relate", "group": "billing_address", "link": "billing_accounts", "table": "billing_accounts", "isnull": "true", "module": "Accounts", "source": "non-db", "massupdate": false, "studio": "false"
                },
                "account_id": {
                    "name": "account_id", "type": "id", "group": "billing_address", "vname": "LBL_ACCOUNT_ID", "source": "non-db", "massupdate": false, "studio": "false"
                },
                "billing_account_name": {
                    "name": "billing_account_name", "rname": "name", "group": "billing_address", "id_name": "billing_account_id", "vname": "LBL_BILLING_ACCOUNT_NAME", "type": "relate", "link": "billing_accounts", "table": "billing_accounts", "isnull": "true", "module": "Accounts", "source": "non-db", "importable": "required", "required": true
                },
                "billing_account_id": {
                    "name": "billing_account_id", "type": "id", "group": "billing_address", "vname": "LBL_BILLING_ACCOUNT_ID", "source": "non-db"
                },
                "billing_contact_name": {
                    "name": "billing_contact_name", "rname": "last_name", "group": "billing_address", "id_name": "billing_contact_id", "vname": "LBL_BILLING_CONTACT_NAME", "type": "relate", "link": "billing_contacts", "table": "billing_contacts", "isnull": "true", "module": "Contacts", "source": "non-db"
                },
                "billing_contact_id": {
                    "name": "billing_contact_id", "rname": "last_name", "id_name": "billing_contact_id", "vname": "LBL_BILLING_CONTACT_ID", "type": "relate", "group": "billing_address", "link": "billing_contacts", "table": "billing_contacts", "isnull": "true", "module": "Contacts", "source": "non-db", "massupdate": false
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "quote_tasks", "vname": "LBL_TASKS", "source": "non-db"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "quote_notes", "vname": "LBL_NOTES", "source": "non-db"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "quote_meetings", "vname": "LBL_MEETINGS", "source": "non-db"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "quote_calls", "vname": "LBL_CALLS", "source": "non-db"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_quotes", "vname": "LBL_EMAILS", "source": "non-db"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_quotes", "vname": "LBL_PROJECTS", "source": "non-db"
                },
                "products": {
                    "name": "products", "type": "link", "relationship": "quote_products", "vname": "LBL_PRODUCTS", "source": "non-db"
                },
                "shipping_accounts": {
                    "name": "shipping_accounts", "type": "link", "relationship": "quotes_shipto_accounts", "vname": "LBL_SHIP_TO_ACCOUNT", "source": "non-db", "link_type": "one"
                },
                "billing_accounts": {
                    "name": "billing_accounts", "type": "link", "relationship": "quotes_billto_accounts", "vname": "LBL_BILL_TO_ACCOUNT", "source": "non-db", "link_type": "one"
                },
                "shipping_contacts": {
                    "name": "shipping_contacts", "type": "link", "relationship": "quotes_contacts_shipto", "vname": "LBL_SHIP_TO_CONTACT", "source": "non-db", "link_type": "one"
                },
                "billing_contacts": {
                    "name": "billing_contacts", "type": "link", "link_type": "one", "vname": "LBL_BILL_TO_CONTACT", "relationship": "quotes_contacts_billto", "source": "non-db"
                },
                "product_bundles": {
                    "name": "product_bundles", "type": "link", "vname": "LBL_PRODUCT_BUNDLES", "relationship": "product_bundle_quote", "source": "non-db"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "vname": "LBL_OPPORTUNITY", "relationship": "quotes_opportunities", "link_type": "one", "source": "non-db"
                },
                "opportunity_name": {
                    "name": "opportunity_name", "rname": "name", "id_name": "opportunity_id", "vname": "LBL_OPPORTUNITY_NAME", "type": "relate", "table": "Opportunities", "isnull": "true", "module": "Opportunities", "link": "opportunities", "massupdate": false, "source": "non-db", "len": 50
                },
                "opportunity_id": {
                    "name": "opportunity_id", "type": "id", "vname": "LBL_BILLING_ACCOUNT_NAME", "source": "non-db"
                },
                "documents": {
                    "name": "documents", "type": "link", "relationship": "documents_quotes", "source": "non-db", "vname": "LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "contracts": {
                    "name": "contracts", "type": "link", "vname": "LBL_CONTRACTS", "relationship": "contracts_quotes", "link_type": "one", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "7ca63567be8ed1284f6c8fb754c9bb5e"
        },
        "Documents": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_NAME", "source": "non-db", "type": "varchar"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "documents_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "documents_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "documents_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "documents_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "documents_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "documents_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "document_name": {
                    "name": "document_name", "vname": "LBL_NAME", "type": "varchar", "len": "255", "required": true, "importable": "required", "unified_search": true, "full_text_search": {
                        "boost": 3
                    }
                },
                "doc_id": {
                    "name": "doc_id", "vname": "LBL_DOC_ID", "type": "varchar", "len": "100", "comment": "Document ID from documents web server provider", "importable": false, "studio": "false"
                },
                "doc_type": {
                    "name": "doc_type", "vname": "LBL_DOC_TYPE", "type": "enum", "function": "getDocumentsExternalApiDropDown", "len": "100", "comment": "Document type (ex: Google, box.net, LotusLive)", "popupHelp": "LBL_DOC_TYPE_POPUP", "massupdate": false, "options": "eapm_list", "default": "Sugar", "studio": {
                        "wirelesseditview": false, "wirelessdetailview": false, "wirelesslistview": false, "wireless_basic_search": false
                    }
                },
                "doc_url": {
                    "name": "doc_url", "vname": "LBL_DOC_URL", "type": "varchar", "len": "255", "comment": "Document URL from documents web server provider", "importable": false, "massupdate": false, "studio": "false"
                },
                "filename": {
                    "name": "filename", "vname": "LBL_FILENAME", "type": "file", "source": "non-db", "comment": "The filename of the document attachment", "required": true, "noChange": true, "allowEapm": true, "fileId": "document_revision_id", "docType": "doc_type"
                },
                "active_date": {
                    "name": "active_date", "vname": "LBL_DOC_ACTIVE_DATE", "type": "date", "importable": "required", "required": true, "display_default": "now"
                },
                "exp_date": {
                    "name": "exp_date", "vname": "LBL_DOC_EXP_DATE", "type": "date"
                },
                "category_id": {
                    "name": "category_id", "vname": "LBL_SF_CATEGORY", "type": "enum", "len": 100, "options": "document_category_dom", "reportable": true
                },
                "subcategory_id": {
                    "name": "subcategory_id", "vname": "LBL_SF_SUBCATEGORY", "type": "enum", "len": 100, "options": "document_subcategory_dom", "reportable": true
                },
                "status_id": {
                    "name": "status_id", "vname": "LBL_DOC_STATUS", "type": "enum", "len": 100, "options": "document_status_dom", "reportable": false
                },
                "status": {
                    "name": "status", "vname": "LBL_DOC_STATUS", "type": "varchar", "source": "non-db", "comment": "Document status for Meta-Data framework"
                },
                "document_revision_id": {
                    "name": "document_revision_id", "vname": "LBL_LATEST_REVISION", "type": "varchar", "len": "36", "reportable": false
                },
                "revisions": {
                    "name": "revisions", "type": "link", "relationship": "document_revisions", "source": "non-db", "vname": "LBL_REVISIONS"
                },
                "revision": {
                    "name": "revision", "vname": "LBL_DOC_VERSION", "type": "varchar", "reportable": false, "required": true, "source": "non-db", "importable": "required", "default": "1"
                },
                "last_rev_created_name": {
                    "name": "last_rev_created_name", "vname": "LBL_LAST_REV_CREATOR", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "last_rev_mime_type": {
                    "name": "last_rev_mime_type", "vname": "LBL_LAST_REV_MIME_TYPE", "type": "varchar", "reportable": false, "studio": "false", "source": "non-db"
                },
                "latest_revision": {
                    "name": "latest_revision", "vname": "LBL_LATEST_REVISION", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "last_rev_create_date": {
                    "name": "last_rev_create_date", "type": "date", "table": "document_revisions", "link": "revisions", "join_name": "document_revisions", "vname": "LBL_LAST_REV_CREATE_DATE", "rname": "date_entered", "reportable": false, "source": "non-db"
                },
                "contracts": {
                    "name": "contracts", "type": "link", "relationship": "contracts_documents", "source": "non-db", "vname": "LBL_CONTRACTS"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "leads_documents", "source": "non-db", "vname": "LBL_LEADS"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "documents_accounts", "source": "non-db", "vname": "LBL_ACCOUNTS_SUBPANEL_TITLE"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "documents_contacts", "source": "non-db", "vname": "LBL_CONTACTS_SUBPANEL_TITLE"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "documents_opportunities", "source": "non-db", "vname": "LBL_OPPORTUNITIES_SUBPANEL_TITLE"
                },
                "cases": {
                    "name": "cases", "type": "link", "relationship": "documents_cases", "source": "non-db", "vname": "LBL_CASES_SUBPANEL_TITLE"
                },
                "bugs": {
                    "name": "bugs", "type": "link", "relationship": "documents_bugs", "source": "non-db", "vname": "LBL_BUGS_SUBPANEL_TITLE"
                },
                "quotes": {
                    "name": "quotes", "type": "link", "relationship": "documents_quotes", "source": "non-db", "vname": "LBL_QUOTES_SUBPANEL_TITLE"
                },
                "products": {
                    "name": "products", "type": "link", "relationship": "documents_products", "source": "non-db", "vname": "LBL_PRODUCTS_SUBPANEL_TITLE"
                },
                "related_doc_id": {
                    "name": "related_doc_id", "vname": "LBL_RELATED_DOCUMENT_ID", "reportable": false, "dbType": "id", "type": "varchar", "len": "36"
                },
                "related_doc_name": {
                    "name": "related_doc_name", "vname": "LBL_DET_RELATED_DOCUMENT", "type": "relate", "table": "documents", "id_name": "related_doc_id", "module": "Documents", "source": "non-db", "comment": "The related document name for Meta-Data framework"
                },
                "related_doc_rev_id": {
                    "name": "related_doc_rev_id", "vname": "LBL_RELATED_DOCUMENT_REVISION_ID", "reportable": false, "dbType": "id", "type": "varchar", "len": "36"
                },
                "related_doc_rev_number": {
                    "name": "related_doc_rev_number", "vname": "LBL_DET_RELATED_DOCUMENT_VERSION", "type": "varchar", "source": "non-db", "comment": "The related document version number for Meta-Data framework"
                },
                "is_template": {
                    "name": "is_template", "vname": "LBL_IS_TEMPLATE", "type": "bool", "default": 0, "reportable": false
                },
                "template_type": {
                    "name": "template_type", "vname": "LBL_TEMPLATE_TYPE", "type": "enum", "len": 100, "options": "document_template_type_dom", "reportable": false
                },
                "latest_revision_name": {
                    "name": "latest_revision_name", "vname": "LBL_LASTEST_REVISION_NAME", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "selected_revision_name": {
                    "name": "selected_revision_name", "vname": "LBL_SELECTED_REVISION_NAME", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "contract_status": {
                    "name": "contract_status", "vname": "LBL_CONTRACT_STATUS", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "contract_name": {
                    "name": "contract_name", "vname": "LBL_CONTRACT_NAME", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "linked_id": {
                    "name": "linked_id", "vname": "LBL_LINKED_ID", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "selected_revision_id": {
                    "name": "selected_revision_id", "vname": "LBL_SELECTED_REVISION_ID", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "latest_revision_id": {
                    "name": "latest_revision_id", "vname": "LBL_LATEST_REVISION_ID", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "selected_revision_filename": {
                    "name": "selected_revision_filename", "vname": "LBL_SELECTED_REVISION_FILENAME", "type": "varchar", "reportable": false, "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "document_name", "label": "LBL_DOC_NAME"},
                                {"name": "uploadfile", "displayParams": {"link": "uploadfile", "id": "id"}},
                                "active_date",
                                "exp_date",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "document_name", "label": "LBL_DOC_NAME"},
                                {"name": "uploadfile", "displayParams": {"link": "uploadfile", "id": "id"}},
                                "active_date",
                                "exp_date",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "document_name", "width": "40", "label": "LBL_NAME", "link": true, "default": true, "enabled": true},
                                {"name": "modified_by_name", "width": "10", "label": "LBL_MODIFIED_USER", "module": "Users", "id": "USERS_ID", "default": false, "sortable": false, "related_fields": ["modified_user_id"]},
                                {"name": "category_id", "width": "40", "label": "LBL_LIST_CATEGORY", "default": true, "enabled": true},
                                {"name": "subcategory_id", "width": "40", "label": "LBL_LIST_SUBCATEGORY", "default": true, "enabled": true},
                                {"name": "team_name", "width": "2", "label": "LBL_LIST_TEAM", "sortable": false, "default": true, "enabled": true},
                                {"name": "created_by_name", "width": "2", "label": "LBL_LIST_LAST_REV_CREATOR", "default": true, "sortable": false, "enabled": true},
                                {"name": "active_date", "width": "10", "label": "LBL_LIST_ACTIVE_DATE", "default": true, "enabled": true},
                                {"name": "EXP_DATE", "width": "10", "label": "LBL_LIST_EXP_DATE", "default": true, "enabled": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["document_name"], "advanced_search": ["document_name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "ea7905dfbdd76c9074b17735ef1ccea9"
        },
        "Emails": {
            "fields": {
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "emails_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "emails_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "emails_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true, "comment": "Date record created"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true, "comment": "Date record last modified"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "reportable": true, "dbType": "id", "comment": "User ID that last modified record"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "vname": "LBL_ASSIGNED_TO", "type": "varchar", "reportable": false, "source": "non-db", "table": "users"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED_BY", "type": "assigned_user_name", "table": "users", "isnull": "false", "reportable": true, "dbType": "id", "comment": "User ID that last modified record"
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "vname": "LBL_CREATED_BY", "type": "id", "len": "36", "reportable": false, "comment": "User name who created record"
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "reportable": false, "comment": "Record deletion indicator"
                },
                "from_addr_name": {
                    "name": "from_addr_name", "type": "varchar", "vname": "from_addr_name", "source": "non-db"
                },
                "reply_to_addr": {
                    "name": "reply_to_addr", "type": "varchar", "vname": "reply_to_addr", "source": "non-db"
                },
                "to_addrs_names": {
                    "name": "to_addrs_names", "type": "varchar", "vname": "to_addrs_names", "source": "non-db"
                },
                "cc_addrs_names": {
                    "name": "cc_addrs_names", "type": "varchar", "vname": "cc_addrs_names", "source": "non-db"
                },
                "bcc_addrs_names": {
                    "name": "bcc_addrs_names", "type": "varchar", "vname": "bcc_addrs_names", "source": "non-db"
                },
                "raw_source": {
                    "name": "raw_source", "type": "varchar", "vname": "raw_source", "source": "non-db"
                },
                "description_html": {
                    "name": "description_html", "type": "varchar", "vname": "description_html", "source": "non-db"
                },
                "description": {
                    "name": "description", "type": "varchar", "vname": "description", "source": "non-db"
                },
                "date_sent": {
                    "name": "date_sent", "vname": "LBL_DATE_SENT", "type": "datetime"
                },
                "message_id": {
                    "name": "message_id", "vname": "LBL_MESSAGE_ID", "type": "varchar", "len": 255, "comment": "ID of the email item obtained from the email transport system"
                },
                "name": {
                    "name": "name", "vname": "LBL_SUBJECT", "type": "varchar", "required": false, "len": "255", "comment": "The subject of the email"
                },
                "type": {
                    "name": "type", "vname": "LBL_LIST_TYPE", "type": "enum", "options": "dom_email_types", "len": 100, "massupdate": false, "comment": "Type of email (ex: draft)"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "len": 100, "options": "dom_email_status"
                },
                "flagged": {
                    "name": "flagged", "vname": "LBL_EMAIL_FLAGGED", "type": "bool", "required": false, "reportable": false, "comment": "flagged status"
                },
                "reply_to_status": {
                    "name": "reply_to_status", "vname": "LBL_EMAIL_REPLY_TO_STATUS", "type": "bool", "required": false, "reportable": false, "comment": "I you reply to an email then reply to status of original email is set"
                },
                "intent": {
                    "name": "intent", "vname": "LBL_INTENT", "type": "varchar", "len": 100, "default": "pick", "comment": "Target of action used in Inbound Email assignment"
                },
                "mailbox_id": {
                    "name": "mailbox_id", "vname": "LBL_MAILBOX_ID", "type": "id", "len": "36", "reportable": false
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "emails_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "emails_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "emails_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "parent_name": {
                    "name": "parent_name", "type": "varchar", "reportable": false, "source": "non-db"
                },
                "parent_type": {
                    "name": "parent_type", "type": "varchar", "reportable": false, "len": 100, "comment": "Identifier of Sugar module to which this email is associated (deprecated as of 4.2)"
                },
                "parent_id": {
                    "name": "parent_id", "type": "id", "len": "36", "reportable": false, "comment": "ID of Sugar object referenced by parent_type (deprecated as of 4.2)"
                },
                "accounts": {
                    "name": "accounts", "vname": "LBL_EMAILS_ACCOUNTS_REL", "type": "link", "relationship": "emails_accounts_rel", "module": "Accounts", "bean_name": "Account", "source": "non-db"
                },
                "bugs": {
                    "name": "bugs", "vname": "LBL_EMAILS_BUGS_REL", "type": "link", "relationship": "emails_bugs_rel", "module": "Bugs", "bean_name": "Bug", "source": "non-db"
                },
                "cases": {
                    "name": "cases", "vname": "LBL_EMAILS_CASES_REL", "type": "link", "relationship": "emails_cases_rel", "module": "Cases", "bean_name": "Case", "source": "non-db"
                },
                "contacts": {
                    "name": "contacts", "vname": "LBL_EMAILS_CONTACTS_REL", "type": "link", "relationship": "emails_contacts_rel", "module": "Contacts", "bean_name": "Contact", "source": "non-db"
                },
                "leads": {
                    "name": "leads", "vname": "LBL_EMAILS_LEADS_REL", "type": "link", "relationship": "emails_leads_rel", "module": "Leads", "bean_name": "Lead", "source": "non-db"
                },
                "opportunities": {
                    "name": "opportunities", "vname": "LBL_EMAILS_OPPORTUNITIES_REL", "type": "link", "relationship": "emails_opportunities_rel", "module": "Opportunities", "bean_name": "Opportunity", "source": "non-db"
                },
                "project": {
                    "name": "project", "vname": "LBL_EMAILS_PROJECT_REL", "type": "link", "relationship": "emails_projects_rel", "module": "Project", "bean_name": "Project", "source": "non-db"
                },
                "projecttask": {
                    "name": "projecttask", "vname": "LBL_EMAILS_PROJECT_TASK_REL", "type": "link", "relationship": "emails_project_task_rel", "module": "ProjectTask", "bean_name": "ProjectTask", "source": "non-db"
                },
                "prospects": {
                    "name": "prospects", "vname": "LBL_EMAILS_PROSPECT_REL", "type": "link", "relationship": "emails_prospects_rel", "module": "Prospects", "bean_name": "Prospect", "source": "non-db"
                },
                "quotes": {
                    "name": "quotes", "vname": "LBL_EMAILS_QUOTES_REL", "type": "link", "relationship": "emails_quotes", "module": "Quotes", "bean_name": "Quote", "source": "non-db"
                },
                "tasks": {
                    "name": "tasks", "vname": "LBL_EMAILS_TASKS_REL", "type": "link", "relationship": "emails_tasks_rel", "module": "Tasks", "bean_name": "Task", "source": "non-db"
                },
                "users": {
                    "name": "users", "vname": "LBL_EMAILS_USERS_REL", "type": "link", "relationship": "emails_users_rel", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "notes": {
                    "name": "notes", "vname": "LBL_EMAILS_NOTES_REL", "type": "link", "relationship": "emails_notes_rel", "module": "Notes", "bean_name": "Note", "source": "non-db"
                },
                "meetings": {
                    "name": "meetings", "vname": "LBL_EMAILS_MEETINGS_REL", "type": "link", "relationship": "emails_meetings_rel", "module": "Meetings", "bean_name": "Meeting", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "d060df38ce44b9c9dfe224303814f641"
        },
        "Campaigns": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_CAMPAIGN_NAME", "dbType": "varchar", "type": "name", "len": "50", "comment": "The name of the campaign", "importable": "required", "required": true, "unified_search": true, "full_text_search": {
                        "boost": 3
                    }
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "type": "none", "comment": "inhertied but not used", "source": "non-db"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "campaigns_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "campaigns_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "campaigns_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "campaigns_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "campaigns_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "campaigns_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "tracker_key": {
                    "name": "tracker_key", "vname": "LBL_TRACKER_KEY", "type": "int", "required": true, "studio": {
                        "editview": false
                    },
                    "len": "11", "auto_increment": true, "comment": "The internal ID of the tracker used in a campaign; no longer used as of 4.2 (see campaign_trkrs)"
                },
                "tracker_count": {
                    "name": "tracker_count", "vname": "LBL_TRACKER_COUNT", "type": "int", "len": "11", "default": "0", "comment": "The number of accesses made to the tracker URL; no longer used as of 4.2 (see campaign_trkrs)"
                },
                "refer_url": {
                    "name": "refer_url", "vname": "LBL_REFER_URL", "type": "varchar", "len": "255", "default": "http:\/\/", "comment": "The URL referenced in the tracker URL; no longer used as of 4.2 (see campaign_trkrs)"
                },
                "tracker_text": {
                    "name": "tracker_text", "vname": "LBL_TRACKER_TEXT", "type": "varchar", "len": "255", "comment": "The text that appears in the tracker URL; no longer used as of 4.2 (see campaign_trkrs)"
                },
                "start_date": {
                    "name": "start_date", "vname": "LBL_CAMPAIGN_START_DATE", "type": "date", "audited": true, "comment": "Starting date of the campaign", "validation": {
                        "type": "isbefore", "compareto": "end_date"
                    },
                    "enable_range_search": true, "options": "date_range_search_dom"
                },
                "end_date": {
                    "name": "end_date", "vname": "LBL_CAMPAIGN_END_DATE", "type": "date", "audited": true, "comment": "Ending date of the campaign", "importable": "required", "required": true, "enable_range_search": true, "options": "date_range_search_dom"
                },
                "status": {
                    "name": "status", "vname": "LBL_CAMPAIGN_STATUS", "type": "enum", "options": "campaign_status_dom", "len": 100, "audited": true, "comment": "Status of the campaign", "importable": "required", "required": true
                },
                "impressions": {
                    "name": "impressions", "vname": "LBL_CAMPAIGN_IMPRESSIONS", "type": "int", "default": 0, "reportable": true, "comment": "Expected Click throughs manually entered by Campaign Manager"
                },
                "currency_id": {
                    "name": "currency_id", "vname": "LBL_CURRENCY", "type": "id", "group": "currency_id", "function": {
                        "name": "getCurrencyDropDown", "returns": "html"
                    },
                    "required": false, "do_report": false, "reportable": false, "comment": "Currency in use for the campaign"
                },
                "budget": {
                    "name": "budget", "vname": "LBL_CAMPAIGN_BUDGET", "type": "currency", "dbType": "double", "comment": "Budgeted amount for the campaign"
                },
                "expected_cost": {
                    "name": "expected_cost", "vname": "LBL_CAMPAIGN_EXPECTED_COST", "type": "currency", "dbType": "double", "comment": "Expected cost of the campaign"
                },
                "actual_cost": {
                    "name": "actual_cost", "vname": "LBL_CAMPAIGN_ACTUAL_COST", "type": "currency", "dbType": "double", "comment": "Actual cost of the campaign"
                },
                "expected_revenue": {
                    "name": "expected_revenue", "vname": "LBL_CAMPAIGN_EXPECTED_REVENUE", "type": "currency", "dbType": "double", "comment": "Expected revenue stemming from the campaign"
                },
                "campaign_type": {
                    "name": "campaign_type", "vname": "LBL_CAMPAIGN_TYPE", "type": "enum", "options": "campaign_type_dom", "len": 100, "audited": true, "comment": "The type of campaign", "importable": "required", "required": true
                },
                "objective": {
                    "name": "objective", "vname": "LBL_CAMPAIGN_OBJECTIVE", "type": "text", "comment": "The objective of the campaign"
                },
                "content": {
                    "name": "content", "vname": "LBL_CAMPAIGN_CONTENT", "type": "text", "comment": "The campaign description"
                },
                "prospectlists": {
                    "name": "prospectlists", "type": "link", "relationship": "prospect_list_campaigns", "source": "non-db"
                },
                "emailmarketing": {
                    "name": "emailmarketing", "type": "link", "relationship": "campaign_email_marketing", "source": "non-db"
                },
                "queueitems": {
                    "name": "queueitems", "type": "link", "relationship": "campaign_emailman", "source": "non-db"
                },
                "log_entries": {
                    "name": "log_entries", "type": "link", "relationship": "campaign_campaignlog", "source": "non-db", "vname": "LBL_LOG_ENTRIES"
                },
                "tracked_urls": {
                    "name": "tracked_urls", "type": "link", "relationship": "campaign_campaigntrakers", "source": "non-db", "vname": "LBL_TRACKED_URLS"
                },
                "frequency": {
                    "name": "frequency", "vname": "LBL_CAMPAIGN_FREQUENCY", "type": "enum", "len": 100, "comment": "Frequency of the campaign", "options": "newsletter_frequency_dom"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "campaign_leads", "source": "non-db", "vname": "LBL_LEADS", "link_class": "ProspectLink", "link_file": "modules\/Campaigns\/ProspectLink.php"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "campaign_opportunities", "source": "non-db", "vname": "LBL_OPPORTUNITIES"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "campaign_contacts", "source": "non-db", "vname": "LBL_CONTACTS", "link_class": "ProspectLink", "link_file": "modules\/Campaigns\/ProspectLink.php"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "campaign_accounts", "source": "non-db", "vname": "LBL_ACCOUNTS", "link_class": "ProspectLink", "link_file": "modules\/Campaigns\/ProspectLink.php"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "cdfc832305b74074b1565cce30d7766f"
        },
        "Calls": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_SUBJECT", "dbType": "varchar", "type": "name", "len": "50", "comment": "Brief description of the call", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "required": true, "importable": "required"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "calls_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "calls_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "calls_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "calls_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "calls_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "calls_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "duration_hours": {
                    "name": "duration_hours", "vname": "LBL_DURATION_HOURS", "type": "int", "len": "2", "comment": "Call duration, hours portion", "required": true
                },
                "duration_minutes": {
                    "name": "duration_minutes", "vname": "LBL_DURATION_MINUTES", "type": "int", "function": {
                        "name": "getDurationMinutesOptions", "returns": "html", "include": "modules\/Calls\/CallHelper.php"
                    },
                    "len": "2", "group": "duration_hours", "importable": "required", "comment": "Call duration, minutes portion"
                },
                "date_start": {
                    "name": "date_start", "vname": "LBL_DATE", "type": "datetimecombo", "dbType": "datetime", "comment": "Date in which call is schedule to (or did) start", "importable": "required", "required": true, "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_end": {
                    "name": "date_end", "vname": "LBL_DATE_END", "type": "datetimecombo", "dbType": "datetime", "massupdate": false, "comment": "Date is which call is scheduled to (or did) end", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "parent_type": {
                    "name": "parent_type", "vname": "LBL_PARENT_TYPE", "type": "parent_type", "dbType": "varchar", "required": false, "group": "parent_name", "options": "parent_type_display", "len": 255, "comment": "The Sugar object to which the call is related"
                },
                "parent_name": {
                    "name": "parent_name", "parent_type": "record_type_display", "type_name": "parent_type", "id_name": "parent_id", "vname": "LBL_LIST_RELATED_TO", "type": "parent", "group": "parent_name", "source": "non-db", "options": "parent_type_display"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "len": 100, "options": "call_status_dom", "comment": "The status of the call (Held, Not Held, etc.)", "required": true, "importable": "required", "default": "Planned", "studio": {
                        "detailview": false
                    }
                },
                "direction": {
                    "name": "direction", "vname": "LBL_DIRECTION", "type": "enum", "len": 100, "options": "call_direction_dom", "comment": "Indicates whether call is inbound or outbound"
                },
                "parent_id": {
                    "name": "parent_id", "vname": "LBL_LIST_RELATED_TO_ID", "type": "id", "group": "parent_name", "reportable": false, "comment": "The ID of the parent Sugar object identified by parent_type"
                },
                "reminder_checked": {
                    "name": "reminder_checked", "vname": "LBL_REMINDER", "type": "bool", "source": "non-db", "comment": "checkbox indicating whether or not the reminder value is set (Meta-data only)", "massupdate": false
                },
                "reminder_time": {
                    "name": "reminder_time", "vname": "LBL_REMINDER_TIME", "type": "enum", "dbType": "int", "options": "reminder_time_options", "reportable": false, "massupdate": false, "default": -1, "comment": "Specifies when a reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start"
                },
                "email_reminder_checked": {
                    "name": "email_reminder_checked", "vname": "LBL_EMAIL_REMINDER", "type": "bool", "source": "non-db", "comment": "checkbox indicating whether or not the email reminder value is set (Meta-data only)", "massupdate": false
                },
                "email_reminder_time": {
                    "name": "email_reminder_time", "vname": "LBL_EMAIL_REMINDER_TIME", "type": "enum", "dbType": "int", "options": "reminder_time_options", "reportable": false, "massupdate": false, "default": -1, "comment": "Specifies when a email reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start"
                },
                "email_reminder_sent": {
                    "name": "email_reminder_sent", "vname": "LBL_EMAIL_REMINDER_SENT", "default": 0, "type": "bool", "comment": "Whether email reminder is already sent", "studio": false, "massupdate": false
                },
                "outlook_id": {
                    "name": "outlook_id", "vname": "LBL_OUTLOOK_ID", "type": "varchar", "len": "255", "reportable": false, "comment": "When the Sugar Plug-in for Microsoft Outlook syncs an Outlook appointment, this is the Outlook appointment item ID"
                },
                "accept_status": {
                    "name": "accept_status", "vname": "LBL_ACCEPT_STATUS", "dbType": "varchar", "type": "varchar", "len": "20", "source": "non-db"
                },
                "set_accept_links": {
                    "name": "accept_status", "vname": "LBL_ACCEPT_LINK", "dbType": "varchar", "type": "varchar", "len": "20", "source": "non-db"
                },
                "contact_name": {
                    "name": "contact_name", "rname": "last_name", "db_concat_fields": ["first_name", "last_name"], "id_name": "contact_id", "massupdate": false, "vname": "LBL_CONTACT_NAME", "type": "relate", "link": "contacts", "table": "contacts", "isnull": "true", "module": "Contacts", "join_name": "contacts", "dbType": "varchar", "source": "non-db", "len": 36, "importable": "false", "studio": {
                        "required": false, "listview": true, "visible": false
                    }
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "opportunity_calls", "source": "non-db", "link_type": "one", "vname": "LBL_OPPORTUNITY"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "calls_leads", "source": "non-db", "vname": "LBL_LEADS"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_calls", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "case": {
                    "name": "case", "type": "link", "relationship": "case_calls", "source": "non-db", "link_type": "one", "vname": "LBL_CASE"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "account_calls", "module": "Accounts", "bean_name": "Account", "source": "non-db", "vname": "LBL_ACCOUNT"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "calls_contacts", "source": "non-db", "vname": "LBL_CONTACTS"
                },
                "users": {
                    "name": "users", "type": "link", "relationship": "calls_users", "source": "non-db", "vname": "LBL_USERS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "calls_notes", "module": "Notes", "bean_name": "Note", "source": "non-db", "vname": "LBL_NOTES"
                },
                "contact_id": {
                    "name": "contact_id", "type": "id", "source": "non-db"
                },
                "repeat_type": {
                    "name": "repeat_type", "vname": "LBL_REPEAT_TYPE", "type": "enum", "len": 36, "options": "repeat_type_dom", "comment": "Type of recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_interval": {
                    "name": "repeat_interval", "vname": "LBL_REPEAT_INTERVAL", "type": "int", "len": 3, "default": 1, "comment": "Interval of recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_dow": {
                    "name": "repeat_dow", "vname": "LBL_REPEAT_DOW", "type": "varchar", "len": 7, "comment": "Days of week in recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_until": {
                    "name": "repeat_until", "vname": "LBL_REPEAT_UNTIL", "type": "date", "comment": "Repeat until specified date", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_count": {
                    "name": "repeat_count", "vname": "LBL_REPEAT_COUNT", "type": "int", "len": 7, "comment": "Number of recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_parent_id": {
                    "name": "repeat_parent_id", "vname": "LBL_REPEAT_PARENT_ID", "type": "id", "len": 36, "comment": "Id of the first element of recurring records", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "recurring_source": {
                    "name": "recurring_source", "vname": "LBL_RECURRING_SOURCE", "type": "varchar", "len": 36, "comment": "Source of recurring call", "importable": false, "massupdate": false, "reportable": false, "studio": false
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "date_start",
                                "direction",
                                "status",
                                "duration_hours",
                                "duration_minutes",
                                "description",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "date_start",
                                "direction",
                                "status",
                                "duration_hours",
                                "duration_minutes",
                                "description",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "29f58e0ed3f48bf9847c2948299ce682"
        },
        "Meetings": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_SUBJECT", "required": true, "type": "name", "dbType": "varchar", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "len": "50", "comment": "Meeting name", "importable": "required"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "meetings_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "meetings_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "meetings_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "meetings_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "meetings_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "meetings_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "accept_status": {
                    "name": "accept_status", "vname": "LBL_ACCEPT_STATUS", "type": "varchar", "dbType": "varchar", "len": "20", "source": "non-db"
                },
                "set_accept_links": {
                    "name": "accept_status", "vname": "LBL_ACCEPT_LINK", "type": "varchar", "dbType": "varchar", "len": "20", "source": "non-db"
                },
                "location": {
                    "name": "location", "vname": "LBL_LOCATION", "type": "varchar", "len": "50", "comment": "Meeting location"
                },
                "password": {
                    "name": "password", "vname": "LBL_PASSWORD", "type": "varchar", "len": "50", "comment": "Meeting password", "studio": {
                        "wirelesseditview": false, "wirelessdetailview": false, "wirelesslistview": false, "wireless_basic_search": false
                    },
                    "dependency": "isInEnum($type,getDD(\"extapi_meeting_password\"))"
                },
                "join_url": {
                    "name": "join_url", "vname": "LBL_URL", "type": "varchar", "len": "200", "comment": "Join URL", "studio": "false", "reportable": false
                },
                "host_url": {
                    "name": "host_url", "vname": "LBL_HOST_URL", "type": "varchar", "len": "400", "comment": "Host URL", "studio": "false", "reportable": false
                },
                "displayed_url": {
                    "name": "displayed_url", "vname": "LBL_DISPLAYED_URL", "type": "url", "len": "400", "comment": "Meeting URL", "studio": {
                        "wirelesseditview": false, "wirelessdetailview": false, "wirelesslistview": false, "wireless_basic_search": false
                    },
                    "dependency": "and(isAlpha($type),not(equal($type,\"Sugar\")))"
                },
                "creator": {
                    "name": "creator", "vname": "LBL_CREATOR", "type": "varchar", "len": "50", "comment": "Meeting creator", "studio": "false"
                },
                "external_id": {
                    "name": "external_id", "vname": "LBL_EXTERNALID", "type": "varchar", "len": "50", "comment": "Meeting ID for external app API", "studio": "false"
                },
                "duration_hours": {
                    "name": "duration_hours", "vname": "LBL_DURATION_HOURS", "type": "int", "len": "3", "comment": "Duration (hours)", "importable": "required", "required": true
                },
                "duration_minutes": {
                    "name": "duration_minutes", "vname": "LBL_DURATION_MINUTES", "type": "int", "group": "duration_hours", "len": "2", "comment": "Duration (minutes)"
                },
                "date_start": {
                    "name": "date_start", "vname": "LBL_DATE", "type": "datetimecombo", "dbType": "datetime", "comment": "Date of start of meeting", "importable": "required", "required": true, "enable_range_search": true, "options": "date_range_search_dom", "validation": {
                        "type": "isbefore", "compareto": "date_end", "blank": false
                    }
                },
                "date_end": {
                    "name": "date_end", "vname": "LBL_DATE_END", "type": "datetimecombo", "dbType": "datetime", "massupdate": false, "comment": "Date meeting ends", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "parent_type": {
                    "name": "parent_type", "vname": "LBL_PARENT_TYPE", "type": "parent_type", "dbType": "varchar", "group": "parent_name", "options": "parent_type_display", "len": 100, "comment": "Module meeting is associated with", "studio": {
                        "searchview": false
                    }
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "len": 100, "options": "meeting_status_dom", "comment": "Meeting status (ex: Planned, Held, Not held)", "default": "Planned"
                },
                "type": {
                    "name": "type", "vname": "LBL_TYPE", "type": "enum", "len": 255, "function": "getMeetingsExternalApiDropDown", "comment": "Meeting type (ex: WebEx, Other)", "options": "eapm_list", "default": "Sugar", "massupdate": false, "studio": {
                        "wirelesseditview": false, "wirelessdetailview": false, "wirelesslistview": false, "wireless_basic_search": false
                    }
                },
                "direction": {
                    "name": "direction", "vname": "LBL_DIRECTION", "type": "enum", "len": 100, "options": "call_direction_dom", "comment": "Indicates whether call is inbound or outbound", "source": "non-db", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "parent_id": {
                    "name": "parent_id", "vname": "LBL_PARENT_ID", "type": "id", "group": "parent_name", "reportable": false, "comment": "ID of item indicated by parent_type", "studio": {
                        "searchview": false
                    }
                },
                "reminder_checked": {
                    "name": "reminder_checked", "vname": "LBL_REMINDER", "type": "bool", "source": "non-db", "comment": "checkbox indicating whether or not the reminder value is set (Meta-data only)", "massupdate": false
                },
                "reminder_time": {
                    "name": "reminder_time", "vname": "LBL_REMINDER_TIME", "type": "enum", "dbType": "int", "options": "reminder_time_options", "reportable": false, "massupdate": false, "default": -1, "comment": "Specifies when a reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start"
                },
                "email_reminder_checked": {
                    "name": "email_reminder_checked", "vname": "LBL_EMAIL_REMINDER", "type": "bool", "source": "non-db", "comment": "checkbox indicating whether or not the email reminder value is set (Meta-data only)", "massupdate": false
                },
                "email_reminder_time": {
                    "name": "email_reminder_time", "vname": "LBL_EMAIL_REMINDER_TIME", "type": "enum", "dbType": "int", "options": "reminder_time_options", "reportable": false, "massupdate": false, "default": -1, "comment": "Specifies when a email reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start"
                },
                "email_reminder_sent": {
                    "name": "email_reminder_sent", "vname": "LBL_EMAIL_REMINDER_SENT", "default": 0, "type": "bool", "comment": "Whether email reminder is already sent", "studio": false, "massupdate": false
                },
                "outlook_id": {
                    "name": "outlook_id", "vname": "LBL_OUTLOOK_ID", "type": "varchar", "len": "255", "reportable": false, "comment": "When the Sugar Plug-in for Microsoft Outlook syncs an Outlook appointment, this is the Outlook appointment item ID"
                },
                "sequence": {
                    "name": "sequence", "vname": "LBL_SEQUENCE", "type": "int", "len": "11", "reportable": false, "default": 0, "comment": "Meeting update sequence for meetings as per iCalendar standards", "studio": {
                        "related": false, "formula": false, "rollup": false
                    }
                },
                "contact_name": {
                    "name": "contact_name", "rname": "last_name", "db_concat_fields": ["first_name", "last_name"], "id_name": "contact_id", "massupdate": false, "vname": "LBL_CONTACT_NAME", "type": "relate", "link": "contacts", "table": "contacts", "isnull": "true", "module": "Contacts", "join_name": "contacts", "dbType": "varchar", "source": "non-db", "len": 36, "studio": "false"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "meetings_contacts", "source": "non-db", "vname": "LBL_CONTACTS"
                },
                "parent_name": {
                    "name": "parent_name", "parent_type": "record_type_display", "type_name": "parent_type", "id_name": "parent_id", "vname": "LBL_LIST_RELATED_TO", "type": "parent", "group": "parent_name", "source": "non-db", "options": "parent_type_display"
                },
                "users": {
                    "name": "users", "type": "link", "relationship": "meetings_users", "source": "non-db", "vname": "LBL_USERS"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "account_meetings", "source": "non-db", "vname": "LBL_ACCOUNT"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "meetings_leads", "source": "non-db", "vname": "LBL_LEADS"
                },
                "opportunity": {
                    "name": "opportunity", "type": "link", "relationship": "opportunity_meetings", "source": "non-db", "vname": "LBL_OPPORTUNITY"
                },
                "case": {
                    "name": "case", "type": "link", "relationship": "case_meetings", "source": "non-db", "vname": "LBL_CASE"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "meetings_notes", "module": "Notes", "bean_name": "Note", "source": "non-db", "vname": "LBL_NOTES"
                },
                "contact_id": {
                    "name": "contact_id", "type": "id", "source": "non-db"
                },
                "repeat_type": {
                    "name": "repeat_type", "vname": "LBL_REPEAT_TYPE", "type": "enum", "len": 36, "options": "repeat_type_dom", "comment": "Type of recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_interval": {
                    "name": "repeat_interval", "vname": "LBL_REPEAT_INTERVAL", "type": "int", "len": 3, "default": 1, "comment": "Interval of recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_dow": {
                    "name": "repeat_dow", "vname": "LBL_REPEAT_DOW", "type": "varchar", "len": 7, "comment": "Days of week in recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_until": {
                    "name": "repeat_until", "vname": "LBL_REPEAT_UNTIL", "type": "date", "comment": "Repeat until specified date", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_count": {
                    "name": "repeat_count", "vname": "LBL_REPEAT_COUNT", "type": "int", "len": 7, "comment": "Number of recurrence", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "repeat_parent_id": {
                    "name": "repeat_parent_id", "vname": "LBL_REPEAT_PARENT_ID", "type": "id", "len": 36, "comment": "Id of the first element of recurring records", "importable": "false", "massupdate": false, "reportable": false, "studio": "false"
                },
                "recurring_source": {
                    "name": "recurring_source", "vname": "LBL_RECURRING_SOURCE", "type": "varchar", "len": 36, "comment": "Source of recurring meeting", "importable": false, "massupdate": false, "reportable": false, "studio": false
                },
                "duration": {
                    "name": "duration", "vname": "LBL_DURATION", "type": "enum", "options": "duration_dom", "source": "non-db", "comment": "Duration handler dropdown", "massupdate": false, "reportable": false, "importable": false
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "date_start",
                                "status",
                                "duration_hours",
                                "duration_minutes",
                                "description",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "date_start",
                                "status",
                                "duration_hours",
                                "duration_minutes",
                                "description",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "073b9e71ee8f325d6541f67c1022545d"
        },
        "Tasks": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_SUBJECT", "dbType": "varchar", "type": "name", "len": "50", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "importable": "required", "required": "true"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "tasks_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "tasks_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "tasks_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "tasks_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "tasks_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "tasks_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "options": "task_status_dom", "len": 100, "required": "true", "default": "Not Started"
                },
                "date_due_flag": {
                    "name": "date_due_flag", "vname": "LBL_DATE_DUE_FLAG", "type": "bool", "default": 0, "group": "date_due", "studio": false
                },
                "date_due": {
                    "name": "date_due", "vname": "LBL_DUE_DATE", "type": "datetimecombo", "dbType": "datetime", "group": "date_due", "studio": {
                        "required": true, "no_duplicate": true
                    },
                    "enable_range_search": true, "options": "date_range_search_dom"
                },
                "time_due": {
                    "name": "time_due", "vname": "LBL_DUE_TIME", "type": "datetime", "source": "non-db", "importable": "false", "massupdate": false
                },
                "date_start_flag": {
                    "name": "date_start_flag", "vname": "LBL_DATE_START_FLAG", "type": "bool", "group": "date_start", "default": 0, "studio": false
                },
                "date_start": {
                    "name": "date_start", "vname": "LBL_START_DATE", "type": "datetimecombo", "dbType": "datetime", "group": "date_start", "validation": {
                        "type": "isbefore", "compareto": "date_due", "blank": false
                    },
                    "studio": {
                        "required": true, "no_duplicate": true
                    },
                    "enable_range_search": true, "options": "date_range_search_dom"
                },
                "parent_type": {
                    "name": "parent_type", "vname": "LBL_PARENT_NAME", "type": "parent_type", "dbType": "varchar", "group": "parent_name", "options": "parent_type_display", "required": false, "len": "255", "comment": "The Sugar object to which the call is related"
                },
                "parent_name": {
                    "name": "parent_name", "parent_type": "record_type_display", "type_name": "parent_type", "id_name": "parent_id", "vname": "LBL_LIST_RELATED_TO", "type": "parent", "group": "parent_name", "source": "non-db", "options": "parent_type_display"
                },
                "parent_id": {
                    "name": "parent_id", "type": "id", "group": "parent_name", "reportable": false, "vname": "LBL_PARENT_ID"
                },
                "contact_id": {
                    "name": "contact_id", "type": "id", "group": "contact_name", "reportable": false, "vname": "LBL_CONTACT_ID"
                },
                "contact_name": {
                    "name": "contact_name", "rname": "last_name", "db_concat_fields": ["first_name", "last_name"], "source": "non-db", "len": "510", "group": "contact_name", "vname": "LBL_CONTACT_NAME", "reportable": false, "id_name": "contact_id", "join_name": "contacts", "type": "relate", "module": "Contacts", "link": "contacts", "table": "contacts"
                },
                "contact_phone": {
                    "name": "contact_phone", "type": "phone", "source": "non-db", "vname": "LBL_CONTACT_PHONE", "studio": {
                        "listview": true
                    }
                },
                "contact_email": {
                    "name": "contact_email", "type": "varchar", "vname": "LBL_EMAIL_ADDRESS", "source": "non-db", "studio": false
                },
                "priority": {
                    "name": "priority", "vname": "LBL_PRIORITY", "type": "enum", "options": "task_priority_dom", "len": 100, "required": "true"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "contact_tasks", "source": "non-db", "side": "right", "vname": "LBL_CONTACT"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "account_tasks", "source": "non-db", "vname": "LBL_ACCOUNT"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "opportunity_tasks", "source": "non-db", "vname": "LBL_OPPORTUNITY"
                },
                "cases": {
                    "name": "cases", "type": "link", "relationship": "case_tasks", "source": "non-db", "vname": "LBL_CASE"
                },
                "bugs": {
                    "name": "bugs", "type": "link", "relationship": "bug_tasks", "source": "non-db", "vname": "LBL_BUGS"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "lead_tasks", "source": "non-db", "vname": "LBL_LEADS"
                },
                "projects": {
                    "name": "projects", "type": "link", "relationship": "projects_tasks", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "project_tasks": {
                    "name": "project_tasks", "type": "link", "relationship": "project_tasks_tasks", "source": "non-db", "vname": "LBL_PROJECT_TASKS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "tasks_notes", "module": "Notes", "bean_name": "Note", "source": "non-db", "vname": "LBL_NOTES"
                },
                "quotes": {
                    "name": "quotes", "type": "link", "relationship": "quote_tasks", "vname": "LBL_QUOTES", "source": "non-db"
                },
                "contact_parent": {
                    "name": "contact_parent", "type": "link", "relationship": "contact_tasks_parent", "source": "non-db", "reportable": false
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "priority",
                                "status",
                                "date_start",
                                "date_due",
                                "description",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "priority",
                                "status",
                                "date_start",
                                "date_due",
                                "description",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "e05892d2864444005c7be11d674d67cc"
        },
        "Notes": {
            "fields": {
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "notes_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "notes_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "notes_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "notes_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "comment": "Date record last modified", "enable_range_search": true
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record"
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_BY", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled"
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED_BY", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "comment": "User who created record"
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED_BY", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false"
                },
                "name": {
                    "name": "name", "vname": "LBL_NOTE_SUBJECT", "dbType": "varchar", "type": "name", "len": "255", "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "Name of the note", "importable": "required", "required": true
                },
                "file_mime_type": {
                    "name": "file_mime_type", "vname": "LBL_FILE_MIME_TYPE", "type": "varchar", "len": "100", "comment": "Attachment MIME type", "importable": false
                },
                "file_url": {
                    "name": "file_url", "vname": "LBL_FILE_URL", "type": "function", "function_class": "UploadFile", "function_name": "get_upload_url", "function_params": ["$this"], "source": "function", "reportable": false, "comment": "Path to file (can be URL)", "importable": false
                },
                "filename": {
                    "name": "filename", "vname": "LBL_FILENAME", "type": "file", "dbType": "varchar", "len": "255", "reportable": true, "comment": "File name associated with the note (attachment)", "importable": false
                },
                "parent_type": {
                    "name": "parent_type", "vname": "LBL_PARENT_TYPE", "type": "parent_type", "dbType": "varchar", "group": "parent_name", "options": "parent_type_display", "len": "255", "comment": "Sugar module the Note is associated with"
                },
                "parent_id": {
                    "name": "parent_id", "vname": "LBL_PARENT_ID", "type": "id", "required": false, "reportable": true, "comment": "The ID of the Sugar item specified in parent_type"
                },
                "contact_id": {
                    "name": "contact_id", "vname": "LBL_CONTACT_ID", "type": "id", "required": false, "reportable": false, "comment": "Contact ID note is associated with"
                },
                "portal_flag": {
                    "name": "portal_flag", "vname": "LBL_PORTAL_FLAG", "type": "bool", "required": true, "comment": "Portal flag indicator determines if note created via portal"
                },
                "embed_flag": {
                    "name": "embed_flag", "vname": "LBL_EMBED_FLAG", "type": "bool", "default": 0, "comment": "Embed flag indicator determines if note embedded in email"
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 30, "cols": 90
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "parent_name": {
                    "name": "parent_name", "parent_type": "record_type_display", "type_name": "parent_type", "id_name": "parent_id", "vname": "LBL_RELATED_TO", "type": "parent", "source": "non-db", "options": "record_type_display_notes"
                },
                "contact_name": {
                    "name": "contact_name", "rname": "name", "id_name": "contact_id", "vname": "LBL_CONTACT_NAME", "table": "contacts", "type": "relate", "link": "contact", "join_name": "contacts", "db_concat_fields": ["first_name", "last_name"], "isnull": "true", "module": "Contacts", "source": "non-db"
                },
                "contact_phone": {
                    "name": "contact_phone", "vname": "LBL_PHONE", "type": "phone", "source": "non-db"
                },
                "contact_email": {
                    "name": "contact_email", "type": "varchar", "vname": "LBL_EMAIL_ADDRESS", "source": "non-db", "studio": false
                },
                "account_id": {
                    "name": "account_id", "vname": "LBL_ACCOUNT_ID", "type": "id", "reportable": false, "source": "non-db"
                },
                "opportunity_id": {
                    "name": "opportunity_id", "vname": "LBL_OPPORTUNITY_ID", "type": "id", "reportable": false, "source": "non-db"
                },
                "acase_id": {
                    "name": "acase_id", "vname": "LBL_CASE_ID", "type": "id", "reportable": false, "source": "non-db"
                },
                "lead_id": {
                    "name": "lead_id", "vname": "LBL_LEAD_ID", "type": "id", "reportable": false, "source": "non-db"
                },
                "product_id": {
                    "name": "product_id", "vname": "LBL_PRODUCT_ID", "type": "id", "reportable": false, "source": "non-db"
                },
                "quote_id": {
                    "name": "quote_id", "vname": "LBL_QUOTE_ID", "type": "id", "reportable": false, "source": "non-db"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "notes_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "notes_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "contact": {
                    "name": "contact", "type": "link", "relationship": "contact_notes", "vname": "LBL_LIST_CONTACT_NAME", "source": "non-db"
                },
                "cases": {
                    "name": "cases", "type": "link", "relationship": "case_notes", "vname": "LBL_CASES", "source": "non-db"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "account_notes", "source": "non-db", "vname": "LBL_ACCOUNTS"
                },
                "opportunities": {
                    "name": "opportunities", "type": "link", "relationship": "opportunity_notes", "source": "non-db", "vname": "LBL_OPPORTUNITIES"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "lead_notes", "source": "non-db", "vname": "LBL_LEADS"
                },
                "products": {
                    "name": "products", "type": "link", "relationship": "product_notes", "source": "non-db", "vname": "LBL_PRODUCTS"
                },
                "quotes": {
                    "name": "quotes", "type": "link", "relationship": "quote_notes", "vname": "LBL_QUOTES", "source": "non-db"
                },
                "contracts": {
                    "name": "contracts", "type": "link", "relationship": "contract_notes", "source": "non-db", "vname": "LBL_CONTRACTS"
                },
                "bugs": {
                    "name": "bugs", "type": "link", "relationship": "bug_notes", "source": "non-db", "vname": "LBL_BUGS"
                },
                "emails": {
                    "name": "emails", "vname": "LBL_EMAILS", "type": "link", "relationship": "emails_notes_rel", "source": "non-db"
                },
                "projects": {
                    "name": "projects", "type": "link", "relationship": "projects_notes", "source": "non-db", "vname": "LBL_PROJECTS"
                },
                "project_tasks": {
                    "name": "project_tasks", "type": "link", "relationship": "project_tasks_notes", "source": "non-db", "vname": "LBL_PROJECT_TASKS"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "meetings_notes", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "calls_notes", "source": "non-db", "vname": "LBL_CALLS"
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "tasks_notes", "source": "non-db", "vname": "LBL_TASKS"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "9a0ad629b93a9d3d5b70abe6432f7fab"
        },
        "Cases": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "name": {
                    "name": "name", "vname": "LBL_SUBJECT", "type": "name", "link": true, "dbType": "varchar", "len": 255, "audited": true, "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "The short description of the bug", "merge_filter": "selected", "required": true, "importable": "required"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "group": "created_by_name", "comment": "Date record created", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "group": "modified_by_name", "comment": "Date record last modified", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "users", "isnull": "false", "group": "modified_by_name", "dbType": "id", "reportable": true, "comment": "User who last modified record", "massupdate": false
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled", "massupdate": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "group": "created_by_name", "comment": "User who created record", "massupdate": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false", "massupdate": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "Full text of the note", "rows": 6, "cols": 80
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "comment": "Record deletion indicator"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "cases_created_by", "vname": "LBL_CREATED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "cases_modified_user", "vname": "LBL_MODIFIED_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "cases_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "cases_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "cases_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "cases_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "case_number": {
                    "name": "case_number", "vname": "LBL_NUMBER", "type": "int", "readonly": true, "len": 11, "required": true, "auto_increment": true, "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "comment": "Visual unique identifier", "duplicate_merge": "disabled", "disable_num_format": true
                },
                "type": {
                    "name": "type", "vname": "LBL_TYPE", "type": "enum", "options": "case_type_dom", "len": 255, "comment": "The type of issue (ex: issue, feature)", "merge_filter": "enabled"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "options": "case_status_dom", "len": 100, "audited": true, "comment": "The status of the case", "merge_filter": "enabled"
                },
                "priority": {
                    "name": "priority", "vname": "LBL_PRIORITY", "type": "enum", "options": "case_priority_dom", "len": 100, "audited": true, "comment": "The priority of the case", "merge_filter": "enabled"
                },
                "resolution": {
                    "name": "resolution", "vname": "LBL_RESOLUTION", "type": "text", "comment": "The resolution of the case"
                },
                "system_id": {
                    "name": "system_id", "vname": "LBL_SYSTEM_ID", "type": "int", "comment": "The offline client device that created the bug"
                },
                "work_log": {
                    "name": "work_log", "vname": "LBL_WORK_LOG", "type": "text", "comment": "Free-form text used to denote activities of interest"
                },
                "account_name": {
                    "name": "account_name", "rname": "name", "id_name": "account_id", "vname": "LBL_ACCOUNT_NAME", "type": "relate", "link": "accounts", "table": "accounts", "join_name": "accounts", "isnull": "true", "module": "Accounts", "dbType": "varchar", "len": 100, "source": "non-db", "unified_search": true, "comment": "The name of the account represented by the account_id field", "required": true, "importable": "required"
                },
                "account_name1": {
                    "name": "account_name1", "source": "non-db", "type": "text", "len": 100, "importable": "false", "studio": {
                        "formula": false
                    }
                },
                "account_id": {
                    "name": "account_id", "type": "relate", "dbType": "id", "rname": "id", "module": "Accounts", "id_name": "account_id", "reportable": false, "vname": "LBL_ACCOUNT_ID", "audited": true, "massupdate": false, "comment": "The account to which the case is associated"
                },
                "portal_viewable": {
                    "name": "portal_viewable", "vname": "LBL_SHOW_IN_PORTAL", "type": "bool", "default": 0, "reportable": false
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "case_tasks", "source": "non-db", "vname": "LBL_TASKS"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "case_notes", "source": "non-db", "vname": "LBL_NOTES"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "case_meetings", "bean_name": "Meeting", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_cases_rel", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "documents": {
                    "name": "documents", "type": "link", "relationship": "documents_cases", "source": "non-db", "vname": "LBL_DOCUMENTS_SUBPANEL_TITLE"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "case_calls", "source": "non-db", "vname": "LBL_CALLS"
                },
                "bugs": {
                    "name": "bugs", "type": "link", "relationship": "cases_bugs", "source": "non-db", "vname": "LBL_BUGS"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "contacts_cases", "source": "non-db", "vname": "LBL_CONTACTS"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "account_cases", "link_type": "one", "side": "right", "source": "non-db", "vname": "LBL_ACCOUNT"
                },
                "project": {
                    "name": "project", "type": "link", "relationship": "projects_cases", "source": "non-db", "vname": "LBL_PROJECTS"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "case_number", "displayParams": {"required": false, "wireless_detail_only": true}},
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "account_name",
                                "priority",
                                "status",
                                "description",
                                "resolution",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "case_number", "displayParams": {"required": false, "wireless_detail_only": true}},
                                {"name": "name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                "account_name",
                                "priority",
                                "status",
                                "description",
                                "resolution",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "case_number", "label": "LBL_NUMBER", "link": true, "default": true, "enabled": true},
                                {"name": "name", "width": "32", "label": "LBL_SUBJECT", "link": true, "default": true, "enabled": true},
                                {"name": "status", "width": "10", "label": "LBL_STATUS", "default": true, "enabled": true},
                                {"name": "priority", "width": "10", "label": "LBL_PRIORITY", "default": true, "enabled": true},
                                {"name": "resolution", "width": "10", "label": "LBL_RESOLUTION", "default": true, "enabled": true},
                                {"name": "team_name", "width": "9", "label": "LBL_TEAM", "default": true, "enabled": true},
                                {"name": "assigned_user_name", "width": "9", "label": "LBL_ASSIGNED_USER", "default": true, "enabled": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["case_number", "priority"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "932dfa9498831eefbd340f6f082f3c55"
        },
        "ProspectLists": {
            "fields": {
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "prospectlists_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "prospectlists_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "prospectlists_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "prospectlists_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": false
                },
                "name": {
                    "name": "name", "vname": "LBL_NAME", "type": "varchar", "len": "50", "importable": "required", "unified_search": true, "full_text_search": {
                        "boost": 3
                    }
                },
                "list_type": {
                    "name": "list_type", "vname": "LBL_TYPE", "type": "enum", "options": "prospect_list_type_dom", "len": 100, "importable": "required"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "modified_user_id_users", "isnull": "false", "dbType": "id", "reportable": true
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "modified_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "created_by", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "created_by_users", "isnull": "false", "dbType": "id"
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_CREATED_BY", "type": "bool", "required": false, "reportable": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text"
                },
                "domain_name": {
                    "name": "domain_name", "vname": "LBL_DOMAIN_NAME", "type": "varchar", "len": "255"
                },
                "entry_count": {
                    "name": "entry_count", "type": "int", "source": "non-db", "vname": "LBL_LIST_ENTRIES"
                },
                "prospects": {
                    "name": "prospects", "type": "link", "relationship": "prospect_list_prospects", "source": "non-db"
                },
                "contacts": {
                    "name": "contacts", "type": "link", "relationship": "prospect_list_contacts", "source": "non-db"
                },
                "leads": {
                    "name": "leads", "type": "link", "relationship": "prospect_list_leads", "source": "non-db"
                },
                "accounts": {
                    "name": "accounts", "type": "link", "relationship": "prospect_list_accounts", "source": "non-db"
                },
                "campaigns": {
                    "name": "campaigns", "type": "link", "relationship": "prospect_list_campaigns", "source": "non-db"
                },
                "users": {
                    "name": "users", "type": "link", "relationship": "prospect_list_users", "source": "non-db"
                },
                "email_marketing": {
                    "name": "email_marketing", "type": "link", "relationship": "email_marketing_prospect_lists", "source": "non-db"
                },
                "marketing_id": {
                    "name": "marketing_id", "vname": "LBL_MARKETING_ID", "type": "varchar", "len": "36", "source": "non-db"
                },
                "marketing_name": {
                    "name": "marketing_name", "vname": "LBL_MARKETING_NAME", "type": "varchar", "len": "255", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "03e8728c94239c9e56c43cd0db9f34a8"
        },
        "Currencies": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_NAME", "type": "id", "required": true, "reportable": false, "comment": "Unique identifer"
                },
                "name": {
                    "name": "name", "vname": "LBL_LIST_NAME", "type": "varchar", "len": "36", "required": true, "comment": "Name of the currency", "importable": "required"
                },
                "symbol": {
                    "name": "symbol", "vname": "LBL_LIST_SYMBOL", "type": "varchar", "len": "36", "required": true, "comment": "Symbol representing the currency", "importable": "required"
                },
                "iso4217": {
                    "name": "iso4217", "vname": "LBL_LIST_ISO4217", "type": "varchar", "len": "3", "comment": "3-letter identifier specified by ISO 4217 (ex: USD)"
                },
                "conversion_rate": {
                    "name": "conversion_rate", "vname": "LBL_LIST_RATE", "type": "float", "dbType": "double", "default": "0", "required": true, "comment": "Conversion rate factor (relative to stored value)", "importable": "required"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "dbType": "varchar", "options": "currency_status_dom", "len": 100, "comment": "Currency status", "importable": "required"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "reportable": false, "comment": "Record deletion indicator"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true, "comment": "Date record created"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true, "comment": "Date record last modified"
                },
                "created_by": {
                    "name": "created_by", "reportable": false, "vname": "LBL_CREATED_BY", "type": "id", "len": "36", "required": true, "comment": "User ID who created record"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "dda4042e8376c4415235f4c3d18e00b8"
        },
        "Employees": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true
                },
                "user_name": {
                    "name": "user_name", "vname": "LBL_USER_NAME", "type": "user_name", "dbType": "varchar", "len": "60", "importable": "required", "required": true, "studio": {
                        "no_duplicate": true, "editview": false, "detailview": true, "quickcreate": false, "basic_search": false, "advanced_search": false, "wirelesseditview": false, "wirelessdetailview": true, "wirelesslistview": false, "wireless_basic_search": false, "wireless_advanced_search": false, "rollup": false
                    }
                },
                "user_hash": {
                    "name": "user_hash", "vname": "LBL_USER_HASH", "type": "varchar", "len": "255", "reportable": false, "importable": "false", "studio": {
                        "no_duplicate": true, "listview": false, "searchview": false, "related": false, "formula": false, "rollup": false
                    }
                },
                "system_generated_password": {
                    "name": "system_generated_password", "vname": "LBL_SYSTEM_GENERATED_PASSWORD", "type": "bool", "required": true, "reportable": false, "massupdate": false, "studio": {
                        "listview": false, "searchview": false, "editview": false, "quickcreate": false, "wirelesseditview": false, "related": false, "formula": false, "rollup": false
                    }
                },
                "pwd_last_changed": {
                    "name": "pwd_last_changed", "vname": "LBL_PSW_MODIFIED", "type": "datetime", "required": false, "massupdate": false, "studio": {
                        "formula": false
                    }
                },
                "authenticate_id": {
                    "name": "authenticate_id", "vname": "LBL_AUTHENTICATE_ID", "type": "varchar", "len": "100", "reportable": false, "importable": "false", "studio": {
                        "listview": false, "searchview": false, "related": false
                    }
                },
                "sugar_login": {
                    "name": "sugar_login", "vname": "LBL_SUGAR_LOGIN", "type": "bool", "default": "1", "reportable": false, "massupdate": false, "importable": false, "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "picture": {
                    "name": "picture", "vname": "LBL_PICTURE_FILE", "type": "image", "dbType": "varchar", "len": "255", "width": "", "height": "", "border": ""
                },
                "first_name": {
                    "name": "first_name", "vname": "LBL_FIRST_NAME", "dbType": "varchar", "type": "name", "len": "30"
                },
                "last_name": {
                    "name": "last_name", "vname": "LBL_LAST_NAME", "dbType": "varchar", "type": "name", "len": "30", "importable": "required", "required": true
                },
                "full_name": {
                    "name": "full_name", "rname": "full_name", "vname": "LBL_NAME", "type": "name", "fields": ["first_name", "last_name"], "source": "non-db", "sort_on": "last_name", "sort_on2": "first_name", "db_concat_fields": ["first_name", "last_name"], "len": "510", "studio": {
                        "formula": false
                    }
                },
                "name": {
                    "name": "name", "rname": "name", "vname": "LBL_NAME", "type": "varchar", "source": "non-db", "len": "510", "db_concat_fields": ["first_name", "last_name"], "importable": "false"
                },
                "is_admin": {
                    "name": "is_admin", "vname": "LBL_IS_ADMIN", "type": "bool", "default": "0", "studio": {
                        "listview": false, "searchview": false, "related": false
                    },
                    "massupdate": false
                },
                "external_auth_only": {
                    "name": "external_auth_only", "vname": "LBL_EXT_AUTHENTICATE", "type": "bool", "reportable": false, "massupdate": false, "default": "0", "studio": {
                        "listview": false, "searchview": false, "related": false
                    }
                },
                "receive_notifications": {
                    "name": "receive_notifications", "vname": "LBL_RECEIVE_NOTIFICATIONS", "type": "bool", "default": "1", "massupdate": false, "studio": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true, "studio": {
                        "editview": false, "quickcreate": false, "wirelesseditview": false
                    }
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true, "studio": {
                        "editview": false, "quickcreate": false, "wirelesseditview": false
                    }
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED_BY_ID", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id"
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_BY", "type": "varchar", "source": "non-db", "studio": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "studio": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED_BY_NAME", "type": "varchar", "source": "non-db", "importable": "false", "studio": {
                        "related": false, "formula": false, "rollup": false
                    }
                },
                "title": {
                    "name": "title", "vname": "LBL_TITLE", "type": "varchar", "len": "50"
                },
                "department": {
                    "name": "department", "vname": "LBL_DEPARTMENT", "type": "varchar", "len": "50"
                },
                "phone_home": {
                    "name": "phone_home", "vname": "LBL_HOME_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_mobile": {
                    "name": "phone_mobile", "vname": "LBL_MOBILE_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_work": {
                    "name": "phone_work", "vname": "LBL_WORK_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_other": {
                    "name": "phone_other", "vname": "LBL_OTHER_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_fax": {
                    "name": "phone_fax", "vname": "LBL_FAX_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "len": 100, "options": "user_status_dom", "importable": "required", "required": false, "massupdate": false, "studio": false
                },
                "address_street": {
                    "name": "address_street", "vname": "LBL_ADDRESS_STREET", "type": "varchar", "len": "150"
                },
                "address_city": {
                    "name": "address_city", "vname": "LBL_ADDRESS_CITY", "type": "varchar", "len": "100"
                },
                "address_state": {
                    "name": "address_state", "vname": "LBL_ADDRESS_STATE", "type": "varchar", "len": "100"
                },
                "address_country": {
                    "name": "address_country", "vname": "LBL_ADDRESS_COUNTRY", "type": "varchar", "len": 100
                },
                "address_postalcode": {
                    "name": "address_postalcode", "vname": "LBL_ADDRESS_POSTALCODE", "type": "varchar", "len": "20"
                },
                "UserType": {
                    "name": "UserType", "vname": "LBL_USER_TYPE", "type": "enum", "len": 50, "options": "user_type_dom", "source": "non-db", "import": false, "reportable": false, "studio": {
                        "formula": false
                    },
                    "massupdate": false
                },
                "default_team": {
                    "name": "default_team", "vname": "LBL_DEFAULT_TEAM", "reportable": false, "type": "varchar", "len": "36", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_DEFAULT_TEAM", "reportable": false, "source": "non-db", "type": "varchar", "len": "36", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false"
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": true, "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset", "studio": {
                        "listview": false, "searchview": false, "editview": false, "quickcreate": false, "wirelesseditview": false
                    }
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "users_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "users_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "users_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "team_memberships": {
                    "name": "team_memberships", "type": "link", "relationship": "team_memberships", "source": "non-db", "vname": "LBL_TEAMS"
                },
                "users_signatures": {
                    "name": "users_signatures", "type": "link", "relationship": "users_users_signatures", "source": "non-db", "studio": "false", "reportable": false
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "reportable": false
                },
                "portal_only": {
                    "name": "portal_only", "vname": "LBL_PORTAL_ONLY_USER", "type": "bool", "massupdate": false, "default": "0", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "show_on_employees": {
                    "name": "show_on_employees", "vname": "LBL_SHOW_ON_EMPLOYEES", "type": "bool", "massupdate": true, "importable": true, "default": true, "studio": {
                        "formula": false
                    }
                },
                "employee_status": {
                    "name": "employee_status", "vname": "LBL_EMPLOYEE_STATUS", "type": "varchar", "function": {
                        "name": "getEmployeeStatusOptions", "returns": "html", "include": "modules\/Employees\/EmployeeStatus.php"
                    },
                    "len": 100
                },
                "messenger_id": {
                    "name": "messenger_id", "vname": "LBL_MESSENGER_ID", "type": "varchar", "len": 100
                },
                "messenger_type": {
                    "name": "messenger_type", "vname": "LBL_MESSENGER_TYPE", "type": "enum", "options": "messenger_type_dom", "len": 100, "massupdate": false
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "calls_users", "source": "non-db", "vname": "LBL_CALLS"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "meetings_users", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "contacts_sync": {
                    "name": "contacts_sync", "type": "link", "relationship": "contacts_users", "source": "non-db", "vname": "LBL_CONTACTS_SYNC", "reportable": false
                },
                "reports_to_id": {
                    "name": "reports_to_id", "vname": "LBL_REPORTS_TO_ID", "type": "id", "required": false
                },
                "reports_to_name": {
                    "name": "reports_to_name", "rname": "last_name", "id_name": "reports_to_id", "vname": "LBL_REPORTS_TO_NAME", "type": "relate", "isnull": "true", "module": "Users", "table": "users", "link": "reports_to_link", "reportable": false, "source": "non-db", "duplicate_merge": "disabled", "side": "right"
                },
                "reports_to_link": {
                    "name": "reports_to_link", "type": "link", "relationship": "user_direct_reports", "link_type": "one", "side": "right", "source": "non-db", "vname": "LBL_REPORTS_TO"
                },
                "reportees": {
                    "name": "reportees", "type": "link", "relationship": "user_direct_reports", "link_type": "many", "side": "left", "source": "non-db", "vname": "LBL_REPORTS_TO", "reportable": false
                },
                "email1": {
                    "name": "email1", "vname": "LBL_EMAIL", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "group": "email1", "merge_filter": "enabled", "required": false
                },
                "email_addresses": {
                    "name": "email_addresses", "type": "link", "relationship": "users_email_addresses", "module": "EmailAddress", "bean_name": "EmailAddress", "source": "non-db", "vname": "LBL_EMAIL_ADDRESSES", "reportable": false, "required": false
                },
                "email_addresses_primary": {
                    "name": "email_addresses_primary", "type": "link", "relationship": "users_email_addresses_primary", "source": "non-db", "vname": "LBL_EMAIL_ADDRESS_PRIMARY", "duplicate_merge": "disabled", "required": false
                },
                "email_link_type": {
                    "name": "email_link_type", "vname": "LBL_EMAIL_LINK_TYPE", "type": "enum", "options": "dom_email_link_type", "importable": false, "reportable": false, "source": "non-db", "studio": false, "massupdate": false
                },
                "aclroles": {
                    "name": "aclroles", "type": "link", "relationship": "acl_roles_users", "source": "non-db", "side": "right", "vname": "LBL_ROLES"
                },
                "is_group": {
                    "name": "is_group", "vname": "LBL_GROUP_USER", "type": "bool", "massupdate": false, "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "c_accept_status_fields": {
                    "name": "c_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "calls", "link_type": "relationship_info", "source": "non-db", "importable": "false", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "m_accept_status_fields": {
                    "name": "m_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "meetings", "link_type": "relationship_info", "source": "non-db", "importable": "false", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "accept_status_id": {
                    "name": "accept_status_id", "type": "varchar", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "importable": "false", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "accept_status_name": {
                    "name": "accept_status_name", "type": "enum", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "options": "dom_meeting_accept_status", "massupdate": false, "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "prospect_lists": {
                    "name": "prospect_lists", "type": "link", "relationship": "prospect_list_users", "module": "ProspectLists", "source": "non-db", "vname": "LBL_PROSPECT_LIST"
                },
                "emails_users": {
                    "name": "emails_users", "type": "link", "relationship": "emails_users_rel", "module": "Emails", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "holidays": {
                    "name": "holidays", "type": "link", "relationship": "users_holidays", "source": "non-db", "side": "right", "vname": "LBL_HOLIDAYS"
                },
                "eapm": {
                    "name": "eapm", "type": "link", "relationship": "eapm_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "source": "non-db"
                },
                "oauth_tokens": {
                    "name": "oauth_tokens", "type": "link", "relationship": "oauthtokens_assigned_user", "vname": "LBL_OAUTH_TOKENS", "link_type": "one", "module": "OAuthTokens", "bean_name": "OAuthToken", "source": "non-db", "side": "left"
                },
                "project_resource": {
                    "name": "project_resource", "type": "link", "relationship": "projects_users_resources", "source": "non-db", "vname": "LBL_PROJECTS"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "first_name", "displayParams": {"wireless_edit_only": true}},
                                {"name": "last_name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                {"name": "title", "customCode": "{if $EDIT_TITLE_DEPT}<input type=\"text\" name=\"{$fields.title.name}\" id=\"{$fields.title.name}\" size=\"30\" maxlength=\"50\" value=\"{$fields.title.value}\" title=\"\" tabindex=\"t\" >{else}{$fields.title.value}<input type=\"hidden\" name=\"{$fields.title.name}\" id=\"{$fields.title.name}\" value=\"{$fields.title.value}\">{\/if}"},
                                {"name": "department", "customCode": "{if $EDIT_TITLE_DEPT}<input type=\"text\" name=\"{$fields.department.name}\" id=\"{$fields.department.name}\" size=\"30\" maxlength=\"50\" value=\"{$fields.department.value}\" title=\"\" tabindex=\"t\" >{else}{$fields.department.value}<input type=\"hidden\" name=\"{$fields.department.name}\" id=\"{$fields.department.name}\" value=\"{$fields.department.value}\">{\/if}"},
                                "phone_work",
                                "phone_mobile",
                                "email1"
                            ]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "first_name", "displayParams": {"wireless_edit_only": true}},
                                {"name": "last_name", "displayParams": {"required": true, "wireless_edit_only": true}},
                                {"name": "title", "customCode": "{if $EDIT_TITLE_DEPT}<input type=\"text\" name=\"{$fields.title.name}\" id=\"{$fields.title.name}\" size=\"30\" maxlength=\"50\" value=\"{$fields.title.value}\" title=\"\" tabindex=\"t\" >{else}{$fields.title.value}<input type=\"hidden\" name=\"{$fields.title.name}\" id=\"{$fields.title.name}\" value=\"{$fields.title.value}\">{\/if}"},
                                {"name": "department", "customCode": "{if $EDIT_TITLE_DEPT}<input type=\"text\" name=\"{$fields.department.name}\" id=\"{$fields.department.name}\" size=\"30\" maxlength=\"50\" value=\"{$fields.department.value}\" title=\"\" tabindex=\"t\" >{else}{$fields.department.value}<input type=\"hidden\" name=\"{$fields.department.name}\" id=\"{$fields.department.name}\" value=\"{$fields.department.value}\">{\/if}"},
                                "phone_work",
                                "phone_mobile",
                                "email1"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "width": "20%", "label": "LBL_NAME", "link": true, "orderBy": "last_name", "default": true, "enabled": true, "related_fields": ["first_name", "last_name", "salutation"]},
                                {"name": "title", "width": "15%", "label": "LBL_TITLE", "default": true, "enabled": true},
                                {"name": "email1", "width": "15%", "label": "LBL_EMAIL", "sortable": false, "link": true, "customCode": "{$EMAIL1_LINK}{$EMAIL1}<\/a>", "default": true, "enabled": true},
                                {"name": "phone_work", "width": "15%", "label": "LBL_OFFICE_PHONE", "default": true, "enabled": true},
                                {"name": "phone_home", "width": "10", "label": "LBL_HOME_PHONE", "default": false},
                                {"name": "phone_mobile", "width": "10", "label": "LBL_MOBILE_PHONE", "default": false},
                                {"name": "phone_other", "width": "10", "label": "LBL_WORK_PHONE", "default": false},
                                {"name": "phone_fax", "width": "10", "label": "LBL_FAX_PHONE", "default": false},
                                {"name": "address_street", "width": "10", "label": "LBL_PRIMARY_ADDRESS_STREET", "default": false},
                                {"name": "address_city", "width": "10", "label": "LBL_PRIMARY_ADDRESS_CITY", "default": false},
                                {"name": "address_state", "width": "10", "label": "LBL_PRIMARY_ADDRESS_STATE", "default": false},
                                {"name": "address_postalcode", "width": "10", "label": "LBL_PRIMARY_ADDRESS_POSTALCODE", "default": false},
                                {"name": "date_entered", "width": "10", "label": "LBL_DATE_ENTERED", "default": false},
                                {"name": "team_name", "width": "10", "label": "LBL_TEAM", "default": true, "enabled": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["last_name", "first_name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "a73449e6c406d748a4e4fdf5f7928bb2"
        },
        "Teams": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true
                },
                "name": {
                    "name": "name", "vname": "LBL_PRIMARY_TEAM_NAME", "type": "name", "dbType": "varchar", "len": "128"
                },
                "name_2": {
                    "name": "name_2", "vname": "LBL_NAME_2", "type": "name", "dbType": "varchar", "len": "128", "reportable": false
                },
                "associated_user_id": {
                    "name": "associated_user_id", "type": "id", "reportable": false
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "reportable": true
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "reportable": true
                },
                "private": {
                    "name": "private", "vname": "LBL_PRIVATE", "type": "bool", "default": "0"
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "reportable": false, "required": false
                },
                "users": {
                    "name": "users", "type": "link", "relationship": "team_memberships", "source": "non-db", "vname": "LBL_USERS"
                },
                "teams_sets": {
                    "name": "teams_sets", "type": "link", "relationship": "team_sets_teams", "link_type": "many", "side": "right", "source": "non-db", "vname": "LBL_TEAMS", "studio": false, "duplicate_merge": "disabled"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "4e3bbe0c72bc903f4cf3606e9555af61"
        },
        "Users": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true
                },
                "user_name": {
                    "name": "user_name", "vname": "LBL_USER_NAME", "type": "user_name", "dbType": "varchar", "len": "60", "importable": "required", "required": true, "studio": {
                        "no_duplicate": true, "editview": false, "detailview": true, "quickcreate": false, "basic_search": false, "advanced_search": false, "wirelesseditview": false, "wirelessdetailview": true, "wirelesslistview": false, "wireless_basic_search": false, "wireless_advanced_search": false, "rollup": false
                    }
                },
                "user_hash": {
                    "name": "user_hash", "vname": "LBL_USER_HASH", "type": "varchar", "len": "255", "reportable": false, "importable": "false", "studio": {
                        "no_duplicate": true, "listview": false, "searchview": false, "related": false, "formula": false, "rollup": false
                    }
                },
                "system_generated_password": {
                    "name": "system_generated_password", "vname": "LBL_SYSTEM_GENERATED_PASSWORD", "type": "bool", "required": true, "reportable": false, "massupdate": false, "studio": {
                        "listview": false, "searchview": false, "editview": false, "quickcreate": false, "wirelesseditview": false, "related": false, "formula": false, "rollup": false
                    }
                },
                "pwd_last_changed": {
                    "name": "pwd_last_changed", "vname": "LBL_PSW_MODIFIED", "type": "datetime", "required": false, "massupdate": false, "studio": {
                        "formula": false
                    }
                },
                "authenticate_id": {
                    "name": "authenticate_id", "vname": "LBL_AUTHENTICATE_ID", "type": "varchar", "len": "100", "reportable": false, "importable": "false", "studio": {
                        "listview": false, "searchview": false, "related": false
                    }
                },
                "sugar_login": {
                    "name": "sugar_login", "vname": "LBL_SUGAR_LOGIN", "type": "bool", "default": "1", "reportable": false, "massupdate": false, "importable": false, "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "picture": {
                    "name": "picture", "vname": "LBL_PICTURE_FILE", "type": "image", "dbType": "varchar", "len": "255", "width": "", "height": "", "border": ""
                },
                "first_name": {
                    "name": "first_name", "vname": "LBL_FIRST_NAME", "dbType": "varchar", "type": "name", "len": "30"
                },
                "last_name": {
                    "name": "last_name", "vname": "LBL_LAST_NAME", "dbType": "varchar", "type": "name", "len": "30", "importable": "required", "required": true
                },
                "full_name": {
                    "name": "full_name", "rname": "full_name", "vname": "LBL_NAME", "type": "name", "fields": ["first_name", "last_name"], "source": "non-db", "sort_on": "last_name", "sort_on2": "first_name", "db_concat_fields": ["first_name", "last_name"], "len": "510", "studio": {
                        "formula": false
                    }
                },
                "name": {
                    "name": "name", "rname": "name", "vname": "LBL_NAME", "type": "varchar", "source": "non-db", "len": "510", "db_concat_fields": ["first_name", "last_name"], "importable": "false"
                },
                "is_admin": {
                    "name": "is_admin", "vname": "LBL_IS_ADMIN", "type": "bool", "default": "0", "studio": {
                        "listview": false, "searchview": false, "related": false
                    }
                },
                "external_auth_only": {
                    "name": "external_auth_only", "vname": "LBL_EXT_AUTHENTICATE", "type": "bool", "reportable": false, "massupdate": false, "default": "0", "studio": {
                        "listview": false, "searchview": false, "related": false
                    }
                },
                "receive_notifications": {
                    "name": "receive_notifications", "vname": "LBL_RECEIVE_NOTIFICATIONS", "type": "bool", "default": "1", "massupdate": false, "studio": false
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true, "studio": {
                        "editview": false, "quickcreate": false, "wirelesseditview": false
                    }
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true, "studio": {
                        "editview": false, "quickcreate": false, "wirelesseditview": false
                    }
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED_BY_ID", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id"
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_BY", "type": "varchar", "source": "non-db", "studio": false
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_ASSIGNED_TO", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "studio": false
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED_BY_NAME", "type": "varchar", "source": "non-db", "importable": "false", "studio": {
                        "related": false, "formula": false, "rollup": false
                    }
                },
                "title": {
                    "name": "title", "vname": "LBL_TITLE", "type": "varchar", "len": "50"
                },
                "department": {
                    "name": "department", "vname": "LBL_DEPARTMENT", "type": "varchar", "len": "50"
                },
                "phone_home": {
                    "name": "phone_home", "vname": "LBL_HOME_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_mobile": {
                    "name": "phone_mobile", "vname": "LBL_MOBILE_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_work": {
                    "name": "phone_work", "vname": "LBL_WORK_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_other": {
                    "name": "phone_other", "vname": "LBL_OTHER_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "phone_fax": {
                    "name": "phone_fax", "vname": "LBL_FAX_PHONE", "type": "phone", "dbType": "varchar", "len": "50"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "len": 100, "options": "user_status_dom", "importable": "required", "required": true
                },
                "address_street": {
                    "name": "address_street", "vname": "LBL_ADDRESS_STREET", "type": "varchar", "len": "150"
                },
                "address_city": {
                    "name": "address_city", "vname": "LBL_ADDRESS_CITY", "type": "varchar", "len": "100"
                },
                "address_state": {
                    "name": "address_state", "vname": "LBL_ADDRESS_STATE", "type": "varchar", "len": "100"
                },
                "address_country": {
                    "name": "address_country", "vname": "LBL_ADDRESS_COUNTRY", "type": "varchar", "len": 100
                },
                "address_postalcode": {
                    "name": "address_postalcode", "vname": "LBL_ADDRESS_POSTALCODE", "type": "varchar", "len": "20"
                },
                "UserType": {
                    "name": "UserType", "vname": "LBL_USER_TYPE", "type": "enum", "len": 50, "options": "user_type_dom", "source": "non-db", "import": false, "reportable": false, "studio": {
                        "formula": false
                    }
                },
                "default_team": {
                    "name": "default_team", "vname": "LBL_DEFAULT_TEAM", "reportable": false, "type": "varchar", "len": "36", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "team_id": {
                    "name": "team_id", "vname": "LBL_DEFAULT_TEAM", "reportable": false, "source": "non-db", "type": "varchar", "len": "36", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false"
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": true, "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset", "studio": {
                        "listview": false, "searchview": false, "editview": false, "quickcreate": false, "wirelesseditview": false
                    }
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "users_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "users_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "users_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "team_memberships": {
                    "name": "team_memberships", "type": "link", "relationship": "team_memberships", "source": "non-db", "vname": "LBL_TEAMS"
                },
                "users_signatures": {
                    "name": "users_signatures", "type": "link", "relationship": "users_users_signatures", "source": "non-db", "studio": "false", "reportable": false
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "reportable": false
                },
                "portal_only": {
                    "name": "portal_only", "vname": "LBL_PORTAL_ONLY_USER", "type": "bool", "massupdate": false, "default": "0", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "show_on_employees": {
                    "name": "show_on_employees", "vname": "LBL_SHOW_ON_EMPLOYEES", "type": "bool", "massupdate": true, "importable": true, "default": true, "studio": {
                        "formula": false
                    }
                },
                "employee_status": {
                    "name": "employee_status", "vname": "LBL_EMPLOYEE_STATUS", "type": "varchar", "function": {
                        "name": "getEmployeeStatusOptions", "returns": "html", "include": "modules\/Employees\/EmployeeStatus.php"
                    },
                    "len": 100
                },
                "messenger_id": {
                    "name": "messenger_id", "vname": "LBL_MESSENGER_ID", "type": "varchar", "len": 100
                },
                "messenger_type": {
                    "name": "messenger_type", "vname": "LBL_MESSENGER_TYPE", "type": "enum", "options": "messenger_type_dom", "len": 100
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "calls_users", "source": "non-db", "vname": "LBL_CALLS"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "meetings_users", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "contacts_sync": {
                    "name": "contacts_sync", "type": "link", "relationship": "contacts_users", "source": "non-db", "vname": "LBL_CONTACTS_SYNC", "reportable": false
                },
                "reports_to_id": {
                    "name": "reports_to_id", "vname": "LBL_REPORTS_TO_ID", "type": "id", "required": false
                },
                "reports_to_name": {
                    "name": "reports_to_name", "rname": "last_name", "id_name": "reports_to_id", "vname": "LBL_REPORTS_TO_NAME", "type": "relate", "isnull": "true", "module": "Users", "table": "users", "link": "reports_to_link", "reportable": false, "source": "non-db", "duplicate_merge": "disabled", "side": "right"
                },
                "reports_to_link": {
                    "name": "reports_to_link", "type": "link", "relationship": "user_direct_reports", "link_type": "one", "side": "right", "source": "non-db", "vname": "LBL_REPORTS_TO"
                },
                "reportees": {
                    "name": "reportees", "type": "link", "relationship": "user_direct_reports", "link_type": "many", "side": "left", "source": "non-db", "vname": "LBL_REPORTS_TO", "reportable": false
                },
                "email1": {
                    "name": "email1", "vname": "LBL_EMAIL", "type": "varchar", "function": {
                        "name": "getEmailAddressWidget", "returns": "html"
                    },
                    "source": "non-db", "group": "email1", "merge_filter": "enabled", "required": true
                },
                "email_addresses": {
                    "name": "email_addresses", "type": "link", "relationship": "users_email_addresses", "module": "EmailAddress", "bean_name": "EmailAddress", "source": "non-db", "vname": "LBL_EMAIL_ADDRESSES", "reportable": false, "required": true
                },
                "email_addresses_primary": {
                    "name": "email_addresses_primary", "type": "link", "relationship": "users_email_addresses_primary", "source": "non-db", "vname": "LBL_EMAIL_ADDRESS_PRIMARY", "duplicate_merge": "disabled", "required": true
                },
                "email_link_type": {
                    "name": "email_link_type", "vname": "LBL_EMAIL_LINK_TYPE", "type": "enum", "options": "dom_email_link_type", "importable": false, "reportable": false, "source": "non-db", "studio": false
                },
                "aclroles": {
                    "name": "aclroles", "type": "link", "relationship": "acl_roles_users", "source": "non-db", "side": "right", "vname": "LBL_ROLES"
                },
                "is_group": {
                    "name": "is_group", "vname": "LBL_GROUP_USER", "type": "bool", "massupdate": false, "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "c_accept_status_fields": {
                    "name": "c_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "calls", "link_type": "relationship_info", "source": "non-db", "importable": "false", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "m_accept_status_fields": {
                    "name": "m_accept_status_fields", "rname": "id", "relationship_fields": {
                        "id": "accept_status_id", "accept_status": "accept_status_name"
                    },
                    "vname": "LBL_LIST_ACCEPT_STATUS", "type": "relate", "link": "meetings", "link_type": "relationship_info", "source": "non-db", "importable": "false", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "accept_status_id": {
                    "name": "accept_status_id", "type": "varchar", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "importable": "false", "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "accept_status_name": {
                    "name": "accept_status_name", "type": "enum", "source": "non-db", "vname": "LBL_LIST_ACCEPT_STATUS", "options": "dom_meeting_accept_status", "massupdate": false, "studio": {
                        "listview": false, "searchview": false, "formula": false
                    }
                },
                "prospect_lists": {
                    "name": "prospect_lists", "type": "link", "relationship": "prospect_list_users", "module": "ProspectLists", "source": "non-db", "vname": "LBL_PROSPECT_LIST"
                },
                "emails_users": {
                    "name": "emails_users", "type": "link", "relationship": "emails_users_rel", "module": "Emails", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "holidays": {
                    "name": "holidays", "type": "link", "relationship": "users_holidays", "source": "non-db", "side": "right", "vname": "LBL_HOLIDAYS"
                },
                "eapm": {
                    "name": "eapm", "type": "link", "relationship": "eapm_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "source": "non-db"
                },
                "oauth_tokens": {
                    "name": "oauth_tokens", "type": "link", "relationship": "oauthtokens_assigned_user", "vname": "LBL_OAUTH_TOKENS", "link_type": "one", "module": "OAuthTokens", "bean_name": "OAuthToken", "source": "non-db", "side": "left"
                },
                "project_resource": {
                    "name": "project_resource", "type": "link", "relationship": "projects_users_resources", "source": "non-db", "vname": "LBL_PROJECTS"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "full_name", "label": "LBL_NAME"},
                                {"name": "phone_work"},
                                "email1",
                                "assigned_user_name",
                                "team_name"
                            ]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true, "related_fields": ["first_name", "last_name", "salutation"]},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "e723712f60bc83c27cbe25125be5e935"
        },
        "EmailAddresses": {
            "fields": {
                "id": {
                    "name": "id", "type": "id", "vname": "LBL_EMAIL_ADDRESS_ID", "required": true
                },
                "email_address": {
                    "name": "email_address", "type": "varchar", "vname": "LBL_EMAIL_ADDRESS", "length": 100, "required": true
                },
                "email_address_caps": {
                    "name": "email_address_caps", "type": "varchar", "vname": "LBL_EMAIL_ADDRESS_CAPS", "length": 100, "required": true, "reportable": false
                },
                "invalid_email": {
                    "name": "invalid_email", "type": "bool", "default": 0, "vname": "LBL_INVALID_EMAIL"
                },
                "opt_out": {
                    "name": "opt_out", "type": "bool", "default": 0, "vname": "LBL_OPT_OUT"
                },
                "date_created": {
                    "name": "date_created", "type": "datetime", "vname": "LBL_DATE_CREATE"
                },
                "date_modified": {
                    "name": "date_modified", "type": "datetime", "vname": "LBL_DATE_MODIFIED"
                },
                "deleted": {
                    "name": "deleted", "type": "bool", "default": 0, "vname": "LBL_DELETED"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "5dbfb95f0db818aebefd41530b60e439"
        },
        "CampaignLog": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "campaign_id": {
                    "name": "campaign_id", "vname": "LBL_CAMPAIGN_ID", "type": "id", "comment": "Campaign identifier"
                },
                "target_tracker_key": {
                    "name": "target_tracker_key", "vname": "LBL_TARGET_TRACKER_KEY", "type": "varchar", "len": "36", "comment": "Identifier of Tracker URL"
                },
                "target_id": {
                    "name": "target_id", "vname": "LBL_TARGET_ID", "type": "varchar", "len": "36", "comment": "Identifier of target record"
                },
                "target_type": {
                    "name": "target_type", "vname": "LBL_TARGET_TYPE", "type": "varchar", "len": 100, "comment": "Descriptor of the target record type (e.g., Contact, Lead)"
                },
                "activity_type": {
                    "name": "activity_type", "vname": "LBL_ACTIVITY_TYPE", "type": "enum", "options": "campainglog_activity_type_dom", "len": 100, "comment": "The activity that occurred (e.g., Viewed Message, Bounced, Opted out)"
                },
                "activity_date": {
                    "name": "activity_date", "vname": "LBL_ACTIVITY_DATE", "type": "datetime", "comment": "The date the activity occurred"
                },
                "related_id": {
                    "name": "related_id", "vname": "LBL_RELATED_ID", "type": "varchar", "len": "36"
                },
                "related_type": {
                    "name": "related_type", "vname": "LBL_RELATED_TYPE", "type": "varchar", "len": 100
                },
                "archived": {
                    "name": "archived", "vname": "LBL_ARCHIVED", "type": "bool", "reportable": false, "default": "0", "comment": "Indicates if item has been archived"
                },
                "hits": {
                    "name": "hits", "vname": "LBL_HITS", "type": "int", "default": "0", "reportable": true, "comment": "Number of times the item has been invoked (e.g., multiple click-thrus)"
                },
                "list_id": {
                    "name": "list_id", "vname": "LBL_LIST_ID", "type": "id", "reportable": false, "len": "36", "comment": "The target list from which item originated"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "reportable": false, "comment": "Record deletion indicator"
                },
                "recipient_name": {
                    "name": "recipient_name", "type": "varchar", "len": "255", "source": "non-db"
                },
                "recipient_email": {
                    "name": "recipient_email", "type": "varchar", "len": "255", "source": "non-db"
                },
                "marketing_name": {
                    "name": "marketing_name", "type": "varchar", "len": "255", "source": "non-db"
                },
                "campaign_name1": {
                    "name": "campaign_name1", "rname": "name", "id_name": "campaign_id", "vname": "LBL_CAMPAIGN_NAME", "type": "relate", "table": "campaigns", "isnull": "true", "module": "Campaigns", "dbType": "varchar", "link": "campaign", "len": "255", "source": "non-db"
                },
                "campaign_name": {
                    "name": "campaign_name", "type": "varchar", "len": "255", "source": "non-db"
                },
                "campaign_objective": {
                    "name": "campaign_objective", "type": "varchar", "len": "255", "source": "non-db"
                },
                "campaign_content": {
                    "name": "campaign_content", "type": "varchar", "len": "255", "source": "non-db"
                },
                "campaign": {
                    "name": "campaign", "type": "link", "relationship": "campaign_campaignlog", "source": "non-db", "vname": "LBL_CAMPAIGNS"
                },
                "related_name": {
                    "source": "function", "function_name": "get_related_name", "function_class": "CampaignLog", "function_params": ["related_id", "related_type"], "function_params_source": "this", "type": "function", "name": "related_name", "reportable": false
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime"
                },
                "more_information": {
                    "name": "more_information", "vname": "LBL_MORE_INFO", "type": "varchar", "len": "100"
                },
                "marketing_id": {
                    "name": "marketing_id", "vname": "LBL_MARKETING_ID", "type": "id", "reportable": false, "comment": "ID of marketing email this entry is associated with"
                },
                "created_contact": {
                    "name": "created_contact", "vname": "LBL_CREATED_CONTACT", "type": "link", "relationship": "campaignlog_contact", "source": "non-db"
                },
                "created_lead": {
                    "name": "created_lead", "vname": "LBL_CREATED_LEAD", "type": "link", "relationship": "campaignlog_lead", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "90b264ee112f57caa743978705ffb30f"
        },
        "ACLRoles": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "required": true, "type": "id", "reportable": false, "comment": "Unique identifier"
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "required": true, "comment": "Date record created"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "required": true, "comment": "Date record last modified"
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED", "type": "assigned_user_name", "table": "modified_user_id_users", "isnull": "false", "dbType": "id", "required": false, "len": 36, "reportable": true, "comment": "User who last modified record"
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "created_by", "vname": "LBL_CREATED", "type": "assigned_user_name", "table": "created_by_users", "isnull": "false", "dbType": "id", "len": 36, "comment": "User who created record"
                },
                "name": {
                    "name": "name", "type": "varchar", "vname": "LBL_NAME", "len": 150, "comment": "The role name"
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "type": "text", "comment": "The role description"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "reportable": false, "comment": "Record deletion indicator"
                },
                "users": {
                    "name": "users", "type": "link", "relationship": "acl_roles_users", "source": "non-db", "vname": "LBL_USERS"
                },
                "actions": {
                    "name": "actions", "type": "link", "relationship": "acl_roles_actions", "source": "non-db", "vname": "LBL_USERS"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "2b37dd9ca2522bf15f779a026baea8de"
        },
        "ProjectTask": {
            "fields": {
                "team_id": {
                    "name": "team_id", "vname": "LBL_TEAM_ID", "group": "team_name", "reportable": false, "dbType": "id", "type": "team_list", "audited": true, "comment": "Team ID for the account"
                },
                "team_set_id": {
                    "name": "team_set_id", "rname": "id", "id_name": "team_set_id", "vname": "LBL_TEAM_SET_ID", "type": "id", "audited": true, "studio": "false", "dbType": "id"
                },
                "team_count": {
                    "name": "team_count", "rname": "team_count", "id_name": "team_id", "vname": "LBL_TEAMS", "join_name": "ts1", "table": "teams", "type": "relate", "required": "true", "isnull": "true", "module": "Teams", "link": "team_count_link", "massupdate": false, "dbType": "int", "source": "non-db", "importable": "false", "reportable": false, "duplicate_merge": "disabled", "studio": "false", "hideacl": true
                },
                "team_name": {
                    "name": "team_name", "db_concat_fields": ["name", "name_2"], "sort_on": "tj.name", "join_name": "tj", "rname": "name", "id_name": "team_id", "vname": "LBL_TEAMS", "type": "relate", "required": "true", "table": "teams", "isnull": "true", "module": "Teams", "link": "team_link", "massupdate": false, "dbType": "varchar", "source": "non-db", "len": 36, "custom_type": "teamset"
                },
                "team_link": {
                    "name": "team_link", "type": "link", "relationship": "projecttask_team", "vname": "LBL_TEAMS_LINK", "link_type": "one", "module": "Teams", "bean_name": "Team", "source": "non-db", "duplicate_merge": "disabled", "studio": "false"
                },
                "team_count_link": {
                    "name": "team_count_link", "type": "link", "relationship": "projecttask_team_count_relationship", "link_type": "one", "module": "Teams", "bean_name": "TeamSet", "source": "non-db", "duplicate_merge": "disabled", "reportable": false, "studio": "false"
                },
                "teams": {
                    "name": "teams", "type": "link", "relationship": "projecttask_teams", "bean_filter_field": "team_set_id", "rhs_key_override": true, "source": "non-db", "vname": "LBL_TEAMS", "link_class": "TeamSetLink", "link_file": "modules\/Teams\/TeamSetLink.php", "studio": "false", "reportable": false
                },
                "id": {
                    "name": "id", "vname": "LBL_ID", "required": true, "type": "id", "reportable": true
                },
                "date_entered": {
                    "name": "date_entered", "vname": "LBL_DATE_ENTERED", "type": "datetime", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "date_modified": {
                    "name": "date_modified", "vname": "LBL_DATE_MODIFIED", "type": "datetime", "enable_range_search": true, "options": "date_range_search_dom"
                },
                "project_id": {
                    "name": "project_id", "vname": "LBL_PROJECT_ID", "required": true, "type": "id", "reportable": false, "importable": "required"
                },
                "project_task_id": {
                    "name": "project_task_id", "vname": "LBL_PROJECT_TASK_ID", "required": false, "type": "int", "reportable": false
                },
                "name": {
                    "name": "name", "vname": "LBL_NAME", "required": true, "dbType": "varchar", "type": "name", "len": 50, "unified_search": true, "full_text_search": {
                        "boost": 3
                    },
                    "importable": "required"
                },
                "status": {
                    "name": "status", "vname": "LBL_STATUS", "type": "enum", "required": false, "options": "project_task_status_options", "audited": true
                },
                "description": {
                    "name": "description", "vname": "LBL_DESCRIPTION", "required": false, "type": "text"
                },
                "resource_id": {
                    "name": "resource_id", "vname": "LBL_RESOURCE_ID", "required": false, "type": "text", "hidden": true
                },
                "resource_name": {
                    "name": "resource_name", "vname": "LBL_RESOURCE", "required": false, "type": "text", "source": "non-db"
                },
                "predecessors": {
                    "name": "predecessors", "vname": "LBL_PREDECESSORS", "required": false, "type": "text"
                },
                "date_start": {
                    "name": "date_start", "vname": "LBL_DATE_START", "type": "date", "validation": {
                        "type": "isbefore", "compareto": "date_finish", "blank": true
                    },
                    "audited": true, "enable_range_search": true
                },
                "time_start": {
                    "name": "time_start", "vname": "LBL_TIME_START", "type": "int", "reportable": false
                },
                "time_finish": {
                    "name": "time_finish", "vname": "LBL_TIME_FINISH", "type": "int", "reportable": false
                },
                "date_finish": {
                    "name": "date_finish", "vname": "LBL_DATE_FINISH", "type": "date", "validation": {
                        "type": "isafter", "compareto": "date_start", "blank": true
                    },
                    "audited": true, "enable_range_search": true
                },
                "duration": {
                    "name": "duration", "vname": "LBL_DURATION", "required": true, "type": "int"
                },
                "duration_unit": {
                    "name": "duration_unit", "vname": "LBL_DURATION_UNIT", "options": "project_duration_units_dom", "type": "text"
                },
                "actual_duration": {
                    "name": "actual_duration", "vname": "LBL_ACTUAL_DURATION", "required": false, "type": "int"
                },
                "percent_complete": {
                    "name": "percent_complete", "vname": "LBL_PERCENT_COMPLETE", "type": "int", "required": false, "audited": true
                },
                "date_due": {
                    "name": "date_due", "vname": "LBL_DATE_DUE", "type": "date", "rel_field": "time_due", "audited": true
                },
                "time_due": {
                    "name": "time_due", "vname": "LBL_TIME_DUE", "type": "time", "rel_field": "date_due", "audited": true
                },
                "parent_task_id": {
                    "name": "parent_task_id", "vname": "LBL_PARENT_TASK_ID", "required": false, "type": "int", "reportable": true
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "type": "assigned_user_name", "vname": "LBL_ASSIGNED_USER_ID", "required": false, "dbType": "id", "table": "users", "isnull": false, "reportable": true, "audited": true
                },
                "modified_user_id": {
                    "name": "modified_user_id", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_MODIFIED_USER_ID", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "reportable": true
                },
                "modified_by_name": {
                    "name": "modified_by_name", "vname": "LBL_MODIFIED_NAME", "type": "relate", "reportable": false, "source": "non-db", "rname": "user_name", "table": "users", "id_name": "modified_user_id", "module": "Users", "link": "modified_user_link", "duplicate_merge": "disabled"
                },
                "priority": {
                    "name": "priority", "vname": "LBL_PRIORITY", "type": "enum", "options": "project_task_priority_options"
                },
                "created_by": {
                    "name": "created_by", "rname": "user_name", "id_name": "modified_user_id", "vname": "LBL_CREATED_BY", "type": "assigned_user_name", "table": "users", "isnull": "false", "dbType": "id", "reportable": true
                },
                "created_by_name": {
                    "name": "created_by_name", "vname": "LBL_CREATED", "type": "relate", "reportable": false, "link": "created_by_link", "rname": "user_name", "source": "non-db", "table": "users", "id_name": "created_by", "module": "Users", "duplicate_merge": "disabled", "importable": "false"
                },
                "milestone_flag": {
                    "name": "milestone_flag", "vname": "LBL_MILESTONE_FLAG", "type": "bool", "required": false
                },
                "order_number": {
                    "name": "order_number", "vname": "LBL_ORDER_NUMBER", "required": false, "type": "int", "default": "1"
                },
                "task_number": {
                    "name": "task_number", "vname": "LBL_TASK_NUMBER", "required": false, "type": "int"
                },
                "estimated_effort": {
                    "name": "estimated_effort", "vname": "LBL_ESTIMATED_EFFORT", "required": false, "type": "int"
                },
                "actual_effort": {
                    "name": "actual_effort", "vname": "LBL_ACTUAL_EFFORT", "required": false, "type": "int"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "required": false, "default": "0", "reportable": false
                },
                "utilization": {
                    "name": "utilization", "vname": "LBL_UTILIZATION", "required": false, "type": "int", "validation": {
                        "type": "range", "min": 0, "max": 100
                    },
                    "function": {
                        "name": "getUtilizationDropdown", "returns": "html", "include": "modules\/ProjectTask\/ProjectTask.php"
                    },
                    "default": 100
                },
                "project_name": {
                    "name": "project_name", "rname": "name", "id_name": "project_id", "vname": "LBL_PARENT_NAME", "type": "relate", "join_name": "project", "table": "project", "isnull": "true", "module": "Project", "link": "project_name_link", "massupdate": false, "source": "non-db"
                },
                "notes": {
                    "name": "notes", "type": "link", "relationship": "project_tasks_notes", "source": "non-db", "vname": "LBL_NOTES"
                },
                "tasks": {
                    "name": "tasks", "type": "link", "relationship": "project_tasks_tasks", "source": "non-db", "vname": "LBL_TASKS"
                },
                "meetings": {
                    "name": "meetings", "type": "link", "relationship": "project_tasks_meetings", "source": "non-db", "vname": "LBL_MEETINGS"
                },
                "calls": {
                    "name": "calls", "type": "link", "relationship": "project_tasks_calls", "source": "non-db", "vname": "LBL_CALLS"
                },
                "emails": {
                    "name": "emails", "type": "link", "relationship": "emails_project_task_rel", "source": "non-db", "vname": "LBL_EMAILS"
                },
                "projects": {
                    "name": "projects", "type": "link", "relationship": "projects_project_tasks", "source": "non-db", "vname": "LBL_LIST_PARENT_NAME"
                },
                "created_by_link": {
                    "name": "created_by_link", "type": "link", "relationship": "project_tasks_created_by", "vname": "LBL_CREATED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "modified_user_link": {
                    "name": "modified_user_link", "type": "link", "relationship": "project_tasks_modified_user", "vname": "LBL_MODIFIED_BY_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "project_name_link": {
                    "name": "project_name_link", "type": "link", "relationship": "projects_project_tasks", "vname": "LBL_PROJECT_NAME", "link_type": "one", "module": "Project", "bean_name": "Project", "source": "non-db"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "project_tasks_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_USER_NAME", "type": "relate", "table": "users", "module": "Users", "dbType": "varchar", "link": "users", "len": "255", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "bea21ca3c9f16146eaa5d49b9c712126"
        },
        "OAuthTokens": {
            "fields": {
                "id": {
                    "name": "id", "vname": "LBL_ID", "type": "id", "required": true, "reportable": true, "comment": "Unique identifier"
                },
                "secret": {
                    "name": "secret", "type": "varchar", "len": 32, "required": true, "comment": "Secret key"
                },
                "tstate": {
                    "name": "tstate", "type": "enum", "len": 1, "options": "token_status", "required": true, "comment": "Token state"
                },
                "consumer": {
                    "name": "consumer", "type": "id", "required": true, "comment": "Token related to the consumer"
                },
                "token_ts": {
                    "name": "token_ts", "type": "long", "required": true, "comment": "Token timestamp", "function": {
                        "name": "displayDateFromTs", "returns": "html", "onListView": true
                    }
                },
                "expire_ts": {
                    "name": "expire_ts", "type": "long", "required": true, "default": -1, "comment": "Token expiration, defaults to -1 for no expiration date", "function": {
                        "name": "displayDateFromTs", "returns": "html", "onListView": true
                    }
                },
                "verify": {
                    "name": "verify", "type": "varchar", "len": 32, "comment": "Token verification info"
                },
                "deleted": {
                    "name": "deleted", "vname": "LBL_DELETED", "type": "bool", "default": "0", "reportable": false, "required": true, "isnull": false, "comment": "Record deletion indicator"
                },
                "callback_url": {
                    "name": "callback_url", "type": "url", "len": 255, "required": false, "comment": "Callback URL for Authorization"
                },
                "consumer_link": {
                    "name": "consumer_link", "type": "link", "relationship": "consumer_tokens", "vname": "LBL_CONSUMER", "link_type": "one", "module": "OAuthKeys", "bean_name": "OAuthKey", "source": "non-db"
                },
                "consumer_name": {
                    "name": "consumer_name", "link": "consumer_link", "vname": "LBL_CONSUMER", "rname": "name", "type": "relate", "reportable": false, "source": "non-db", "table": "oauth_consumer", "id_name": "consumer", "module": "OAuthKeys", "duplicate_merge": "disabled"
                },
                "contact_id": {
                    "name": "contact_id", "vname": "LBL_CONTACTS", "type": "id", "required": false, "reportable": false, "comment": "Contact ID this oauth token is associated with (via portal)"
                },
                "contact_name": {
                    "name": "contact_name", "rname": "name", "id_name": "contact_id", "vname": "LBL_CONTACTS", "table": "contacts", "type": "relate", "link": "contact", "join_name": "contacts", "db_concat_fields": ["first_name", "last_name"], "isnull": "true", "module": "Contacts", "source": "non-db"
                },
                "assigned_user_id": {
                    "name": "assigned_user_id", "rname": "user_name", "id_name": "assigned_user_id", "vname": "LBL_ASSIGNED_TO_ID", "group": "assigned_user_name", "type": "relate", "table": "users", "module": "Users", "reportable": true, "isnull": "false", "dbType": "id", "audited": true, "comment": "User ID assigned to record", "duplicate_merge": "disabled"
                },
                "assigned_user_name": {
                    "name": "assigned_user_name", "link": "assigned_user_link", "vname": "LBL_ASSIGNED_TO_NAME", "rname": "user_name", "type": "relate", "reportable": false, "source": "non-db", "table": "users", "id_name": "assigned_user_id", "module": "Users", "duplicate_merge": "disabled"
                },
                "assigned_user_link": {
                    "name": "assigned_user_link", "type": "link", "relationship": "oauthtokens_assigned_user", "vname": "LBL_ASSIGNED_TO_USER", "link_type": "one", "module": "Users", "bean_name": "User", "source": "non-db", "duplicate_merge": "enabled", "rname": "user_name", "id_name": "assigned_user_id", "table": "users"
                },
                "contact": {
                    "name": "contact", "type": "link", "relationship": "contact_oauthtokens", "vname": "LBL_CONTACTS", "source": "non-db"
                }
            },
            "views": {
                "detail": {
                    "meta": {
                        "templateMeta": {
                            "form": {
                                "buttons": ["EDIT", "DUPLICATE", "DELETE"]
                            },
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": [
                                {"label": "10", "field": "30"},
                                {"label": "10", "field": "30"}
                            ]
                        },
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": ["name", "assigned_user_name", "team_name"]}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "panels": [
                            {"label": "LBL_PANEL_1", "fields": [
                                {"name": "name", "label": "LBL_NAME", "default": true, "enabled": true, "link": true},
                                {"name": "team_name", "label": "LBL_TEAM", "width": 9, "default": true, "enabled": true},
                                {"name": "assigned_user_name", "label": "LBL_ASSIGNED_TO_NAME", "width": 9, "default": true, "enabled": true, "link": true}
                            ]}
                        ]
                    }
                },
                "search": {
                    "meta": {
                        "templateMeta": {
                            "maxColumns": "1", "widths": {
                                "label": "10", "field": "30"
                            }
                        },
                        "layout": {
                            "basic_search": ["name"], "advanced_search": ["name"]
                        }
                    }
                }
            },
            "layouts": {
                "detail": {
                    "meta": {
                        "type": "detail", "components": [
                            {"view": "detail"}
                        ]
                    }
                },
                "edit": {
                    "meta": {
                        "type": "edit", "components": [
                            {"view": "edit"}
                        ]
                    }
                },
                "list": {
                    "meta": {
                        "type": "list", "components": [
                            {"view": "list"}
                        ]
                    }
                }
            },
            "_hash": "2f6b67cbba6ea233c9032553692b35f7"
        }
    },
    "relationships": {
        "accounts_bugs": {
            "name": "accounts_bugs", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "accounts_bugs", "join_key_lhs": "account_id", "join_key_rhs": "bug_id"
        },
        "accounts_contacts": {
            "name": "accounts_contacts", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "accounts_contacts", "join_key_lhs": "account_id", "join_key_rhs": "contact_id"
        },
        "accounts_opportunities": {
            "name": "accounts_opportunities", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "accounts_opportunities", "join_key_lhs": "account_id", "join_key_rhs": "opportunity_id"
        },
        "calls_contacts": {
            "name": "calls_contacts", "lhs_module": "Calls", "lhs_table": "calls", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "calls_contacts", "join_key_lhs": "call_id", "join_key_rhs": "contact_id"
        },
        "calls_users": {
            "name": "calls_users", "lhs_module": "Calls", "lhs_table": "calls", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "calls_users", "join_key_lhs": "call_id", "join_key_rhs": "user_id"
        },
        "calls_leads": {
            "name": "calls_leads", "lhs_module": "Calls", "lhs_table": "calls", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "calls_leads", "join_key_lhs": "call_id", "join_key_rhs": "lead_id"
        },
        "cases_bugs": {
            "name": "cases_bugs", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "cases_bugs", "join_key_lhs": "case_id", "join_key_rhs": "bug_id"
        },
        "contacts_bugs": {
            "name": "contacts_bugs", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contacts_bugs", "join_key_lhs": "contact_id", "join_key_rhs": "bug_id"
        },
        "contacts_cases": {
            "name": "contacts_cases", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contacts_cases", "join_key_lhs": "contact_id", "join_key_rhs": "case_id"
        },
        "contacts_users": {
            "name": "contacts_users", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contacts_users", "join_key_lhs": "contact_id", "join_key_rhs": "user_id"
        },
        "emails_accounts_rel": {
            "name": "emails_accounts_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Accounts"
        },
        "emails_bugs_rel": {
            "name": "emails_bugs_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Bugs"
        },
        "emails_cases_rel": {
            "name": "emails_cases_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Cases"
        },
        "emails_contacts_rel": {
            "name": "emails_contacts_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "relationship_role_column": "bean_module", "relationship_role_column_value": "Contacts", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id"
        },
        "emails_leads_rel": {
            "name": "emails_leads_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Leads"
        },
        "emails_opportunities_rel": {
            "name": "emails_opportunities_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Opportunities"
        },
        "emails_tasks_rel": {
            "name": "emails_tasks_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Tasks"
        },
        "emails_users_rel": {
            "name": "emails_users_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Users"
        },
        "emails_project_task_rel": {
            "name": "emails_project_task_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "ProjectTask"
        },
        "emails_projects_rel": {
            "name": "emails_projects_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Project", "rhs_table": "project", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Project"
        },
        "emails_prospects_rel": {
            "name": "emails_prospects_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Prospects"
        },
        "emails_quotes": {
            "name": "emails_quotes", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "emails_beans", "join_key_lhs": "email_id", "join_key_rhs": "bean_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Quotes"
        },
        "meetings_contacts": {
            "name": "meetings_contacts", "lhs_module": "Meetings", "lhs_table": "meetings", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "meetings_contacts", "join_key_lhs": "meeting_id", "join_key_rhs": "contact_id"
        },
        "meetings_users": {
            "name": "meetings_users", "lhs_module": "Meetings", "lhs_table": "meetings", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "meetings_users", "join_key_lhs": "meeting_id", "join_key_rhs": "user_id"
        },
        "meetings_leads": {
            "name": "meetings_leads", "lhs_module": "Meetings", "lhs_table": "meetings", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "meetings_leads", "join_key_lhs": "meeting_id", "join_key_rhs": "lead_id"
        },
        "opportunities_contacts": {
            "name": "opportunities_contacts", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "opportunities_contacts", "join_key_lhs": "opportunity_id", "join_key_rhs": "contact_id"
        },
        "team_sets_teams": {
            "name": "team_sets_teams", "lhs_module": "TeamSets", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "tracker_user_id": {
            "name": "tracker_user_id", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "TrackerSessions", "rhs_table": "tracker", "rhs_key": "user_id", "relationship_type": "one-to-many"
        },
        "tracker_tracker_queries": {
            "name": "tracker_tracker_queries", "lhs_module": "Trackers", "lhs_table": "tracker", "lhs_key": "monitor_id", "rhs_module": "TrackerQueries", "rhs_table": "tracker_queries", "rhs_key": "query_id", "relationship_type": "many-to-many", "join_table": "tracker_tracker_queries", "join_key_lhs": "monitor_id", "join_key_rhs": "query_id"
        },
        "prospect_list_campaigns": {
            "name": "prospect_list_campaigns", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "prospect_list_campaigns", "join_key_lhs": "prospect_list_id", "join_key_rhs": "campaign_id"
        },
        "prospect_list_contacts": {
            "name": "prospect_list_contacts", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "prospect_lists_prospects", "join_key_lhs": "prospect_list_id", "join_key_rhs": "related_id", "relationship_role_column": "related_type", "relationship_role_column_value": "Contacts"
        },
        "prospect_list_prospects": {
            "name": "prospect_list_prospects", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "prospect_lists_prospects", "join_key_lhs": "prospect_list_id", "join_key_rhs": "related_id", "relationship_role_column": "related_type", "relationship_role_column_value": "Prospects"
        },
        "prospect_list_leads": {
            "name": "prospect_list_leads", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "prospect_lists_prospects", "join_key_lhs": "prospect_list_id", "join_key_rhs": "related_id", "relationship_role_column": "related_type", "relationship_role_column_value": "Leads"
        },
        "prospect_list_users": {
            "name": "prospect_list_users", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "prospect_lists_prospects", "join_key_lhs": "prospect_list_id", "join_key_rhs": "related_id", "relationship_role_column": "related_type", "relationship_role_column_value": "Users"
        },
        "prospect_list_accounts": {
            "name": "prospect_list_accounts", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "prospect_lists_prospects", "join_key_lhs": "prospect_list_id", "join_key_rhs": "related_id", "relationship_role_column": "related_type", "relationship_role_column_value": "Accounts"
        },
        "roles_users": {
            "name": "roles_users", "lhs_module": "Roles", "lhs_table": "roles", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "roles_users", "join_key_lhs": "role_id", "join_key_rhs": "user_id"
        },
        "projects_bugs": {
            "name": "projects_bugs", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_bugs", "join_key_lhs": "project_id", "join_key_rhs": "bug_id"
        },
        "projects_cases": {
            "name": "projects_cases", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_cases", "join_key_lhs": "project_id", "join_key_rhs": "case_id"
        },
        "projects_products": {
            "name": "projects_products", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_products", "join_key_lhs": "project_id", "join_key_rhs": "product_id"
        },
        "projects_accounts": {
            "name": "projects_accounts", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_accounts", "join_key_lhs": "project_id", "join_key_rhs": "account_id"
        },
        "projects_contacts": {
            "name": "projects_contacts", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_contacts", "join_key_lhs": "project_id", "join_key_rhs": "contact_id"
        },
        "projects_opportunities": {
            "name": "projects_opportunities", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_opportunities", "join_key_lhs": "project_id", "join_key_rhs": "opportunity_id"
        },
        "product_bundle_note": {
            "name": "product_bundle_note", "lhs_module": "ProductBundles", "lhs_table": "product_bundles", "lhs_key": "id", "rhs_module": "ProductBundleNotes", "rhs_table": "product_bundle_note", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "product_bundle_note", "join_key_lhs": "bundle_id", "join_key_rhs": "note_id"
        },
        "product_bundle_product": {
            "name": "product_bundle_product", "lhs_module": "ProductBundles", "lhs_table": "product_bundles", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "product_bundle_product", "join_key_lhs": "bundle_id", "join_key_rhs": "product_id"
        },
        "product_bundle_quote": {
            "name": "product_bundle_quote", "lhs_module": "ProductBundles", "lhs_table": "product_bundles", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "product_bundle_quote", "join_key_lhs": "bundle_id", "join_key_rhs": "quote_id"
        },
        "product_product": {
            "name": "product_product", "lhs_module": "Products", "lhs_table": "products", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "product_product", "join_key_lhs": "parent_id", "join_key_rhs": "child_id", "reverse": "1"
        },
        "quotes_billto_accounts": {
            "name": "quotes_billto_accounts", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "relationship_type": "many-to-many", "true_relationship_type": "one-to-many", "join_table": "quotes_accounts", "join_key_rhs": "quote_id", "join_key_lhs": "account_id", "relationship_role_column": "account_role", "relationship_role_column_value": "Bill To"
        },
        "quotes_shipto_accounts": {
            "name": "quotes_shipto_accounts", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "relationship_type": "many-to-many", "true_relationship_type": "one-to-many", "join_table": "quotes_accounts", "join_key_rhs": "quote_id", "join_key_lhs": "account_id", "relationship_role_column": "account_role", "relationship_role_column_value": "Ship To"
        },
        "quotes_contacts_shipto": {
            "name": "quotes_contacts_shipto", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "relationship_type": "many-to-many", "true_relationship_type": "one-to-many", "join_table": "quotes_contacts", "join_key_rhs": "quote_id", "join_key_lhs": "contact_id", "relationship_role_column": "contact_role", "relationship_role_column_value": "Ship To"
        },
        "quotes_contacts_billto": {
            "name": "quotes_contacts_billto", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "relationship_type": "many-to-many", "true_relationship_type": "one-to-many", "join_table": "quotes_contacts", "join_key_rhs": "quote_id", "join_key_lhs": "contact_id", "relationship_role_column": "contact_role", "relationship_role_column_value": "Bill To"
        },
        "quotes_opportunities": {
            "name": "quotes_opportunities", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "quotes_opportunities", "join_key_lhs": "quote_id", "join_key_rhs": "opportunity_id"
        },
        "contracts_opportunities": {
            "name": "contracts_opportunities", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contracts_opportunities", "join_key_lhs": "contract_id", "join_key_rhs": "opportunity_id"
        },
        "contracts_contacts": {
            "name": "contracts_contacts", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contracts_contacts", "join_key_lhs": "contract_id", "join_key_rhs": "contact_id"
        },
        "contracts_quotes": {
            "name": "contracts_quotes", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contracts_quotes", "join_key_lhs": "contract_id", "join_key_rhs": "quote_id"
        },
        "contracts_products": {
            "name": "contracts_products", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "contracts_products", "join_key_lhs": "contract_id", "join_key_rhs": "product_id"
        },
        "projects_quotes": {
            "name": "projects_quotes", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "projects_quotes", "join_key_lhs": "project_id", "join_key_rhs": "quote_id"
        },
        "users_holidays": {
            "name": "users_holidays", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Holidays", "rhs_table": "holidays", "rhs_key": "person_id", "relationship_type": "one-to-many", "relationship_role_column": "related_module", "relationship_role_column_value": null
        },
        "acl_roles_actions": {
            "name": "acl_roles_actions", "lhs_module": "ACLRoles", "lhs_table": "acl_roles", "lhs_key": "id", "rhs_module": "ACLActions", "rhs_table": "acl_actions", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "acl_roles_actions", "join_key_lhs": "role_id", "join_key_rhs": "action_id"
        },
        "acl_roles_users": {
            "name": "acl_roles_users", "lhs_module": "ACLRoles", "lhs_table": "acl_roles", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "acl_roles_users", "join_key_lhs": "role_id", "join_key_rhs": "user_id"
        },
        "email_marketing_prospect_lists": {
            "name": "email_marketing_prospect_lists", "lhs_module": "EmailMarketing", "lhs_table": "email_marketing", "lhs_key": "id", "rhs_module": "ProspectLists", "rhs_table": "prospect_lists", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_marketing_prospect_lists", "join_key_lhs": "email_marketing_id", "join_key_rhs": "prospect_list_id"
        },
        "contracts_documents": {
            "name": "contracts_documents", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "linked_documents", "join_key_lhs": "parent_id", "join_key_rhs": "document_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "Contracts"
        },
        "leads_documents": {
            "name": "leads_documents", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "linked_documents", "join_key_lhs": "parent_id", "join_key_rhs": "document_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "Leads"
        },
        "contracttype_documents": {
            "name": "contracttype_documents", "lhs_module": "ContractTypes", "lhs_table": "contract_types", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "linked_documents", "join_key_lhs": "parent_id", "join_key_rhs": "document_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "ContracTemplates"
        },
        "documents_accounts": {
            "name": "documents_accounts", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_accounts", "join_key_lhs": "document_id", "join_key_rhs": "account_id"
        },
        "documents_contacts": {
            "name": "documents_contacts", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_contacts", "join_key_lhs": "document_id", "join_key_rhs": "contact_id"
        },
        "documents_opportunities": {
            "name": "documents_opportunities", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_opportunities", "join_key_lhs": "document_id", "join_key_rhs": "opportunity_id"
        },
        "documents_cases": {
            "name": "documents_cases", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_cases", "join_key_lhs": "document_id", "join_key_rhs": "case_id"
        },
        "documents_bugs": {
            "name": "documents_bugs", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_bugs", "join_key_lhs": "document_id", "join_key_rhs": "bug_id"
        },
        "documents_products": {
            "name": "documents_products", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_products", "join_key_lhs": "document_id", "join_key_rhs": "product_id"
        },
        "documents_quotes": {
            "name": "documents_quotes", "true_relationship_type": "many-to-many", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "documents_quotes", "join_key_lhs": "document_id", "join_key_rhs": "quote_id"
        },
        "user_direct_reports": {
            "name": "user_direct_reports", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "reports_to_id", "relationship_type": "one-to-many"
        },
        "users_users_signatures": {
            "name": "users_users_signatures", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "UserSignature", "rhs_table": "users_signatures", "rhs_key": "user_id", "relationship_type": "one-to-many"
        },
        "users_email_addresses": {
            "name": "users_email_addresses", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Users"
        },
        "users_email_addresses_primary": {
            "name": "users_email_addresses_primary", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "primary_address", "relationship_role_column_value": "1"
        },
        "users_team_count_relationship": {
            "name": "users_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "users_teams": {
            "name": "users_teams", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "users_team": {
            "name": "users_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "default_team", "relationship_type": "one-to-many"
        },
        "accounts_modified_user": {
            "name": "accounts_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "accounts_created_by": {
            "name": "accounts_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "accounts_assigned_user": {
            "name": "accounts_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "accounts_team_count_relationship": {
            "name": "accounts_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "accounts_teams": {
            "name": "accounts_teams", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "accounts_team": {
            "name": "accounts_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "accounts_email_addresses": {
            "name": "accounts_email_addresses", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Accounts"
        },
        "accounts_email_addresses_primary": {
            "name": "accounts_email_addresses_primary", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "primary_address", "relationship_role_column_value": "1"
        },
        "member_accounts": {
            "name": "member_accounts", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "account_cases": {
            "name": "account_cases", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "account_id", "relationship_type": "one-to-many"
        },
        "account_tasks": {
            "name": "account_tasks", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Accounts"
        },
        "account_notes": {
            "name": "account_notes", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Accounts"
        },
        "account_meetings": {
            "name": "account_meetings", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Accounts"
        },
        "account_calls": {
            "name": "account_calls", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Accounts"
        },
        "account_emails": {
            "name": "account_emails", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Accounts"
        },
        "account_leads": {
            "name": "account_leads", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "account_id", "relationship_type": "one-to-many"
        },
        "account_campaign_log": {
            "name": "account_campaign_log", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "CampaignLog", "rhs_table": "campaign_log", "rhs_key": "target_id", "relationship_type": "one-to-many"
        },
        "leads_modified_user": {
            "name": "leads_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "leads_created_by": {
            "name": "leads_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "leads_assigned_user": {
            "name": "leads_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "leads_team_count_relationship": {
            "name": "leads_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "leads_teams": {
            "name": "leads_teams", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "leads_team": {
            "name": "leads_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "leads_email_addresses": {
            "name": "leads_email_addresses", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Leads"
        },
        "leads_email_addresses_primary": {
            "name": "leads_email_addresses_primary", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "primary_address", "relationship_role_column_value": "1"
        },
        "lead_direct_reports": {
            "name": "lead_direct_reports", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "reports_to_id", "relationship_type": "one-to-many"
        },
        "lead_tasks": {
            "name": "lead_tasks", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Leads"
        },
        "lead_notes": {
            "name": "lead_notes", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Leads"
        },
        "lead_meetings": {
            "name": "lead_meetings", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Leads"
        },
        "lead_calls": {
            "name": "lead_calls", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Leads"
        },
        "lead_emails": {
            "name": "lead_emails", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Leads"
        },
        "lead_campaign_log": {
            "name": "lead_campaign_log", "lhs_module": "Leads", "lhs_table": "leads", "lhs_key": "id", "rhs_module": "CampaignLog", "rhs_table": "campaign_log", "rhs_key": "target_id", "relationship_type": "one-to-many"
        },
        "cases_modified_user": {
            "name": "cases_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "cases_created_by": {
            "name": "cases_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "cases_assigned_user": {
            "name": "cases_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "cases_team_count_relationship": {
            "name": "cases_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "cases_teams": {
            "name": "cases_teams", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "cases_team": {
            "name": "cases_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Cases", "rhs_table": "cases", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "case_calls": {
            "name": "case_calls", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Cases"
        },
        "case_tasks": {
            "name": "case_tasks", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Cases"
        },
        "case_notes": {
            "name": "case_notes", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Cases"
        },
        "case_meetings": {
            "name": "case_meetings", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Cases"
        },
        "case_emails": {
            "name": "case_emails", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Cases"
        },
        "bugs_modified_user": {
            "name": "bugs_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "bugs_created_by": {
            "name": "bugs_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "bugs_assigned_user": {
            "name": "bugs_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "bugs_team_count_relationship": {
            "name": "bugs_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "bugs_teams": {
            "name": "bugs_teams", "lhs_module": "Bugs", "lhs_table": "bugs", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "bugs_team": {
            "name": "bugs_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "bug_tasks": {
            "name": "bug_tasks", "lhs_module": "Bugs", "lhs_table": "bugs", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Bugs"
        },
        "bug_meetings": {
            "name": "bug_meetings", "lhs_module": "Bugs", "lhs_table": "bugs", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Bugs"
        },
        "bug_calls": {
            "name": "bug_calls", "lhs_module": "Bugs", "lhs_table": "bugs", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Bugs"
        },
        "bug_emails": {
            "name": "bug_emails", "lhs_module": "Bugs", "lhs_table": "bugs", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Bugs"
        },
        "bug_notes": {
            "name": "bug_notes", "lhs_module": "Bugs", "lhs_table": "bugs", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Bugs"
        },
        "bugs_release": {
            "name": "bugs_release", "lhs_module": "Releases", "lhs_table": "releases", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "found_in_release", "relationship_type": "one-to-many"
        },
        "bugs_fixed_in_release": {
            "name": "bugs_fixed_in_release", "lhs_module": "Releases", "lhs_table": "releases", "lhs_key": "id", "rhs_module": "Bugs", "rhs_table": "bugs", "rhs_key": "fixed_in_release", "relationship_type": "one-to-many"
        },
        "prospectlists_assigned_user": {
            "name": "prospectlists_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "prospectlists", "rhs_table": "prospect_lists", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "prospectlists_team_count_relationship": {
            "name": "prospectlists_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "ProspectLists", "rhs_table": "prospect_lists", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "prospectlists_teams": {
            "name": "prospectlists_teams", "lhs_module": "ProspectLists", "lhs_table": "prospect_lists", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "prospectlists_team": {
            "name": "prospectlists_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "ProspectLists", "rhs_table": "prospect_lists", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "prospects_modified_user": {
            "name": "prospects_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "prospects_created_by": {
            "name": "prospects_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "prospects_assigned_user": {
            "name": "prospects_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "prospects_team_count_relationship": {
            "name": "prospects_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "prospects_teams": {
            "name": "prospects_teams", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "prospects_team": {
            "name": "prospects_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "prospects_email_addresses": {
            "name": "prospects_email_addresses", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Prospects"
        },
        "prospects_email_addresses_primary": {
            "name": "prospects_email_addresses_primary", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "primary_address", "relationship_role_column_value": "1"
        },
        "prospect_tasks": {
            "name": "prospect_tasks", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Prospects"
        },
        "prospect_notes": {
            "name": "prospect_notes", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Prospects"
        },
        "prospect_meetings": {
            "name": "prospect_meetings", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Prospects"
        },
        "prospect_calls": {
            "name": "prospect_calls", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Prospects"
        },
        "prospect_emails": {
            "name": "prospect_emails", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Prospects"
        },
        "prospect_campaign_log": {
            "name": "prospect_campaign_log", "lhs_module": "Prospects", "lhs_table": "prospects", "lhs_key": "id", "rhs_module": "CampaignLog", "rhs_table": "campaign_log", "rhs_key": "target_id", "relationship_type": "one-to-many"
        },
        "project_team_count_relationship": {
            "name": "project_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Project", "rhs_table": "project", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "project_teams": {
            "name": "project_teams", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "project_team": {
            "name": "project_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Project", "rhs_table": "project", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "projects_notes": {
            "name": "projects_notes", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Project"
        },
        "projects_tasks": {
            "name": "projects_tasks", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Project"
        },
        "projects_meetings": {
            "name": "projects_meetings", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Project"
        },
        "projects_calls": {
            "name": "projects_calls", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Project"
        },
        "projects_emails": {
            "name": "projects_emails", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Project"
        },
        "projects_project_tasks": {
            "name": "projects_project_tasks", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "project_id", "relationship_type": "one-to-many"
        },
        "projects_assigned_user": {
            "name": "projects_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Project", "rhs_table": "project", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "projects_modified_user": {
            "name": "projects_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Project", "rhs_table": "project", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "projects_created_by": {
            "name": "projects_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Project", "rhs_table": "project", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "projects_users_resources": {
            "name": "projects_users_resources", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "project_resources", "join_key_lhs": "project_id", "join_key_rhs": "resource_id", "relationship_role_column": "resource_type", "relationship_role_column_value": "Users"
        },
        "projects_contacts_resources": {
            "name": "projects_contacts_resources", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "project_resources", "join_key_lhs": "project_id", "join_key_rhs": "resource_id", "relationship_role_column": "resource_type", "relationship_role_column_value": "Contacts"
        },
        "projects_holidays": {
            "name": "projects_holidays", "lhs_module": "Project", "lhs_table": "project", "lhs_key": "id", "rhs_module": "Holidays", "rhs_table": "holidays", "rhs_key": "related_module_id", "relationship_type": "one-to-many"
        },
        "projecttask_team_count_relationship": {
            "name": "projecttask_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "projecttask_teams": {
            "name": "projecttask_teams", "lhs_module": "ProjectTask", "lhs_table": "project_task", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "projecttask_team": {
            "name": "projecttask_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "project_tasks_notes": {
            "name": "project_tasks_notes", "lhs_module": "ProjectTask", "lhs_table": "project_task", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "ProjectTask"
        },
        "project_tasks_tasks": {
            "name": "project_tasks_tasks", "lhs_module": "ProjectTask", "lhs_table": "project_task", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "ProjectTask"
        },
        "project_tasks_meetings": {
            "name": "project_tasks_meetings", "lhs_module": "ProjectTask", "lhs_table": "project_task", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "ProjectTask"
        },
        "project_tasks_calls": {
            "name": "project_tasks_calls", "lhs_module": "ProjectTask", "lhs_table": "project_task", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "ProjectTask"
        },
        "project_tasks_emails": {
            "name": "project_tasks_emails", "lhs_module": "ProjectTask", "lhs_table": "project_task", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "ProjectTask"
        },
        "project_tasks_assigned_user": {
            "name": "project_tasks_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "project_tasks_modified_user": {
            "name": "project_tasks_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "project_tasks_created_by": {
            "name": "project_tasks_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "ProjectTask", "rhs_table": "project_task", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "campaigns_modified_user": {
            "name": "campaigns_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "campaigns_created_by": {
            "name": "campaigns_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "campaigns_assigned_user": {
            "name": "campaigns_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "campaigns_team_count_relationship": {
            "name": "campaigns_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "campaigns_teams": {
            "name": "campaigns_teams", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "campaigns_team": {
            "name": "campaigns_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "campaign_accounts": {
            "name": "campaign_accounts", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "Accounts", "rhs_table": "accounts", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_contacts": {
            "name": "campaign_contacts", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_leads": {
            "name": "campaign_leads", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_prospects": {
            "name": "campaign_prospects", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "Prospects", "rhs_table": "prospects", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_opportunities": {
            "name": "campaign_opportunities", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_email_marketing": {
            "name": "campaign_email_marketing", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "EmailMarketing", "rhs_table": "email_marketing", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_emailman": {
            "name": "campaign_emailman", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "EmailMan", "rhs_table": "emailman", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_campaignlog": {
            "name": "campaign_campaignlog", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "CampaignLog", "rhs_table": "campaign_log", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "campaign_assigned_user": {
            "name": "campaign_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "campaign_modified_user": {
            "name": "campaign_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Campaigns", "rhs_table": "campaigns", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "email_template_email_marketings": {
            "name": "email_template_email_marketings", "lhs_module": "EmailTemplates", "lhs_table": "email_templates", "lhs_key": "id", "rhs_module": "EmailMarketing", "rhs_table": "email_marketing", "rhs_key": "template_id", "relationship_type": "one-to-many"
        },
        "campaignlog_contact": {
            "name": "campaignlog_contact", "lhs_module": "CampaignLog", "lhs_table": "campaign_log", "lhs_key": "related_id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "id", "relationship_type": "one-to-many"
        },
        "campaignlog_lead": {
            "name": "campaignlog_lead", "lhs_module": "CampaignLog", "lhs_table": "campaign_log", "lhs_key": "related_id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "id", "relationship_type": "one-to-many"
        },
        "campaign_campaigntrakers": {
            "name": "campaign_campaigntrakers", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "CampaignTrackers", "rhs_table": "campaign_trkrs", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "schedulers_modified_user": {
            "name": "schedulers_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Schedulers", "rhs_table": "schedulers", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "schedulers_created_by": {
            "name": "schedulers_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Schedulers", "rhs_table": "schedulers", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "schedulers_created_by_rel": {
            "name": "schedulers_created_by_rel", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Schedulers", "rhs_table": "schedulers", "rhs_key": "created_by", "relationship_type": "one-to-one"
        },
        "schedulers_modified_user_id_rel": {
            "name": "schedulers_modified_user_id_rel", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Schedulers", "rhs_table": "schedulers", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "schedulers_jobs_rel": {
            "name": "schedulers_jobs_rel", "lhs_module": "Schedulers", "lhs_table": "schedulers", "lhs_key": "id", "rhs_module": "SchedulersJobs", "rhs_table": "job_queue", "rhs_key": "scheduler_id", "relationship_type": "one-to-many"
        },
        "schedulersjobs_assigned_user": {
            "name": "schedulersjobs_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SchedulersJobs", "rhs_table": "schedulersjobs", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "contacts_modified_user": {
            "name": "contacts_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "contacts_created_by": {
            "name": "contacts_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "contacts_assigned_user": {
            "name": "contacts_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "contacts_team_count_relationship": {
            "name": "contacts_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "contacts_teams": {
            "name": "contacts_teams", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "contacts_team": {
            "name": "contacts_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "contacts_email_addresses": {
            "name": "contacts_email_addresses", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "bean_module", "relationship_role_column_value": "Contacts"
        },
        "contacts_email_addresses_primary": {
            "name": "contacts_email_addresses_primary", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "EmailAddresses", "rhs_table": "email_addresses", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "email_addr_bean_rel", "join_key_lhs": "bean_id", "join_key_rhs": "email_address_id", "relationship_role_column": "primary_address", "relationship_role_column_value": "1"
        },
        "contact_direct_reports": {
            "name": "contact_direct_reports", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Contacts", "rhs_table": "contacts", "rhs_key": "reports_to_id", "relationship_type": "one-to-many"
        },
        "contact_leads": {
            "name": "contact_leads", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "contact_id", "relationship_type": "one-to-many"
        },
        "contact_notes": {
            "name": "contact_notes", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "contact_id", "relationship_type": "one-to-many"
        },
        "contact_tasks": {
            "name": "contact_tasks", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "contact_id", "relationship_type": "one-to-many"
        },
        "contact_tasks_parent": {
            "name": "contact_tasks_parent", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Contacts"
        },
        "contact_products": {
            "name": "contact_products", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "contact_id", "relationship_type": "one-to-many"
        },
        "contact_campaign_log": {
            "name": "contact_campaign_log", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "CampaignLog", "rhs_table": "campaign_log", "rhs_key": "target_id", "relationship_type": "one-to-many"
        },
        "opportunities_modified_user": {
            "name": "opportunities_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "opportunities_created_by": {
            "name": "opportunities_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "opportunities_assigned_user": {
            "name": "opportunities_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "opportunities_team_count_relationship": {
            "name": "opportunities_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "opportunities_teams": {
            "name": "opportunities_teams", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "opportunities_team": {
            "name": "opportunities_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "opportunity_calls": {
            "name": "opportunity_calls", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Opportunities"
        },
        "opportunity_meetings": {
            "name": "opportunity_meetings", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Opportunities"
        },
        "opportunity_tasks": {
            "name": "opportunity_tasks", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Opportunities"
        },
        "opportunity_notes": {
            "name": "opportunity_notes", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Opportunities"
        },
        "opportunity_emails": {
            "name": "opportunity_emails", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Opportunities"
        },
        "opportunity_leads": {
            "name": "opportunity_leads", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "id", "rhs_module": "Leads", "rhs_table": "leads", "rhs_key": "opportunity_id", "relationship_type": "one-to-many"
        },
        "opportunity_currencies": {
            "name": "opportunity_currencies", "lhs_module": "Opportunities", "lhs_table": "opportunities", "lhs_key": "currency_id", "rhs_module": "Currencies", "rhs_table": "currencies", "rhs_key": "id", "relationship_type": "one-to-many"
        },
        "opportunities_campaign": {
            "name": "opportunities_campaign", "lhs_module": "Campaigns", "lhs_table": "campaigns", "lhs_key": "id", "rhs_module": "Opportunities", "rhs_table": "opportunities", "rhs_key": "campaign_id", "relationship_type": "one-to-many"
        },
        "emailtemplates_team_count_relationship": {
            "name": "emailtemplates_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "EmailTemplates", "rhs_table": "email_templates", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "emailtemplates_teams": {
            "name": "emailtemplates_teams", "lhs_module": "EmailTemplates", "lhs_table": "email_templates", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "emailtemplates_team": {
            "name": "emailtemplates_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "EmailTemplates", "rhs_table": "email_templates", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "emailtemplates_assigned_user": {
            "name": "emailtemplates_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "EmailTemplates", "rhs_table": "email_templates", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "notes_assigned_user": {
            "name": "notes_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "notes_team_count_relationship": {
            "name": "notes_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "notes_teams": {
            "name": "notes_teams", "lhs_module": "Notes", "lhs_table": "notes", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "notes_team": {
            "name": "notes_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "notes_modified_user": {
            "name": "notes_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "notes_created_by": {
            "name": "notes_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "calls_modified_user": {
            "name": "calls_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "calls_created_by": {
            "name": "calls_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "calls_assigned_user": {
            "name": "calls_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "calls_team_count_relationship": {
            "name": "calls_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "calls_teams": {
            "name": "calls_teams", "lhs_module": "Calls", "lhs_table": "calls", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "calls_team": {
            "name": "calls_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "calls_notes": {
            "name": "calls_notes", "lhs_module": "Calls", "lhs_table": "calls", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Calls"
        },
        "emails_team_count_relationship": {
            "name": "emails_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "emails_teams": {
            "name": "emails_teams", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "emails_team": {
            "name": "emails_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "emails_assigned_user": {
            "name": "emails_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "emails_modified_user": {
            "name": "emails_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "emails_created_by": {
            "name": "emails_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "emails_notes_rel": {
            "name": "emails_notes_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "emails_meetings_rel": {
            "name": "emails_meetings_rel", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "meetings_modified_user": {
            "name": "meetings_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "meetings_created_by": {
            "name": "meetings_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "meetings_assigned_user": {
            "name": "meetings_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "meetings_team_count_relationship": {
            "name": "meetings_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "meetings_teams": {
            "name": "meetings_teams", "lhs_module": "Meetings", "lhs_table": "meetings", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "meetings_team": {
            "name": "meetings_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "meetings_notes": {
            "name": "meetings_notes", "lhs_module": "Meetings", "lhs_table": "meetings", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Meetings"
        },
        "tasks_modified_user": {
            "name": "tasks_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "tasks_created_by": {
            "name": "tasks_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "tasks_assigned_user": {
            "name": "tasks_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "tasks_team_count_relationship": {
            "name": "tasks_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "tasks_teams": {
            "name": "tasks_teams", "lhs_module": "Tasks", "lhs_table": "tasks", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "tasks_team": {
            "name": "tasks_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "tasks_notes": {
            "name": "tasks_notes", "lhs_module": "Tasks", "lhs_table": "tasks", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "tracker_monitor_id": {
            "name": "tracker_monitor_id", "lhs_module": "TrackerPerfs", "lhs_table": "tracker_perf", "lhs_key": "monitor_id", "rhs_module": "Trackers", "rhs_table": "tracker", "rhs_key": "monitor_id", "relationship_type": "one-to-one"
        },
        "documents_modified_user": {
            "name": "documents_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "documents_created_by": {
            "name": "documents_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "documents_assigned_user": {
            "name": "documents_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "documents_team_count_relationship": {
            "name": "documents_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "documents_teams": {
            "name": "documents_teams", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "documents_team": {
            "name": "documents_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Documents", "rhs_table": "documents", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "document_revisions": {
            "name": "document_revisions", "lhs_module": "Documents", "lhs_table": "documents", "lhs_key": "id", "rhs_module": "DocumentRevisions", "rhs_table": "document_revisions", "rhs_key": "document_id", "relationship_type": "one-to-many"
        },
        "revisions_created_by": {
            "name": "revisions_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "DocumentRevisions", "rhs_table": "document_revisions", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "inboundemail_team_count_relationship": {
            "name": "inboundemail_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "InboundEmail", "rhs_table": "inbound_email", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "inboundemail_teams": {
            "name": "inboundemail_teams", "lhs_module": "InboundEmail", "lhs_table": "inbound_email", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "inboundemail_team": {
            "name": "inboundemail_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "InboundEmail", "rhs_table": "inbound_email", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "inbound_email_created_by": {
            "name": "inbound_email_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "InboundEmail", "rhs_table": "inbound_email", "rhs_key": "created_by", "relationship_type": "one-to-one"
        },
        "inbound_email_modified_user_id": {
            "name": "inbound_email_modified_user_id", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "InboundEmail", "rhs_table": "inbound_email", "rhs_key": "modified_user_id", "relationship_type": "one-to-one"
        },
        "savedsearch_team_count_relationship": {
            "name": "savedsearch_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "SavedSearch", "rhs_table": "saved_search", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "savedsearch_teams": {
            "name": "savedsearch_teams", "lhs_module": "SavedSearch", "lhs_table": "saved_search", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "savedsearch_team": {
            "name": "savedsearch_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "SavedSearch", "rhs_table": "saved_search", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "saved_search_assigned_user": {
            "name": "saved_search_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SavedSearch", "rhs_table": "saved_search", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "reports_team_count_relationship": {
            "name": "reports_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Reports", "rhs_table": "saved_reports", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "reports_teams": {
            "name": "reports_teams", "lhs_module": "Reports", "lhs_table": "saved_reports", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "reports_team": {
            "name": "reports_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Reports", "rhs_table": "saved_reports", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "report_assigned_user": {
            "name": "report_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Reports", "rhs_table": "saved_reports", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "team_memberships": {
            "name": "team_memberships", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Users", "rhs_table": "users", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_memberships", "join_key_lhs": "team_id", "join_key_rhs": "user_id"
        },
        "quotes_modified_user": {
            "name": "quotes_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "quotes_created_by": {
            "name": "quotes_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "quotes_assigned_user": {
            "name": "quotes_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "quotes_team_count_relationship": {
            "name": "quotes_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "quotes_teams": {
            "name": "quotes_teams", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "quotes_team": {
            "name": "quotes_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "quote_tasks": {
            "name": "quote_tasks", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Tasks", "rhs_table": "tasks", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Quotes"
        },
        "quote_notes": {
            "name": "quote_notes", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Quotes"
        },
        "quote_meetings": {
            "name": "quote_meetings", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Meetings", "rhs_table": "meetings", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Quotes"
        },
        "quote_calls": {
            "name": "quote_calls", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Calls", "rhs_table": "calls", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Quotes"
        },
        "quote_emails": {
            "name": "quote_emails", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Emails", "rhs_table": "emails", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Quotes"
        },
        "quote_products": {
            "name": "quote_products", "lhs_module": "Quotes", "lhs_table": "quotes", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "quote_id", "relationship_type": "one-to-many"
        },
        "products_modified_user": {
            "name": "products_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "products_created_by": {
            "name": "products_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "products_team_count_relationship": {
            "name": "products_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "products_teams": {
            "name": "products_teams", "lhs_module": "Products", "lhs_table": "products", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "products_team": {
            "name": "products_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "product_notes": {
            "name": "product_notes", "lhs_module": "Products", "lhs_table": "products", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Products"
        },
        "products_accounts": {
            "name": "products_accounts", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "account_id", "relationship_type": "one-to-many"
        },
        "product_categories": {
            "name": "product_categories", "lhs_module": "ProductCategories", "lhs_table": "product_categories", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "category_id", "relationship_type": "one-to-many"
        },
        "product_types": {
            "name": "product_types", "lhs_module": "ProductTypes", "lhs_table": "product_types", "lhs_key": "id", "rhs_module": "Products", "rhs_table": "products", "rhs_key": "type_id", "relationship_type": "one-to-many"
        },
        "productbundles_team_count_relationship": {
            "name": "productbundles_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "ProductBundles", "rhs_table": "product_bundles", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "productbundles_teams": {
            "name": "productbundles_teams", "lhs_module": "ProductBundles", "lhs_table": "product_bundles", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "productbundles_team": {
            "name": "productbundles_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "ProductBundles", "rhs_table": "product_bundles", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "product_templates_product_categories": {
            "name": "product_templates_product_categories", "lhs_module": "ProductCategories", "lhs_table": "product_categories", "lhs_key": "id", "rhs_module": "ProductTemplates", "rhs_table": "product_templates", "rhs_key": "category_id", "relationship_type": "one-to-many"
        },
        "product_templates_product_types": {
            "name": "product_templates_product_types", "lhs_module": "ProductTypes", "lhs_table": "product_types", "lhs_key": "id", "rhs_module": "ProductTemplates", "rhs_table": "product_templates", "rhs_key": "type_id", "relationship_type": "one-to-many"
        },
        "product_templates_manufacturers": {
            "name": "product_templates_manufacturers", "lhs_module": "Manufacturers", "lhs_table": "manufacturers", "lhs_key": "id", "rhs_module": "ProductTemplates", "rhs_table": "product_templates", "rhs_key": "manufacturer_id", "relationship_type": "one-to-many"
        },
        "product_templates_modified_user": {
            "name": "product_templates_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "ProductTemplates", "rhs_table": "product_templates", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "product_templates_created_by": {
            "name": "product_templates_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "ProductTemplates", "rhs_table": "product_templates", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "member_categories": {
            "name": "member_categories", "lhs_module": "ProductCategories", "lhs_table": "product_categories", "lhs_key": "id", "rhs_module": "ProductCategories", "rhs_table": "product_categories", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "shipper_quotes": {
            "name": "shipper_quotes", "lhs_module": "Shippers", "lhs_table": "shippers", "lhs_key": "id", "rhs_module": "Quotes", "rhs_table": "quotes", "rhs_key": "shipper_id", "relationship_type": "one-to-many"
        },
        "teamnotices_team_count_relationship": {
            "name": "teamnotices_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "TeamNotices", "rhs_table": "team_notices", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "teamnotices_teams": {
            "name": "teamnotices_teams", "lhs_module": "TeamNotices", "lhs_table": "team_notices", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "teamnotices_team": {
            "name": "teamnotices_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "TeamNotices", "rhs_table": "team_notices", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "timeperiod_forecast_schedules": {
            "name": "timeperiod_forecast_schedules", "lhs_module": "TimePeriods", "lhs_table": "timeperiods", "lhs_key": "id", "rhs_module": "Forecasts", "rhs_table": "forecast_schedule", "rhs_key": "timeperiod_id", "relationship_type": "one-to-many"
        },
        "related_timeperiods": {
            "name": "related_timeperiods", "lhs_module": "TimePeriods", "lhs_table": "timeperiods", "lhs_key": "id", "rhs_module": "TimePeriods", "rhs_table": "timeperiods", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "forecasts_created_by": {
            "name": "forecasts_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Forecasts", "rhs_table": "forecasts", "rhs_key": "user_id", "relationship_type": "one-to-many"
        },
        "workflow_triggers": {
            "name": "workflow_triggers", "lhs_module": "WorkFlow", "lhs_table": "workflow", "lhs_key": "id", "rhs_module": "WorkFlowTriggerShells", "rhs_table": "workflow_triggershells", "rhs_key": "parent_id", "relationship_role_column": "frame_type", "relationship_role_column_value": "Primary", "relationship_type": "one-to-many"
        },
        "workflow_trigger_filters": {
            "name": "workflow_trigger_filters", "lhs_module": "WorkFlow", "lhs_table": "workflow", "lhs_key": "id", "rhs_module": "WorkFlowTriggerShells", "rhs_table": "workflow_triggershells", "rhs_key": "parent_id", "relationship_role_column": "frame_type", "relationship_role_column_value": "Secondary", "relationship_type": "one-to-many"
        },
        "workflow_alerts": {
            "name": "workflow_alerts", "lhs_module": "WorkFlow", "lhs_table": "workflow", "lhs_key": "id", "rhs_module": "WorkFlowAlertShells", "rhs_table": "workflow_alertshells", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "workflow_actions": {
            "name": "workflow_actions", "lhs_module": "WorkFlow", "lhs_table": "workflow", "lhs_key": "id", "rhs_module": "WorkFlowActionShells", "rhs_table": "workflow_actionshells", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "past_triggers": {
            "name": "past_triggers", "lhs_module": "WorkFlowTriggerShells", "lhs_table": "workflow_triggershells", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "past_trigger", "relationship_type": "one-to-many"
        },
        "future_triggers": {
            "name": "future_triggers", "lhs_module": "WorkFlowTriggerShells", "lhs_table": "workflow_triggershells", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "future_trigger", "relationship_type": "one-to-many"
        },
        "trigger_expressions": {
            "name": "trigger_expressions", "lhs_module": "WorkFlowTriggerShells", "lhs_table": "workflow_triggershells", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "expression", "relationship_type": "one-to-many"
        },
        "alert_components": {
            "name": "alert_components", "lhs_module": "WorkFlowAlertShells", "lhs_table": "workflow_alertshells", "lhs_key": "id", "rhs_module": "WorkFlowAlerts", "rhs_table": "workflow_alerts", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "expressions": {
            "name": "expressions", "lhs_module": "WorkFlowAlerts", "lhs_table": "workflow_alerts", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "filter", "relationship_type": "one-to-many"
        },
        "rel1_alert_fil": {
            "name": "rel1_alert_fil", "lhs_module": "WorkFlowAlerts", "lhs_table": "workflow_alerts", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "rel1_alert_fil", "relationship_type": "one-to-many"
        },
        "rel2_alert_fil": {
            "name": "rel2_alert_fil", "lhs_module": "WorkFlowAlerts", "lhs_table": "workflow_alerts", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "rel2_alert_fil", "relationship_type": "one-to-many"
        },
        "actions": {
            "name": "actions", "lhs_module": "WorkFlowActionShells", "lhs_table": "workflow_actionshells", "lhs_key": "id", "rhs_module": "WorkFlowActions", "rhs_table": "workflow_actions", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "action_bridge": {
            "name": "action_bridge", "lhs_module": "WorkFlowActionShells", "lhs_table": "workflow_actionshells", "lhs_key": "id", "rhs_module": "WorkFlow", "rhs_table": "workflow", "rhs_key": "parent_id", "relationship_type": "one-to-many"
        },
        "rel1_action_fil": {
            "name": "rel1_action_fil", "lhs_module": "WorkFlowActionShells", "lhs_table": "workflow_actionshells", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "rel1_action_fil", "relationship_type": "one-to-many"
        },
        "member_expressions": {
            "name": "member_expressions", "lhs_module": "Expressions", "lhs_table": "expressions", "lhs_key": "id", "rhs_module": "Expressions", "rhs_table": "expressions", "rhs_key": "parent_exp_id", "relationship_type": "one-to-many"
        },
        "contracts_modified_user": {
            "name": "contracts_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Contracts", "rhs_table": "contracts", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "contracts_created_by": {
            "name": "contracts_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Contracts", "rhs_table": "contracts", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "contracts_assigned_user": {
            "name": "contracts_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Contracts", "rhs_table": "contracts", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "contracts_team_count_relationship": {
            "name": "contracts_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "Contracts", "rhs_table": "contracts", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "contracts_teams": {
            "name": "contracts_teams", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "contracts_team": {
            "name": "contracts_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "Contracts", "rhs_table": "contracts", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "contracts_contract_types": {
            "name": "contracts_contract_types", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "type", "rhs_module": "ContractTypes", "rhs_table": "contract_types", "rhs_key": "id", "relationship_type": "one-to-many"
        },
        "contract_notes": {
            "name": "contract_notes", "lhs_module": "Contracts", "lhs_table": "contracts", "lhs_key": "id", "rhs_module": "Notes", "rhs_table": "notes", "rhs_key": "parent_id", "relationship_role_column": "parent_type", "relationship_role_column_value": "Contracts", "relationship_type": "one-to-many"
        },
        "account_contracts": {
            "name": "account_contracts", "lhs_module": "Accounts", "lhs_table": "accounts", "lhs_key": "id", "rhs_module": "Contracts", "rhs_table": "contracts", "rhs_key": "account_id", "relationship_type": "one-to-many"
        },
        "kbdocuments_team_count_relationship": {
            "name": "kbdocuments_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "kbdocuments_teams": {
            "name": "kbdocuments_teams", "lhs_module": "KBDocuments", "lhs_table": "kbdocuments", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "kbdocuments_team": {
            "name": "kbdocuments_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "kbdocument_revisions": {
            "name": "kbdocument_revisions", "lhs_module": "KBDocuments", "lhs_table": "kbdocuments", "lhs_key": "id", "rhs_module": "KBDocumentRevisions", "rhs_table": "kbdocument_revisions", "rhs_key": "kbdocument_id", "relationship_type": "one-to-many"
        },
        "kbdocuments_modified_user": {
            "name": "kbdocuments_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "kbdocuments_created_by": {
            "name": "kbdocuments_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "kb_assigned_user": {
            "name": "kb_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "kbdoc_approver_user": {
            "name": "kbdoc_approver_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "kbdoc_approver_id", "relationship_type": "one-to-many"
        },
        "case_kbdocuments": {
            "name": "case_kbdocuments", "lhs_module": "Cases", "lhs_table": "cases", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Cases"
        },
        "email_kbdocuments": {
            "name": "email_kbdocuments", "lhs_module": "Emails", "lhs_table": "emails", "lhs_key": "id", "rhs_module": "KBDocuments", "rhs_table": "kbdocuments", "rhs_key": "parent_id", "relationship_type": "one-to-many", "relationship_role_column": "parent_type", "relationship_role_column_value": "Emails"
        },
        "kbrev_revisions_created_by": {
            "name": "kbrev_revisions_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "KBDocumentRevisions", "rhs_table": "kbdocument_revisions", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "kbtags_team_count_relationship": {
            "name": "kbtags_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "KBTags", "rhs_table": "kbtags", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "kbtags_teams": {
            "name": "kbtags_teams", "lhs_module": "KBTags", "lhs_table": "kbtags", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "kbtags_team": {
            "name": "kbtags_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "KBTags", "rhs_table": "kbtags", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "kbdocumentkbtags_team_count_relationship": {
            "name": "kbdocumentkbtags_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "KBDocumentKBTags", "rhs_table": "kbdocuments_kbtags", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "kbdocumentkbtags_teams": {
            "name": "kbdocumentkbtags_teams", "lhs_module": "KBDocumentKBTags", "lhs_table": "kbdocuments_kbtags", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "kbdocumentkbtags_team": {
            "name": "kbdocumentkbtags_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "KBDocumentKBTags", "rhs_table": "kbdocuments_kbtags", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "kbrevisions_created_by": {
            "name": "kbrevisions_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "KBDocumentKBTags", "rhs_table": "kbdocuments_kbtags", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "kbcontents_team_count_relationship": {
            "name": "kbcontents_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "KBContents", "rhs_table": "kbcontents", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "kbcontents_teams": {
            "name": "kbcontents_teams", "lhs_module": "KBContents", "lhs_table": "kbcontents", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "kbcontents_team": {
            "name": "kbcontents_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "KBContents", "rhs_table": "kbcontents", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "customqueries_team_count_relationship": {
            "name": "customqueries_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "CustomQueries", "rhs_table": "custom_queries", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "customqueries_teams": {
            "name": "customqueries_teams", "lhs_module": "CustomQueries", "lhs_table": "custom_queries", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "customqueries_team": {
            "name": "customqueries_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "CustomQueries", "rhs_table": "custom_queries", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "datasets_team_count_relationship": {
            "name": "datasets_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "DataSets", "rhs_table": "data_sets", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "datasets_teams": {
            "name": "datasets_teams", "lhs_module": "DataSets", "lhs_table": "data_sets", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "datasets_team": {
            "name": "datasets_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "DataSets", "rhs_table": "data_sets", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "reportmaker_team_count_relationship": {
            "name": "reportmaker_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "ReportMaker", "rhs_table": "report_maker", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "reportmaker_teams": {
            "name": "reportmaker_teams", "lhs_module": "ReportMaker", "lhs_table": "report_maker", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "reportmaker_team": {
            "name": "reportmaker_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "ReportMaker", "rhs_table": "report_maker", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "sugarfeed_modified_user": {
            "name": "sugarfeed_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SugarFeed", "rhs_table": "sugarfeed", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "sugarfeed_created_by": {
            "name": "sugarfeed_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SugarFeed", "rhs_table": "sugarfeed", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "sugarfeed_team_count_relationship": {
            "name": "sugarfeed_team_count_relationship", "lhs_module": "Teams", "lhs_table": "team_sets", "lhs_key": "id", "rhs_module": "SugarFeed", "rhs_table": "sugarfeed", "rhs_key": "team_set_id", "relationship_type": "one-to-many"
        },
        "sugarfeed_teams": {
            "name": "sugarfeed_teams", "lhs_module": "SugarFeed", "lhs_table": "sugarfeed", "lhs_key": "team_set_id", "rhs_module": "Teams", "rhs_table": "teams", "rhs_key": "id", "relationship_type": "many-to-many", "join_table": "team_sets_teams", "join_key_lhs": "team_set_id", "join_key_rhs": "team_id"
        },
        "sugarfeed_team": {
            "name": "sugarfeed_team", "lhs_module": "Teams", "lhs_table": "teams", "lhs_key": "id", "rhs_module": "SugarFeed", "rhs_table": "sugarfeed", "rhs_key": "team_id", "relationship_type": "one-to-many"
        },
        "sugarfeed_assigned_user": {
            "name": "sugarfeed_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SugarFeed", "rhs_table": "sugarfeed", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "notifications_modified_user": {
            "name": "notifications_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Notifications", "rhs_table": "notifications", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "notifications_created_by": {
            "name": "notifications_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Notifications", "rhs_table": "notifications", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "notifications_assigned_user": {
            "name": "notifications_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "Notifications", "rhs_table": "notifications", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "eapm_modified_user": {
            "name": "eapm_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "EAPM", "rhs_table": "eapm", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "eapm_created_by": {
            "name": "eapm_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "EAPM", "rhs_table": "eapm", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "eapm_assigned_user": {
            "name": "eapm_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "EAPM", "rhs_table": "eapm", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "oauthkeys_modified_user": {
            "name": "oauthkeys_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "OAuthKeys", "rhs_table": "oauthkeys", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "oauthkeys_created_by": {
            "name": "oauthkeys_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "OAuthKeys", "rhs_table": "oauthkeys", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "oauthkeys_assigned_user": {
            "name": "oauthkeys_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "OAuthKeys", "rhs_table": "oauthkeys", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "consumer_tokens": {
            "name": "consumer_tokens", "lhs_module": "OAuthKeys", "lhs_table": "oauth_consumer", "lhs_key": "id", "rhs_module": "OAuthTokens", "rhs_table": "oauth_tokens", "rhs_key": "consumer", "relationship_type": "one-to-many"
        },
        "oauthtokens_assigned_user": {
            "name": "oauthtokens_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "OAuthTokens", "rhs_table": "oauth_tokens", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "contacts_oauthtokens": {
            "name": "contacts_oauthtokens", "lhs_module": "Contacts", "lhs_table": "contacts", "lhs_key": "id", "rhs_module": "OAuthTokens", "rhs_table": "oauth_tokens", "rhs_key": "contact_id", "relationship_type": "one-to-many"
        },
        "sugarfavorites_modified_user": {
            "name": "sugarfavorites_modified_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SugarFavorites", "rhs_table": "sugarfavorites", "rhs_key": "modified_user_id", "relationship_type": "one-to-many"
        },
        "sugarfavorites_created_by": {
            "name": "sugarfavorites_created_by", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SugarFavorites", "rhs_table": "sugarfavorites", "rhs_key": "created_by", "relationship_type": "one-to-many"
        },
        "sugarfavorites_assigned_user": {
            "name": "sugarfavorites_assigned_user", "lhs_module": "Users", "lhs_table": "users", "lhs_key": "id", "rhs_module": "SugarFavorites", "rhs_table": "sugarfavorites", "rhs_key": "assigned_user_id", "relationship_type": "one-to-many"
        },
        "_hash": "eac57757c8f55c304d52b913341385bd"
    },
    "_hash": "87fd53cf7aa0604f200ee4f7304b83ad"
};