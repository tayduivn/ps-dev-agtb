-- MSSQL upgrade script for Sugar 5.2.0 to 5.5.1

-- //BEGIN SUGARCRM flav=pro ONLY 
 ALTER TABLE leads ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE contacts ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE accounts ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE opportunities ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE cases ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE notes ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE email_templates ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE calls ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE emails ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE meetings ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE tasks ADD team_set_id varchar(36)  NULL ;
-- //END SUGARCRM flav=pro ONLY 

 ALTER TABLE users ADD system_generated_password bit  DEFAULT '0' NOT NULL ,
                       pwd_last_changed datetime  NULL ,
		       -- //BEGIN SUGARCRM flav=pro ONLY 
		       team_set_id varchar(36) NULL,
		       -- //END SUGARCRM flav=pro ONLY 
                       external_auth_only bit  DEFAULT '0' NULL;

-- //BEGIN SUGARCRM flav=pro ONLY 
create index idx_users_tmst_id  on users (team_set_id);
-- //END SUGARCRM flav=pro ONLY 

-- //BEGIN SUGARCRM flav=pro ONLY 
 ALTER TABLE bugs ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE feeds ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE project ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE project_task ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE campaigns ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE prospect_lists ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE prospects ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE documents ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE inbound_email ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE dashboards ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE saved_search ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE contracts ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE saved_reports ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE report_schedules ADD date_start datetime  NULL ;
 ALTER TABLE teams ADD name_2 varchar(128)  NULL ,
                       associated_user_id varchar(36)  NULL ;

CREATE TABLE team_sets (
	id varchar(36)  NOT NULL ,
	name varchar(128)  NULL ,
	team_md5 varchar(32)  NULL ,
	team_count int  DEFAULT '0' NULL ,
	date_modified datetime  NULL ,
	deleted bit  DEFAULT '0' NULL ,
	created_by varchar(36)  NULL  ) 
ALTER TABLE team_sets 
	ADD CONSTRAINT pk_team_sets PRIMARY KEY (id) 
	create index idx_team_sets_md5 on team_sets ( team_md5 );

CREATE TABLE team_sets_modules (
	id varchar(36)  NOT NULL ,
	team_set_id varchar(36)  NULL ,
	module_table_name varchar(128)  NULL ,
	deleted bit  DEFAULT '0' NULL  ) 
ALTER TABLE team_sets_modules 
	ADD CONSTRAINT pk_team_sets_modules PRIMARY KEY (id) 
	create index idx_team_sets_modules on team_sets_modules ( team_set_id );

 ALTER TABLE team_notices ADD team_set_id varchar(36)  NULL ;

 ALTER TABLE quotes ADD team_set_id varchar(36)  NULL ,
						 discount decimal(26,6)  NULL ,
						 deal_tot decimal(26,2)  NULL ,
						 deal_tot_usdollar decimal(26,2)  NULL ,
						 new_sub decimal(26,6)  NULL ,
						 new_sub_usdollar decimal(26,6)  NULL ;

 ALTER TABLE products ADD team_set_id varchar(36)  NULL ,
						  discount_amount decimal(26,6)  NULL ,
						  discount_select bit  DEFAULT 0  NULL,
						  deal_calc decimal(26,6)  NULL ,
						  deal_calc_usdollar decimal(26,6)  NULL,
						  discount_amount_usdollar decimal(26,6)  NULL;

 ALTER TABLE product_bundles ADD deal_tot decimal(26,2)  NULL ,
								 deal_tot_usdollar decimal(26,2)  NULL ,
								 new_sub decimal(26,6)  NULL ,
								 new_sub_usdollar decimal(26,6)  NULL ;

 ALTER TABLE kbdocuments ADD team_set_id varchar(36)  NULL ;

 ALTER TABLE kbtags ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE kbdocuments_kbtags ADD team_set_id varchar(36)  NULL ;
 create index idx_doc_id_tag_id on kbdocuments_kbtags ( kbdocument_id, kbtag_id );
 ALTER TABLE kbcontents ADD team_set_id varchar(36)  NULL ;
 create index idx_cont_id_doc_id on kbdocument_revisions ( kbcontent_id, kbdocument_id );
 create index idx_name_rev_id_del on kbdocument_revisions ( document_revision_id, kbdocument_id, deleted );
 ALTER TABLE sugarfeed ADD team_set_id varchar(36)  NULL ;
-- //END SUGARCRM flav=pro ONLY 

-- //BEGIN SUGARCRM flav=ent ONLY 
 ALTER TABLE custom_queries ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE data_sets ADD team_set_id varchar(36)  NULL ;
 ALTER TABLE report_maker ADD team_set_id varchar(36)  NULL ;  
-- //END SUGARCRM flav=ent ONLY 

DROP INDEX sgrfeed_date on sugarfeed;
create index sgrfeed_date on sugarfeed ( date_entered, deleted );

-- //BEGIN SUGARCRM flav=pro ONLY 
 ALTER TABLE folders ADD team_set_id varchar(36)  NOT NULL ;
CREATE TABLE team_sets_teams (
                id varchar(36)  NOT NULL ,
                team_set_id varchar(36)  NULL ,
                team_id varchar(36)  NULL ,
                date_modified datetime  NULL ,
                deleted bit  DEFAULT '0' NULL  ) 
create index idx_ud_set_id on team_sets_teams ( team_set_id, team_id ) 
create index idx_ud_team_id on team_sets_teams ( team_id )
create index idx_ud_team_set_id on team_sets_teams ( team_set_id );

-- //END SUGARCRM flav=pro ONLY 
CREATE TABLE users_password_link (
                id varchar(36)  NOT NULL ,
                username varchar(36)  NULL ,
                date_generated datetime  NULL ,
                deleted bit  DEFAULT 0  NOT NULL  ) 
ALTER TABLE users_password_link ADD CONSTRAINT pk_users_password_link PRIMARY KEY (id) 
create index idx_username on users_password_link ( username );

-- //BEGIN SUGARCRM flav=pro ONLY 
IF EXISTS (select * from sys.indexes where name='idx_accnt_team_del') 
drop index accounts.idx_accnt_team_del;

IF EXISTS (select * from sys.indexes where name='idx_calls_team_id') 
drop index calls.idx_calls_team_id;

IF EXISTS (select * from sys.indexes where name='idx_team_id_status') 
drop index calls.idx_team_id_status;

IF EXISTS (select * from sys.indexes where name='idx_case_tm_usr_stat_del') 
drop index cases.idx_case_tm_usr_stat_del;

IF EXISTS (select * from sys.indexes where name='idx_team_del_id_user') 
drop index contacts.idx_team_del_id_user;

IF EXISTS (select * from sys.indexes where name='idx_email_team_status') 
drop index emails.idx_email_team_status;

IF EXISTS (select * from sys.indexes where name='idx_lead_stat_del') 
drop index leads.idx_lead_stat_del;

IF EXISTS (select * from sys.indexes where name='idx_leads_tem_del_conv') 
drop index leads.idx_leads_tem_del_conv;

IF EXISTS (select * from sys.indexes where name='idx_team_del_user') 
drop index leads.idx_team_del_user;

IF EXISTS (select * from sys.indexes where name='idx_meet_tm_usr_stat_del') 
drop index meetings.idx_meet_tm_usr_stat_del;

IF EXISTS (select * from sys.indexes where name='idx_opp_team_asg_del') 
drop index opportunities.idx_opp_team_asg_del;

IF EXISTS (select * from sys.indexes where name='idx_prospects_del_team') 
drop index prospects.idx_prospects_del_team;

IF EXISTS (select * from sys.indexes where name='idx_tasks_team_id') 
drop index tasks.idx_tasks_team_id;
-- //END SUGARCRM flav=pro ONLY 

create index idx_calls_par_del  on calls (parent_id, parent_type, deleted );

create index idx_mail_to  on email_cache (toaddr );

alter table outbound_email alter column type varchar(15) NOT NULL;