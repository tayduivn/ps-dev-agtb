-- MySQL upgrade script for Sugar 5.5.1 Pro to 5.5.1 Ent

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

ALTER TABLE bugs ADD portal_viewable bool NULL DEFAULT '0' COMMENT '';

ALTER TABLE contacts    
    ADD portal_password varchar(32) NULL DEFAULT NULL COMMENT '',
    ADD portal_name varchar(255) NULL default NULL,
    ADD portal_active bool NOT NULL default '0',
    ADD portal_app varchar(255) NULL default NULL;

ALTER TABLE cases ADD portal_viewable tinyint(1) NULL DEFAULT '0' COMMENT '' ;