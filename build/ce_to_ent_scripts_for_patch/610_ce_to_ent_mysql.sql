-- MySQL upgrade script for Sugar 5.5.1 CE to 5.5.1 Ent

CREATE TABLE acl_fields (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(150) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    category varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    aclaccess int(3) NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    role_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_aclfield_role_del (role_id, category, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE address_book_list_items (
    list_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    bean_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    INDEX abli_list_id_idx (list_id),
    INDEX abli_list_id_bean_idx (list_id, bean_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE address_book_lists (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    assigned_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    list_name varchar(100) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX abml_user_bean_idx (assigned_user_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE category_tree (
    self_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    node_id int(11) NOT NULL COMMENT '' auto_increment,
    parent_node_id int(11) NULL DEFAULT '0' COMMENT '',
    type varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (node_id),
    INDEX idx_categorytree (self_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contract_types (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(30) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contracts (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(255) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    assigned_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    reference_code varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    account_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    start_date date NULL DEFAULT NULL COMMENT '',
    end_date date NULL DEFAULT NULL COMMENT '',
    currency_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    total_contract_value decimal(26,6) NULL DEFAULT NULL COMMENT '',
    total_contract_value_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    status varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    customer_signed_date date NULL DEFAULT NULL COMMENT '',
    company_signed_date date NULL DEFAULT NULL COMMENT '',
    expiration_notice datetime NULL DEFAULT NULL COMMENT '',
    type varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_contracts_tmst_id (team_set_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contracts_audit (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_created datetime NULL DEFAULT NULL COMMENT '',
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field_name varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    data_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contracts_contacts (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    contact_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    contract_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX contracts_contacts_alt (contact_id, contract_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contracts_opportunities (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    opportunity_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    contract_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX contracts_opp_alt (contract_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contracts_products (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    product_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    contract_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX contracts_prod_alt (contract_id, product_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE contracts_quotes (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    quote_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    contract_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX contracts_quot_alt (contract_id, quote_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE custom_queries (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    custom_query text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    query_type varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    query_locked varchar(3) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_custom_queries_tmst_id (team_set_id),
    INDEX idx_customqueries (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE data_sets (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    report_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    query_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    list_order_y int(3) NULL DEFAULT '0' COMMENT '',
    exportable varchar(3) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    header varchar(3) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    table_width varchar(3) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    font_size varchar(8) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    output_default varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    prespace_y varchar(3) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    use_prev_header varchar(3) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    header_back_color varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    body_back_color varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    header_text_color varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    body_text_color varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    table_width_type varchar(3) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    custom_layout varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_data_sets_tmst_id (team_set_id),
    INDEX idx_dataset (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE dataset_attributes (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    display_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    display_name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    attribute_type varchar(8) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    font_size varchar(8) NULL DEFAULT '0' COMMENT '' COLLATE utf8_general_ci,
    cell_size varchar(3) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    size_type varchar(3) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    bg_color varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    font_color varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    wrap varchar(3) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    style varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    format_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_datasetatt (parent_id, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE dataset_layouts (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_value varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    layout_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    list_order_x int(4) NULL DEFAULT NULL COMMENT '',
    list_order_z int(4) NULL DEFAULT NULL COMMENT '',
    row_header_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    hide_column varchar(3) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_datasetlayout (parent_value, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE expressions (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    lhs_type varchar(15) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    lhs_field varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    lhs_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    lhs_value varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    lhs_group_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    operator varchar(15) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rhs_group_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rhs_type varchar(15) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rhs_field varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rhs_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rhs_value varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    exp_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    exp_order int(4) NULL DEFAULT NULL COMMENT '',
    parent_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_exp_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_exp_side int(8) NULL DEFAULT NULL COMMENT '',
    ext1 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    ext2 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    ext3 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_exp (parent_id, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE forecast_schedule (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    timeperiod_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    cascade_hierarchy tinyint(1) NULL DEFAULT '0' COMMENT '',
    forecast_start_date date NULL DEFAULT NULL COMMENT '',
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE forecasts (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    timeperiod_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    forecast_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    opp_count int(5) NULL DEFAULT NULL COMMENT '',
    opp_weigh_value int(11) NULL DEFAULT NULL COMMENT '',
    best_case int(11) NULL DEFAULT NULL COMMENT '',
    likely_case int(11) NULL DEFAULT NULL COMMENT '',
    worst_case int(11) NULL DEFAULT NULL COMMENT '',
    user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE holidays (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    holiday_date date NOT NULL COMMENT '',
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    person_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    person_type varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    related_module varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    related_module_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    resource_name varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_holiday_id_del (id, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE kbcontents (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    kbdocument_body longtext NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    document_revision_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    kb_index int(11) NOT NULL COMMENT '' auto_increment,
    PRIMARY KEY (id),
    UNIQUE fts_unique_idx (kb_index),
    INDEX idx_kbcontents_tmst_id (team_set_id),
    FULLTEXT INDEX kbdocument_body (kbdocument_body)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ENGINE=MyISAM;

CREATE TABLE kbcontents_audit (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_created datetime NULL DEFAULT NULL COMMENT '',
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field_name varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    data_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE kbdocument_revisions (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    change_log varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    kbdocument_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    filename varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    file_ext varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    file_mime_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    revision varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    latest tinyint(1) NULL DEFAULT '0' COMMENT '',
    kbcontent_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    document_revision_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_del_latest_kbcontent_id (deleted, latest, kbcontent_id),
    INDEX idx_cont_id_doc_id (kbcontent_id, kbdocument_id),
    INDEX idx_name_rev_id_del (document_revision_id, kbdocument_id, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE kbdocuments (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    kbdocument_name varchar(255) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    active_date date NULL DEFAULT NULL COMMENT '',
    exp_date date NULL DEFAULT NULL COMMENT '',
    status_id varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    is_external_article tinyint(1) NULL DEFAULT '0' COMMENT '',
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    kbdocument_revision_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    kbdocument_revision_number varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    mail_merge_document varchar(3) NULL DEFAULT 'off' COMMENT '' COLLATE utf8_general_ci,
    related_doc_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    related_doc_rev_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    is_template tinyint(1) NULL DEFAULT '0' COMMENT '',
    template_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    kbdoc_approver_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    assigned_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_kbdocuments_tmst_id (team_set_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE kbdocuments_kbtags (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    kbdocument_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    kbtag_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    revision varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_kbdocuments_kbtags_tmst_id (team_set_id),
    INDEX idx_doc_id_tag_id (kbdocument_id, kbtag_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE kbdocuments_views_ratings (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    kbdocument_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    views_number int(11) NULL DEFAULT '0' COMMENT '',
    ratings_number int(11) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_kbvr_kbdoc (kbdocument_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE kbtags (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_tag_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    tag_name varchar(255) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    root_tag tinyint(1) NULL DEFAULT '0' COMMENT '',
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    revision varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_kbtags_tmst_id (team_set_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE manufacturers (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_manufacturers (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_bundle_note (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    bundle_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    note_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    note_index int(11) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_pbn_bundle (bundle_id),
    INDEX idx_pbn_note (note_id),
    INDEX idx_pbn_pb_nb (note_id, bundle_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_bundle_notes (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_bundle_product (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    bundle_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    product_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    product_index int(11) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_pbp_bundle (bundle_id),
    INDEX idx_pbp_quote (product_id),
    INDEX idx_pbp_bq (product_id, bundle_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_bundle_quote (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    bundle_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    quote_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    bundle_index int(11) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_pbq_bundle (bundle_id),
    INDEX idx_pbq_quote (quote_id),
    INDEX idx_pbq_bq (quote_id, bundle_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_bundles (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    bundle_stage varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    tax decimal(26,6) NULL DEFAULT NULL COMMENT '',
    tax_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    total decimal(26,6) NULL DEFAULT NULL COMMENT '',
    total_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    subtotal_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    shipping_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    deal_tot decimal(26,2) NULL DEFAULT NULL COMMENT '',
    deal_tot_usdollar decimal(26,2) NULL DEFAULT NULL COMMENT '',
    new_sub decimal(26,6) NULL DEFAULT NULL COMMENT '',
    new_sub_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    subtotal decimal(26,6) NULL DEFAULT NULL COMMENT '',
    shipping decimal(26,6) NULL DEFAULT NULL COMMENT '',
    currency_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_product_bundles_tmst_id (team_set_id),
    INDEX idx_products_bundles (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_categories (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_productcategories (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_product (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    parent_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    child_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_pp_parent (parent_id),
    INDEX idx_pp_child (child_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_templates (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    type_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    manufacturer_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    category_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    mft_part_num varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    vendor_part_num varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_cost_price date NULL DEFAULT NULL COMMENT '',
    cost_price decimal(26,6) NOT NULL COMMENT '',
    discount_price decimal(26,6) NOT NULL COMMENT '',
    list_price decimal(26,6) NOT NULL COMMENT '',
    cost_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    list_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    currency_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    currency varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    tax_class varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_available date NULL DEFAULT NULL COMMENT '',
    website varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    weight decimal(12,2) NULL DEFAULT NULL COMMENT '',
    qty_in_stock int(5) NULL DEFAULT NULL COMMENT '',
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_description varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_contact varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_term varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    pricing_formula varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    pricing_factor int(4) NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_product_template (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE product_types (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_producttypes (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE products (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    product_template_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    account_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    contact_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    type_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    quote_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    manufacturer_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    category_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    mft_part_num varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    vendor_part_num varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_purchased date NULL DEFAULT NULL COMMENT '',
    cost_price decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount_price decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount_amount decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount_select tinyint(1) default '0' COMMENT '',
    deal_calc decimal(26,6) NULL DEFAULT NULL COMMENT '',
    deal_calc_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount_amount_usdollar decimal(26,6) default NULL,
    list_price decimal(26,6) NULL DEFAULT NULL COMMENT '',
    cost_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    list_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    currency_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    tax_class varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    website varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    weight decimal(12,2) NULL DEFAULT NULL COMMENT '',
    quantity int(5) NULL DEFAULT NULL COMMENT '',
    support_name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_description varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_contact varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    support_term varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_support_expires date NULL DEFAULT NULL COMMENT '',
    date_support_starts date NULL DEFAULT NULL COMMENT '',
    pricing_formula varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    pricing_factor int(4) NULL DEFAULT NULL COMMENT '',
    serial_number varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    asset_number varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    book_value decimal(26,6) NULL DEFAULT NULL COMMENT '',
    book_value_date date NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_products_tmst_id (team_set_id),
    INDEX idx_products (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE products_audit (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_created datetime NULL DEFAULT NULL COMMENT '',
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field_name varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    data_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE project_resources (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    project_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    resource_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    resource_type varchar(20) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE projects_quotes (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    quote_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    project_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_proj_quote_proj (project_id),
    INDEX idx_proj_quote_quote (quote_id),
    INDEX projects_quotes_alt (project_id, quote_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE quotas (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    timeperiod_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    quota_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    amount int(11) NOT NULL COMMENT '',
    amount_base_currency int(11) NOT NULL COMMENT '',
    currency_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    committed tinyint(1) NULL DEFAULT '0' COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE quotes (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    assigned_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    shipper_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    currency_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    taxrate_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    show_line_nums tinyint(1) NOT NULL DEFAULT '1' COMMENT '',
    calc_grand_total tinyint(1) NOT NULL DEFAULT '1' COMMENT '',
    quote_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_quote_expected_closed date NULL DEFAULT NULL COMMENT '',
    original_po_date date NULL DEFAULT NULL COMMENT '',
    payment_terms varchar(128) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_quote_closed date NULL DEFAULT NULL COMMENT '',
    date_order_shipped date NULL DEFAULT NULL COMMENT '',
    order_stage varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    quote_stage varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    purchase_order_num varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    quote_num int(11) NOT NULL COMMENT '' auto_increment,
    subtotal decimal(26,6) NULL DEFAULT NULL COMMENT '',
    subtotal_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    shipping decimal(26,6) NULL DEFAULT NULL COMMENT '',
    shipping_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    discount decimal(26,6) NULL DEFAULT NULL COMMENT '',
    deal_tot decimal(26,2) NULL DEFAULT NULL COMMENT '',
    deal_tot_usdollar decimal(26,2) NULL DEFAULT NULL COMMENT '',
    new_sub decimal(26,6) NULL DEFAULT NULL COMMENT '',
    new_sub_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    tax decimal(26,6) NULL DEFAULT NULL COMMENT '',
    tax_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    total decimal(26,6) NULL DEFAULT NULL COMMENT '',
    total_usdollar decimal(26,6) NULL DEFAULT NULL COMMENT '',
    billing_address_street varchar(150) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    billing_address_city varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    billing_address_state varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    billing_address_postalcode varchar(20) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    billing_address_country varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    shipping_address_street varchar(150) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    shipping_address_city varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    shipping_address_state varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    shipping_address_postalcode varchar(20) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    shipping_address_country varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    system_id int(11) NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    UNIQUE quote_num (quote_num, system_id),
    INDEX idx_quotes_tmst_id (team_set_id),
    INDEX idx_qte_name (name)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE quotes_accounts (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    quote_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    account_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    account_role varchar(20) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_acc_qte_acc (account_id),
    INDEX idx_acc_qte_opp (quote_id),
    INDEX idx_quote_account_role (quote_id, account_role)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE quotes_audit (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_created datetime NULL DEFAULT NULL COMMENT '',
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field_name varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    data_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_string varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    before_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    after_value_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE quotes_contacts (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    contact_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    quote_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    contact_role varchar(20) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_con_qte_con (contact_id),
    INDEX idx_con_qte_opp (quote_id),
    INDEX idx_quote_contact_role (quote_id, contact_role)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE quotes_opportunities (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    opportunity_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    quote_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_opp_qte_opp (opportunity_id),
    INDEX idx_quote_oportunities (quote_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE report_cache (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    assigned_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    contents text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    report_options text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted varchar(1) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    PRIMARY KEY (id, assigned_user_id, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE report_maker (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    title varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    report_align varchar(8) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    scheduled tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_report_maker_tmst_id (team_set_id),
    INDEX idx_rmaker (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE report_schedules (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    user_id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    report_id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    next_run datetime NOT NULL  COMMENT '',
    active tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    time_interval int(11) NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    schedule_type varchar(3) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_start date  NULL ,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE saved_reports (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(255) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    module varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    report_type varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    content longtext NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    assigned_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    is_published tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    chart_type varchar(36) NOT NULL DEFAULT 'none' COMMENT '' COLLATE utf8_general_ci,
    schedule_type varchar(3) NULL DEFAULT 'pro' COMMENT '' COLLATE utf8_general_ci,
    favorite tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_saved_reports_tmst_id (team_set_id),
    INDEX idx_rep_owner_module_name (assigned_user_id, name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE session_active (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    session_id varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    last_request_time datetime NULL DEFAULT NULL COMMENT '',
    session_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    is_violation tinyint(1) NULL DEFAULT '0' COMMENT '',
    num_active_sessions int(11) NULL DEFAULT '0' COMMENT '',
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    UNIQUE idx_session_id (session_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE session_history (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    session_id varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    last_request_time datetime NULL DEFAULT NULL COMMENT '',
    session_type varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    is_violation tinyint(1) NULL DEFAULT '0' COMMENT '',
    num_active_sessions int(11) NULL DEFAULT '0' COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE shippers (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_shippers (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE systems (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    system_id int(11) NOT NULL COMMENT '' auto_increment,
    system_key varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    last_connect_date datetime NULL DEFAULT NULL COMMENT '',
    status varchar(255) NULL DEFAULT 'Active' COMMENT '' COLLATE utf8_general_ci,
    num_syncs int(11) NULL DEFAULT '0' COMMENT '',
    system_name varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    install_method varchar(100) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX system_id (system_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE taxrates (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    value decimal(7,5) NULL DEFAULT NULL COMMENT '',
    list_order int(4) NULL DEFAULT NULL COMMENT '',
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_taxrates (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE team_memberships (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    explicit_assign tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    implicit_assign tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_team_membership (user_id, team_id),
    INDEX idx_teammemb_team_user (team_id, user_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE team_notices (
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    date_start date NOT NULL COMMENT '',
    date_end date NOT NULL COMMENT '',
    modified_user_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    status varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    url varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    url_title varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_team_notices_tmst_id (team_set_id),
    INDEX idx_team_notice (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE team_sets (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(128) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_md5 varchar(32) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_count int(11) NULL DEFAULT '0' COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_team_sets_md5 (team_md5)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE team_sets_modules (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    module_table_name varchar(128) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_team_sets_modules (team_set_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE team_sets_teams (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    INDEX idx_ud_set_id (team_set_id, team_id),
    INDEX idx_ud_team_id (team_id),
    INDEX idx_ud_team_set_id (team_set_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE teams (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(128) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name_2 varchar(128) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    associated_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    private tinyint(1) NULL DEFAULT '0' COMMENT '',
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_team_del (name),
    INDEX idx_team_del_name (deleted, name)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE timeperiods (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    name varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    start_date date NULL DEFAULT NULL COMMENT '',
    end_date date NULL DEFAULT NULL COMMENT '',
    created_by varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_entered datetime NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    is_fiscal_year tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE tracker_perf (
    id int(11) NOT NULL COMMENT '' auto_increment,
    monitor_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    server_response_time double NULL DEFAULT NULL COMMENT '',
    db_round_trips int(6) NULL DEFAULT NULL COMMENT '',
    files_opened int(6) NULL DEFAULT NULL COMMENT '',
    memory_usage int(12) NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_tracker_perf_mon_id (monitor_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE tracker_queries (
    id int(11) NOT NULL COMMENT '' auto_increment,
    query_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    `text` text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    query_hash varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    sec_total double NULL DEFAULT NULL COMMENT '',
    sec_avg double NULL DEFAULT NULL COMMENT '',
    run_count int(6) NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_tracker_queries_query_hash (query_hash),
    INDEX idx_tracker_queries_query_id (query_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE tracker_sessions (
    id int(11) NOT NULL COMMENT '' auto_increment,
    session_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_start datetime NULL DEFAULT NULL COMMENT '',
    date_end datetime NULL DEFAULT NULL COMMENT '',
    seconds int(9) NULL DEFAULT '0' COMMENT '',
    client_ip varchar(20) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    user_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    active tinyint(1) NULL DEFAULT '1' COMMENT '',
    round_trips int(5) NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_tracker_sessions_s_id (session_id),
    INDEX idx_tracker_sessions_uas_id (user_id, active, session_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE tracker_tracker_queries (
    id int(11) NOT NULL COMMENT '' auto_increment,
    monitor_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    query_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_tracker_tq_monitor (monitor_id),
    INDEX idx_tracker_tq_query (query_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE users_holidays (
    id varchar(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    user_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    holiday_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_user_holi_user (user_id),
    INDEX idx_user_holi_holi (holiday_id),
    INDEX users_quotes_alt (user_id, holiday_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    base_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    status tinyint(1) NULL DEFAULT '0' COMMENT '',
    description text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    fire_order varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    record_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    list_order_y int(3) NULL DEFAULT '0' COMMENT '',
    PRIMARY KEY (id),
    INDEX idx_workflow (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow_actions (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    value text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    set_type varchar(10) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    adv_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    ext1 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    ext2 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    ext3 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_action (deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow_actionshells (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    action_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parameters varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    action_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_actionshell (deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow_alerts (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field_value varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_email_value varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module1 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module2 varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module1_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module2_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    where_filter tinyint(1) NULL DEFAULT '0' COMMENT '',
    user_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    array_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    relate_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    address_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    user_display_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_workflowalerts (deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow_alertshells (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    name varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    alert_text text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    alert_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    source_type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    custom_template_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_workflowalertshell (name, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow_schedules (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    date_expired datetime NOT NULL  COMMENT '',
    workflow_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    target_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    bean_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parameters varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id),
    INDEX idx_wkfl_schedule (workflow_id, deleted)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE workflow_triggershells (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
    date_entered datetime NOT NULL  COMMENT '',
    date_modified datetime NOT NULL  COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    created_by char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    field varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    type varchar(25) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    frame_type varchar(15) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    eval text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parent_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    show_past tinyint(1) NULL DEFAULT '0' COMMENT '',
    rel_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    rel_module_type varchar(10) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    parameters varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE worksheet (
    id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    timeperiod_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    forecast_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    related_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    related_forecast_type varchar(25) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    best_case int(11) NULL DEFAULT NULL COMMENT '',
    likely_case int(11) NULL DEFAULT NULL COMMENT '',
    worst_case int(11) NULL DEFAULT NULL COMMENT '',
    date_modified datetime NULL DEFAULT NULL COMMENT '',
    modified_user_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE accounts
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_accounts_tmst_id (team_set_id);


ALTER TABLE bugs
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD system_id int(11) NULL DEFAULT NULL COMMENT '' AFTER resolution,
    ADD portal_viewable bool NULL DEFAULT '0' COMMENT '' AFTER product_category,
    DROP INDEX bug_number,
    ADD UNIQUE bug_number (bug_number, system_id),
    ADD INDEX idx_bugs_tmst_id (team_set_id);


ALTER TABLE calls
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_calls_tmst_id (team_set_id);


ALTER TABLE campaigns
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_campaigns_tmst_id (team_set_id);


ALTER TABLE cases
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD system_id int(11) NULL DEFAULT NULL COMMENT '' AFTER resolution,
    ADD portal_viewable tinyint(1) NULL DEFAULT '0' COMMENT '' AFTER account_id,
    DROP INDEX case_number,
    ADD UNIQUE case_number (case_number, system_id),
    ADD INDEX idx_cases_tmst_id (team_set_id);


ALTER TABLE contacts
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD portal_name varchar(255) NULL default NULL,
    ADD portal_active bool NOT NULL default '0',
    ADD portal_app varchar(255) NULL default NULL,
    ADD portal_password varchar(32) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER portal_active,
    ADD INDEX idx_contacts_tmst_id (team_set_id);

ALTER TABLE documents
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER deleted,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_documents_tmst_id (team_set_id);


ALTER TABLE email_templates
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD base_module varchar(50) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER deleted,
    ADD from_name varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER base_module,
    ADD from_address varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER from_name,
    ADD INDEX idx_email_templates_tmst_id (team_set_id);


ALTER TABLE emails
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_emails_tmst_id (team_set_id);


ALTER TABLE folders
    ADD team_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci AFTER assign_to_id,
    ADD team_set_id char(36) NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci AFTER team_id;


ALTER TABLE inbound_email
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_inbound_email_tmst_id (team_set_id);


ALTER TABLE leads
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_leads_tmst_id (team_set_id);


ALTER TABLE meetings
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_meetings_tmst_id (team_set_id);


ALTER TABLE notes
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_notes_tmst_id (team_set_id);


ALTER TABLE opportunities
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_opportunities_tmst_id (team_set_id);


ALTER TABLE project
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD is_template tinyint(1) NULL DEFAULT '0' COMMENT '' AFTER priority,
    ADD INDEX idx_project_tmst_id (team_set_id);


ALTER TABLE project_task
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD resource_id text NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER description,
    ADD INDEX idx_project_task_tmst_id (team_set_id);


ALTER TABLE prospect_lists
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_prospect_lists_tmst_id (team_set_id);


ALTER TABLE prospects
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_prospects_tmst_id (team_set_id);


ALTER TABLE saved_search
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci FIRST,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_saved_search_tmst_id (team_set_id);


ALTER TABLE sugarfeed
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER deleted,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_sugarfeed_tmst_id (team_set_id);


ALTER TABLE tasks
    ADD team_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER assigned_user_id,
    ADD team_set_id char(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER team_id,
    ADD INDEX idx_tasks_tmst_id (team_set_id);


ALTER TABLE tracker
    ADD team_id varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER item_summary;


ALTER TABLE users
    ADD default_team varchar(36) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,    
    ADD team_set_id char(36) NULL COLLATE utf8_general_ci,
    ADD index idx_users_tmst_id(team_set_id);

UPDATE `accounts` SET team_id = 1;
UPDATE `bugs` SET team_id = 1;
UPDATE `calls` SET team_id = 1;
UPDATE `campaigns` SET team_id = 1;
UPDATE `cases` SET team_id = 1;
UPDATE `contacts` SET team_id = 1;
UPDATE `contracts` SET team_id = 1;
UPDATE `documents` SET team_id = 1;
UPDATE `emails` SET team_id = 1;
UPDATE `email_templates` SET team_id = 1;
UPDATE `inbound_email` SET team_id = 1;
UPDATE `leads` SET team_id = 1;
UPDATE `meetings` SET team_id = 1;
UPDATE `notes` SET team_id = 1;
UPDATE `opportunities` SET team_id = 1;
UPDATE `project` SET team_id = 1;
UPDATE `project_task` SET team_id = 1;
UPDATE `prospect_lists` SET team_id = 1;
UPDATE `prospects` SET team_id = 1;
UPDATE `tasks` SET team_id = 1;
UPDATE `folders` SET team_id = 1 WHERE is_group = '1';

ALTER TABLE contracts_audit ADD INDEX idx_contracts_primary (id);
ALTER TABLE kbcontents_audit ADD INDEX idx_kbcontents_primary (id);
ALTER TABLE products_audit ADD INDEX idx_products_primary (id);
ALTER TABLE quotes_audit ADD INDEX idx_quotes_primary (id);
ALTER TABLE contracts_audit ADD INDEX idx_contracts_parent_id (parent_id);
ALTER TABLE kbcontents_audit ADD INDEX idx_kbcontents_parent_id (parent_id);       
ALTER TABLE products_audit ADD INDEX idx_products_parent_id (parent_id);
ALTER TABLE quotes_audit ADD INDEX idx_quotes_parent_id (parent_id);   