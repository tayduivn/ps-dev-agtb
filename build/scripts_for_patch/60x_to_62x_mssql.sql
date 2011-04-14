CREATE NONCLUSTERED INDEX idx_accounts_parent_id on accounts_audit (parent_id);

CREATE NONCLUSTERED INDEX idx_bugs_parent_id on bugs_audit (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_campaigns_parent_id on campaigns_audit (parent_id);

-- //END SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_cases_parent_id on cases_audit (parent_id);

CREATE NONCLUSTERED INDEX idx_contacts_parent_id on contacts_audit (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_contracts_parent_id on contracts_audit (parent_id);

-- //END SUGARCRM flav=pro ONLY 

DROP TABLE dashboards;

-- //BEGIN SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_kbcontents_parent_id on kbcontents_audit (parent_id);

-- //END SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_leads_parent_id on leads_audit (parent_id);

CREATE NONCLUSTERED INDEX idx_opportunities_parent_id on opportunities_audit (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_products_parent_id on products_audit (parent_id);

CREATE NONCLUSTERED INDEX idx_project_task_parent_id on project_task_audit (parent_id);

CREATE NONCLUSTERED INDEX idx_quotes_parent_id on quotes_audit (parent_id);

ALTER TABLE [report_cache] DROP CONSTRAINT pk_report_cache;
ALTER TABLE [report_cache] ADD CONSTRAINT pk_report_cache PRIMARY KEY (id, assigned_user_id);

-- //END SUGARCRM flav=pro ONLY 

ALTER TABLE [users] DROP COLUMN user_preferences;

CREATE NONCLUSTERED INDEX idx_accounts_primary on accounts_audit (id);
CREATE NONCLUSTERED INDEX idx_bugs_primary on bugs_audit (id);
CREATE NONCLUSTERED INDEX idx_campaigns_primary on campaigns_audit (id);
CREATE NONCLUSTERED INDEX idx_cases_primary on cases_audit (id);
CREATE NONCLUSTERED INDEX idx_contacts_primary on contacts_audit (id);
CREATE NONCLUSTERED INDEX idx_leads_primary on leads_audit (id);
CREATE NONCLUSTERED INDEX idx_opportunities_primary on opportunities_audit (id);
CREATE NONCLUSTERED INDEX idx_project_task_primary on project_task_audit (id);

-- //BEGIN SUGARCRM flav=pro ONLY
CREATE NONCLUSTERED INDEX idx_contracts_primary on contracts_audit (id);
CREATE NONCLUSTERED INDEX idx_kbcontents_primary on kbcontents_audit (id);
CREATE NONCLUSTERED INDEX idx_products_primary on products_audit (id);
CREATE NONCLUSTERED INDEX idx_quotes_primary on quotes_audit (id);
ALTER TABLE [team_sets_teams] ADD CONSTRAINT pk_team_sets_teams PRIMARY KEY (id);
-- //END SUGARCRM flav=pro ONLY

ALTER TABLE meetings ADD DEFAULT ('Planned') FOR status;

ALTER TABLE calls ADD DEFAULT ('Planned') FOR status;

ALTER TABLE tasks ADD DEFAULT ('Not Started') For status;