ALTER TABLE accounts_audit
    ADD INDEX idx_accounts_parent_id (parent_id);

ALTER TABLE bugs_audit
    ADD INDEX idx_bugs_parent_id (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE campaigns_audit
    ADD INDEX idx_campaigns_parent_id (parent_id);
-- //END SUGARCRM flav=pro ONLY 

ALTER TABLE cases_audit
    ADD INDEX idx_cases_parent_id (parent_id);

ALTER TABLE contacts_audit
    ADD INDEX idx_contacts_parent_id (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE contracts_audit
    ADD INDEX idx_contracts_parent_id (parent_id);
-- //END SUGARCRM flav=pro ONLY 

DROP TABLE dashboards;

-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE kbcontents_audit
    ADD INDEX idx_kbcontents_parent_id (parent_id);
-- //END SUGARCRM flav=pro ONLY 

ALTER TABLE leads_audit
    ADD INDEX idx_leads_parent_id (parent_id);

ALTER TABLE opportunities_audit
    ADD INDEX idx_opportunities_parent_id (parent_id);

ALTER TABLE outbound_email
    ALTER mail_smtpport SET DEFAULT 0;

-- //BEGIN SUGARCRM flav=pro ONLY 

ALTER TABLE products_audit
    ADD INDEX idx_products_parent_id (parent_id);

ALTER TABLE project_task_audit
    ADD INDEX idx_project_task_parent_id (parent_id);

ALTER TABLE quotes_audit
    ADD INDEX idx_quotes_parent_id (parent_id);

ALTER TABLE report_cache
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (id, assigned_user_id);

-- //END SUGARCRM flav=pro ONLY 
    
ALTER TABLE users DROP user_preferences;    

ALTER TABLE accounts_audit ADD INDEX idx_accounts_primary (id);
ALTER TABLE bugs_audit ADD INDEX idx_bugs_primary (id);
ALTER TABLE campaigns_audit ADD INDEX idx_campaigns_primary (id);
ALTER TABLE cases_audit ADD INDEX idx_cases_primary (id);
ALTER TABLE contacts_audit ADD INDEX idx_contacts_primary (id);
ALTER TABLE leads_audit ADD INDEX idx_leads_primary (id);
ALTER TABLE opportunities_audit ADD INDEX idx_opportunities_primary (id);
ALTER TABLE project_task_audit ADD INDEX idx_project_task_primary (id);


-- //BEGIN SUGARCRM flav=pro ONLY
ALTER TABLE contracts_audit ADD INDEX idx_contracts_primary (id);
ALTER TABLE kbcontents_audit ADD INDEX idx_kbcontents_primary (id);
ALTER TABLE products_audit ADD INDEX idx_products_primary (id);
ALTER TABLE quotes_audit ADD INDEX idx_quotes_primary (id);

ALTER TABLE team_sets_teams
    ADD PRIMARY KEY (id);
-- //END SUGARCRM flav=pro ONLY
