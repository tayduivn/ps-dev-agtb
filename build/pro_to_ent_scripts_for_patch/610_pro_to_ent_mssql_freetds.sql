-- MSSQL upgrade script for Sugar 5.5.1 Pro to 5.5.1 Ent

CREATE TABLE custom_queries (team_id nvarchar(36)  NULL ,team_set_id nvarchar(36)  NULL ,id nvarchar(36)  NOT NULL ,deleted bit  DEFAULT '0' NOT NULL ,date_entered datetime  NOT NULL ,date_modified datetime  NOT NULL ,modified_user_id nvarchar(36)  NULL ,created_by nvarchar(36)  NULL ,name nvarchar(50)  NULL ,description ntext  NULL ,custom_query ntext  NULL ,query_type nvarchar(50)  NULL ,list_order int  NULL ,query_locked nvarchar(3)  DEFAULT '0' NULL  ) ALTER TABLE custom_queries ADD CONSTRAINT pk_custom_queries PRIMARY KEY (id) create index idx_customqueries on custom_queries ( name, deleted ) create index idx_custom_queries_tmst_id on custom_queries ( team_set_id );

CREATE TABLE data_sets (team_id nvarchar(36)  NULL ,team_set_id nvarchar(36)  NULL ,id nvarchar(36)  NOT NULL ,deleted bit  DEFAULT '0' NOT NULL ,date_entered datetime  NOT NULL ,date_modified datetime  NOT NULL ,modified_user_id nvarchar(36)  NULL ,created_by nvarchar(36)  NULL ,parent_id nvarchar(36)  NULL ,report_id nvarchar(36)  NULL ,query_id nvarchar(36)  NOT NULL ,name nvarchar(50)  NULL ,list_order_y int  DEFAULT '0' NULL ,exportable nvarchar(3)  DEFAULT '0' NULL ,header nvarchar(3)  DEFAULT '0' NULL ,description ntext  NULL ,table_width nvarchar(3)  DEFAULT '0' NULL ,font_size nvarchar(8)  DEFAULT '0' NULL ,output_default nvarchar(25)  NULL ,prespace_y nvarchar(3)  DEFAULT '0' NULL ,use_prev_header nvarchar(3)  DEFAULT '0' NULL ,header_back_color nvarchar(25)  NULL ,body_back_color nvarchar(25)  NULL ,header_text_color nvarchar(25)  NULL ,body_text_color nvarchar(25)  NULL ,table_width_type nvarchar(3)  NULL ,custom_layout nvarchar(10)  NULL  ) ALTER TABLE data_sets ADD CONSTRAINT pk_data_sets PRIMARY KEY (id) create index idx_dataset on data_sets ( name, deleted ) create index idx_data_sets_tmst_id on data_sets ( team_set_id );

CREATE TABLE report_maker (team_id nvarchar(36)  NULL ,team_set_id nvarchar(36)  NULL ,id nvarchar(36)  NOT NULL ,deleted bit  DEFAULT '0' NOT NULL ,date_entered datetime  NOT NULL ,date_modified datetime  NOT NULL ,modified_user_id nvarchar(36)  NULL ,created_by nvarchar(36)  NULL ,name nvarchar(50)  NULL ,title nvarchar(50)  NULL ,report_align nvarchar(8)  NULL ,description ntext  NULL ,scheduled bit  DEFAULT '0' NULL  ) ALTER TABLE report_maker ADD CONSTRAINT pk_report_maker PRIMARY KEY (id) create index idx_rmaker on report_maker ( name, deleted ) create index idx_report_maker_tmst_id on report_maker ( team_set_id );

CREATE TABLE dataset_layouts (id nvarchar(36)  NOT NULL ,deleted bit  DEFAULT '0' NOT NULL ,date_entered datetime  NOT NULL ,date_modified datetime  NOT NULL ,modified_user_id nvarchar(36)  NULL ,created_by nvarchar(36)  NULL ,parent_value nvarchar(50)  NULL ,layout_type nvarchar(25)  NOT NULL ,parent_id nvarchar(36)  NULL ,list_order_x int  NULL ,list_order_z int  NULL ,row_header_id nvarchar(36)  NULL ,hide_column nvarchar(3)  NULL  ) ALTER TABLE dataset_layouts ADD CONSTRAINT pk_dataset_layouts PRIMARY KEY (id) create index idx_datasetlayout on dataset_layouts ( parent_value, deleted );

CREATE TABLE dataset_attributes (id nvarchar(36)  NOT NULL ,deleted bit  DEFAULT '0' NOT NULL ,date_entered datetime  NOT NULL ,date_modified datetime  NOT NULL ,modified_user_id nvarchar(36)  NULL ,created_by nvarchar(36)  NULL ,display_type nvarchar(25)  NOT NULL ,display_name nvarchar(50)  NULL ,attribute_type nvarchar(8)  NOT NULL ,parent_id nvarchar(36)  NULL ,font_size nvarchar(8)  DEFAULT '0' NULL ,cell_size nvarchar(3)  NULL ,size_type nvarchar(3)  NULL ,bg_color nvarchar(25)  NULL ,font_color nvarchar(25)  NULL ,wrap nvarchar(3)  NULL ,style nvarchar(25)  NULL ,format_type nvarchar(25)  NOT NULL  ) ALTER TABLE dataset_attributes ADD CONSTRAINT pk_dataset_attributes PRIMARY KEY (id) create index idx_datasetatt on dataset_attributes ( parent_id, deleted );

ALTER TABLE bugs ADD portal_viewable bit  DEFAULT '0' NULL ;

ALTER TABLE contacts ADD portal_password nvarchar(32)  NULL ,portal_name nvarchar(255) NULL default NULL,portal_active bit NOT NULL default '0',portal_app nvarchar(255) NULL default NULL;

ALTER TABLE cases ADD portal_viewable bit  DEFAULT '0' NULL ;

DROP INDEX idx_message_id on emails;

ALTER TABLE emails ALTER COLUMN message_id nvarchar(255) NULL;

CREATE INDEX idx_message_id on emails (message_id);