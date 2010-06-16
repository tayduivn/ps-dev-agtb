-- MySQL upgrade script for Sugar 5.2.0 to 5.5.1

ALTER TABLE upgrade_history modify column manifest longtext NULL;
-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE leads   drop INDEX idx_lead_stat_del,
		    drop INDEX idx_leads_tem_del_conv,
		    drop INDEX idx_team_del_user,
		    add column team_set_id char(36)  NULL ,
                    add index idx_leads_tmst_id (team_set_id);
ALTER TABLE contacts   drop INDEX idx_team_del_id_user,
		       add column team_set_id char(36)  NULL ,
                       add index idx_contacts_tmst_id (team_set_id);
ALTER TABLE accounts   drop INDEX idx_accnt_team_del,
		       add column team_set_id char(36)  NULL ,
                       add index idx_accounts_tmst_id (team_set_id);
ALTER TABLE opportunities   drop INDEX idx_opp_team_asg_del,
			    add column team_set_id char(36)  NULL ,
                            add index idx_opportunities_tmst_id (team_set_id);
ALTER TABLE cases   drop INDEX idx_case_tm_usr_stat_del,
		    add column team_set_id char(36)  NULL ,
                    add index idx_cases_tmst_id (team_set_id);
ALTER TABLE notes   add column team_set_id char(36)  NULL ,
                    add index idx_notes_tmst_id (team_set_id);
ALTER TABLE email_templates   add column team_set_id char(36)  NULL ,
                              add index idx_email_templates_tmst_id (team_set_id);
ALTER TABLE calls   drop INDEX idx_calls_team_id,
                    drop INDEX idx_team_id_status,
		    		add column team_set_id char(36)  NULL ,
                    add index idx_calls_tmst_id (team_set_id),
					add index idx_calls_par_del (parent_id, parent_type, deleted );

ALTER TABLE emails   drop INDEX idx_email_team_status,
		     add column team_set_id char(36)  NULL ,
                     add index idx_emails_tmst_id (team_set_id);
ALTER TABLE meetings   drop INDEX idx_meet_tm_usr_stat_del,
		       add column team_set_id char(36)  NULL ,
                       add index idx_meetings_tmst_id (team_set_id);
ALTER TABLE tasks   drop INDEX idx_tasks_team_id,
		    add column team_set_id char(36)  NULL ,
                    add index idx_tasks_tmst_id (team_set_id);
-- //END SUGARCRM flav=pro ONLY 

ALTER TABLE users   add column system_generated_password bool  DEFAULT '0' NOT NULL ,  
		    add column pwd_last_changed datetime  NULL ,
		    -- //BEGIN SUGARCRM flav=pro ONLY 
		    add team_set_id char(36) NULL ,
		    add index idx_users_tmst_id(team_set_id),
		    -- //END SUGARCRM flav=pro ONLY 
                    add column external_auth_only bool  DEFAULT '0' NULL;                    

ALTER TABLE outbound_email modify mail_smtpssl int(1) DEFAULT 0 NULL, 
						   modify column `type` varchar(15) NOT NULL default 'user',
						   modify column `mail_sendtype` varchar(8) NOT NULL default 'smtp';

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE bugs   add column team_set_id char(36)  NULL ,
                   add index idx_bugs_tmst_id (team_set_id);
ALTER TABLE feeds   add column team_set_id char(36)  NULL ,
                    add index idx_feeds_tmst_id (team_set_id);
ALTER TABLE project   add column team_set_id char(36)  NULL ,
                      add index idx_project_tmst_id (team_set_id);
ALTER TABLE project_task   add column team_set_id char(36)  NULL ,
                           add index idx_project_task_tmst_id (team_set_id);
ALTER TABLE campaigns   add column team_set_id char(36)  NULL ,
                        add index idx_campaigns_tmst_id (team_set_id);
ALTER TABLE prospect_lists   add column team_set_id char(36)  NULL ,
                             add index idx_prospect_lists_tmst_id (team_set_id);
ALTER TABLE prospects   drop INDEX idx_prospects_del_team,
			add column team_set_id char(36)  NULL ,
                        add index idx_prospects_tmst_id (team_set_id);
ALTER TABLE documents   add column team_set_id char(36)  NULL ,
                        add index idx_documents_tmst_id (team_set_id);
ALTER TABLE inbound_email   add column team_set_id char(36)  NULL ,
                            add index idx_inbound_email_tmst_id (team_set_id);
-- //END SUGARCRM flav=pro ONLY 

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE dashboards   add column team_set_id char(36)  NULL ,
                         add index idx_dashboards_tmst_id (team_set_id);
ALTER TABLE saved_search   add column team_set_id char(36)  NULL ,
                           add index idx_saved_search_tmst_id (team_set_id);
ALTER TABLE contracts   add column team_set_id char(36)  NULL ,
                        add index idx_contracts_tmst_id (team_set_id);
ALTER TABLE saved_reports   add column team_set_id char(36)  NULL ,
                            modify column content longtext  NULL ,
                            add index idx_saved_reports_tmst_id (team_set_id);

ALTER TABLE report_schedules   add column date_start date  NULL ;

ALTER TABLE teams   add column name_2 varchar(128)  NULL ,  
                    add column associated_user_id char(36)  NULL ;

CREATE TABLE team_sets (
				id char(36)  NOT NULL ,
				name varchar(128)  NULL ,
				team_md5 varchar(32)  NULL ,
				team_count int  DEFAULT '0' NULL ,
				date_modified datetime  NULL ,
				deleted bool  DEFAULT '0' NULL ,
				created_by char(36)  NULL  , 
				PRIMARY KEY (id),   
				KEY idx_team_sets_md5 (team_md5)) 
			CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE team_sets_modules (
				id char(36)  NOT NULL ,
				team_set_id char(36)  NULL ,
				module_table_name varchar(128)  NULL ,
				deleted bool  DEFAULT '0' NULL  , 
				PRIMARY KEY (id),   
				KEY idx_team_sets_modules (team_set_id)) 
			CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE team_notices   add column team_set_id char(36)  NULL ,
                           add index idx_team_notices_tmst_id (team_set_id);

ALTER TABLE quotes  add column discount decimal(26,6)  NULL ,                    
					add column team_set_id char(36)  NULL ,                   
                    add column deal_tot decimal(26,2)  NULL ,
                    add column deal_tot_usdollar decimal(26,2)  NULL ,
                    add column new_sub decimal(26,6)  NULL ,  
                    add column new_sub_usdollar decimal(26,6)  NULL ,
                    add index idx_quotes_tmst_id (team_set_id);

ALTER TABLE products    add column discount_amount decimal(26,6)  NULL , 						
						add column team_set_id char(36)  NULL ,						
						add column discount_select tinyint(1) default '0' ,  
						add column deal_calc decimal(26,6)  NULL ,  
						add column deal_calc_usdollar decimal(26,6)  NULL ,
						add column discount_amount_usdollar decimal(26,6) NULL,
                        add index idx_products_tmst_id (team_set_id);

ALTER TABLE product_bundles   add column deal_tot decimal(26,2)  NULL ,  
							  add column deal_tot_usdollar decimal(26,2)  NULL ,  
							  add column new_sub decimal(26,6)  NULL ,  
							  add column new_sub_usdollar decimal(26,6)  NULL ,
                              add column team_set_id char(36)  NULL ,
                              add index idx_product_bundles_tmst_id (team_set_id);

ALTER TABLE kbdocuments   add column team_set_id char(36)  NULL ,
                          add index idx_kbdocuments_tmst_id (team_set_id);
ALTER TABLE kbtags   modify column team_id char(36)  NULL ,  
                     add column team_set_id char(36)  NULL ,
                     add index idx_kbtags_tmst_id (team_set_id);
ALTER TABLE kbdocuments_kbtags   add column team_set_id char(36)  NULL ,
                                 add index idx_doc_id_tag_id (kbdocument_id, kbtag_id),
                                 add index idx_kbdocuments_kbtags_tmst_id (team_set_id);
ALTER TABLE kbdocument_revisions add index idx_cont_id_doc_id (kbcontent_id, kbdocument_id),
                                 add index idx_name_rev_id_del (document_revision_id, kbdocument_id, deleted);
ALTER TABLE kbcontents   add column team_set_id char(36)  NULL ,
                         add index idx_kbcontents_tmst_id (team_set_id);
-- //END SUGARCRM flav=pro ONLY 

-- //BEGIN SUGARCRM flav=ent ONLY 
ALTER TABLE custom_queries   add column team_set_id char(36)  NULL ,
                             add index idx_custom_queries_tmst_id (team_set_id);
ALTER TABLE data_sets   add column team_set_id char(36)  NULL ,
                        add index idx_data_sets_tmst_id (team_set_id);
ALTER TABLE report_maker   add column team_set_id char(36)  NULL ,
                           add index idx_report_maker_tmst_id (team_set_id);
-- //END SUGARCRM flav=ent ONLY 

ALTER TABLE sugarfeed   drop index sgrfeed_date ,  					    
                        -- //BEGIN SUGARCRM flav=pro ONLY 
                        add column team_set_id char(36)  NULL,
                        add index idx_sugarfeed_tmst_id (team_set_id),
						-- //END SUGARCRM flav=pro ONLY 
						add index sgrfeed_date (date_entered, deleted)
						;

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE folders   add column team_set_id char(36)  NOT NULL ;
CREATE TABLE team_sets_teams (
                id char(36)  NOT NULL ,
                team_set_id char(36)  NULL ,
                team_id char(36)  NULL ,
                date_modified datetime  NULL ,
                deleted bool  DEFAULT '0' NULL  ,
                KEY idx_ud_set_id (team_set_id, team_id),
                KEY idx_ud_team_id (team_id), 
                KEY idx_ud_team_set_id (team_set_id))
            CHARACTER SET utf8 COLLATE utf8_general_ci;
-- //END SUGARCRM flav=pro ONLY 

CREATE TABLE users_password_link (
                id char(36)  NOT NULL ,
                username varchar(36)  NULL ,
                date_generated datetime  NULL ,
                deleted bool  DEFAULT 0  NOT NULL  , 
                PRIMARY KEY (id),   
                KEY idx_username (username)) 
            CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE import_maps   modify column content text  NULL ,  modify column default_values text  NULL ;

create index `idx_mail_to`  on `email_cache` (`toaddr` );