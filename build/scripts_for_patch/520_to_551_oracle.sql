-- Oracle upgrade script for Sugar 5.2.0 to 5.5.1

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE LEADS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE CONTACTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE ACCOUNTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE OPPORTUNITIES ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE CASES ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE NOTES ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE EMAIL_TEMPLATES ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE CALLS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE EMAILS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE MEETINGS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE TASKS ADD team_set_id varchar2(36)  NULL ;
-- //END SUGARCRM flav=pro ONLY 

ALTER TABLE USERS ADD (
					system_generated_password number(1)  DEFAULT 0 NOT NULL,
					pwd_last_changed date  NULL ,
					-- //BEGIN SUGARCRM flav=pro ONLY 
					team_set_id varchar2(36) NULL ,
					-- //END SUGARCRM flav=pro ONLY 
					external_auth_only number(1)  DEFAULT '0' NULL);

ALTER TABLE outbound_email modify(mail_smtpssl number,
								  TYPE VARCHAR2(15),
        						  MAIL_SENDTYPE  DEFAULT 'smtp');

-- //BEGIN SUGARCRM flav=pro ONLY 
create index idx_users_tmst_id  on users (team_set_id);
-- //END SUGARCRM flav=pro ONLY 

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE BUGS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE FEEDS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE PROJECT ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE PROJECT_TASK ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE CAMPAIGNS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE PROSPECT_LISTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE PROSPECTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE DOCUMENTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE INBOUND_EMAIL ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE DASHBOARDS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE SAVED_SEARCH ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE CONTRACTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE SAVED_REPORTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE report_schedules ADD date_start date  NULL ;
--conver the type of field CONTENT from BLOB to CLOB
ALTER TABLE SAVED_REPORTS ADD CONTENT_TMP CLOB;
UPDATE SAVED_REPORTS SET CONTENT_TMP = blob_to_clob(CONTENT) WHERE CONTENT IS NOT NULL;
ALTER TABLE SAVED_REPORTS DROP COLUMN CONTENT;
ALTER TABLE SAVED_REPORTS RENAME COLUMN CONTENT_TMP TO CONTENT;

ALTER TABLE TEAMS ADD (
					name_2 varchar2(128)  NULL ,
					associated_user_id varchar2(36)  NULL) ;

CREATE TABLE team_sets (
				id varchar2(36)  NOT NULL ,
				name varchar2(128)  NULL ,
				team_md5 varchar2(32)  NULL ,
				team_count number  DEFAULT '0' NULL ,
				date_modified date  NULL ,
				deleted number(1)  DEFAULT '0' NULL ,
				created_by varchar2(36)  NULL ,
                constraint team_setspk primary key(id));
CREATE INDEX idx_team_sets_md5 ON team_sets (team_md5);

CREATE TABLE team_sets_modules (
				id varchar2(36)  NOT NULL ,
				team_set_id varchar2(36)  NULL ,
				module_table_name varchar2(128)  NULL ,
				deleted number(1)  DEFAULT '0' NULL,
                constraint team_sets_modulespk primary key(id));
CREATE INDEX idx_team_sets_modules ON team_sets_modules (team_set_id);

ALTER TABLE TEAM_NOTICES ADD team_set_id varchar2(36)  NULL ;

ALTER TABLE QUOTES ADD (
					team_set_id varchar2(36)  NULL ,
					discount number (20,2)  NULL ,
					deal_tot number (20,2)  NULL ,
					deal_tot_usdollar number (20,2)  NULL ,
					new_sub number (20,2)  NULL ,
					new_sub_usdollar number (20,2)  NULL );

ALTER TABLE PRODUCTS ADD (
						team_set_id varchar2(36)  NULL ,
						discount_amount number (20,2)  NULL ,
						discount_select number(1) default 0 ,
						deal_calc number (26,6)  NULL ,
						deal_calc_usdollar number (26,6)  NULL ,
						discount_amount_usdollar decimal(20,2)  NULL);

ALTER TABLE PRODUCT_BUNDLES ADD (
								deal_tot number (20,2)  NULL ,
								deal_tot_usdollar number (20,2)  NULL ,
								new_sub number (20,2)  NULL ,
								new_sub_usdollar number (20,2)  NULL );

ALTER TABLE KBDOCUMENTS ADD team_set_id varchar2(36)  NULL ;

 ALTER TABLE KBTAGS ADD (TMP_TEAM_ID VARCHAR2(36) NULL, team_set_id varchar2(36)  NULL);

 UPDATE KBTAGS SET TMP_TEAM_ID=TEAM_ID ;
 ALTER TABLE KBTAGS DROP COLUMN TEAM_ID ;
 ALTER TABLE KBTAGS RENAME COLUMN TMP_TEAM_ID TO TEAM_ID;

CREATE INDEX idx_kbdocuments_kbtags_tmst_id ON KBDOCUMENTS_KBTAGS (team_set_id);
CREATE INDEX idx_doc_id_tag_id ON KBDOCUMENTS_KBTAGS (kbdocument_id, kbtag_id);

CREATE INDEX idx_cont_id_doc_id ON kbdocument_revisions (kbcontent_id, kbdocument_id);  
CREATE INDEX idx_name_rev_id_del ON kbdocument_revisions (document_revision_id, kbdocument_id, deleted);

ALTER TABLE KBDOCUMENTS_KBTAGS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE KBCONTENTS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE SUGARFEED ADD team_set_id varchar2(36)  NULL ;
-- //END SUGARCRM flav=pro ONLY 

-- //BEGIN SUGARCRM flav=ent ONLY 
ALTER TABLE CUSTOM_QUERIES ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE DATA_SETS ADD team_set_id varchar2(36)  NULL ;
ALTER TABLE REPORT_MAKER ADD team_set_id varchar2(36)  NULL ;
-- //END SUGARCRM flav=ent ONLY 

DROP INDEX sgrfeed_date ;
CREATE INDEX sgrfeed_date ON sugarfeed (date_entered,deleted);

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE FOLDERS ADD team_set_id varchar2(36)  NOT NULL ;
CREATE TABLE team_sets_teams (
                id varchar2(36)  NOT NULL ,
                team_set_id varchar2(36)  NULL ,
                team_id varchar2(36)  NULL ,
                date_modified date  NULL ,
                deleted number(1)  DEFAULT '0' NULL );
CREATE INDEX idx_ud_set_id ON team_sets_teams (team_set_id,team_id);
CREATE INDEX idx_ud_team_id ON team_sets_teams (team_id);
CREATE INDEX idx_ud_team_set_id ON team_sets_teams (team_set_id);
-- //END SUGARCRM flav=pro ONLY 

CREATE TABLE users_password_link (
                id varchar2(36)  NOT NULL ,
                username varchar2(36)  NULL ,
                date_generated date  NULL ,
                deleted number(1)  DEFAULT 0  NOT NULL,
                constraint users_password_link_pk primary key(id));
CREATE INDEX idx_username ON users_password_link (username);

ALTER TABLE IMPORT_MAPS ADD (
                            CONTENT_TMP CLOB,
                            DEFAULT_VALUES_TMP CLOB );

UPDATE IMPORT_MAPS SET 
                    CONTENT_TMP = blob_to_clob(CONTENT) ,
                    DEFAULT_VALUES_TMP = blob_to_clob(DEFAULT_VALUES);

ALTER TABLE IMPORT_MAPS DROP COLUMN CONTENT;
ALTER TABLE IMPORT_MAPS DROP COLUMN DEFAULT_VALUES;

ALTER TABLE IMPORT_MAPS RENAME COLUMN CONTENT_TMP TO CONTENT;
ALTER TABLE IMPORT_MAPS RENAME COLUMN DEFAULT_VALUES_TMP TO DEFAULT_VALUES;

-- //BEGIN SUGARCRM flav=pro ONLY 
DROP INDEX idx_accnt_team_del ;
DROP INDEX idx_calls_team_id;
DROP INDEX idx_team_id_status;
DROP INDEX idx_case_tm_usr_stat_del;
DROP INDEX idx_team_del_id_user;
DROP INDEX idx_email_team_status;
DROP INDEX idx_lead_stat_del;
DROP INDEX idx_leads_tem_del_conv;
DROP INDEX idx_team_del_user;
DROP INDEX idx_meet_tm_usr_stat_del;
DROP INDEX idx_opp_team_asg_del;
DROP INDEX idx_prospects_del_team;
DROP INDEX idx_tasks_team_id;
-- //END SUGARCRM flav=pro ONLY 

create index idx_calls_par_del  on calls (parent_id, parent_type, deleted );

create index idx_mail_to  on email_cache (toaddr );