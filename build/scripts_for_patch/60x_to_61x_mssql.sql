-- //BEGIN SUGARCRM flav=pro ONLY 
ALTER TABLE [products] DROP team_id;
ALTER TABLE [products] DROP team_set_id;
-- //END SUGARCRM flav=pro ONLY 

CREATE NONCLUSTERED INDEX idx_accounts_primary on accounts_audit (id);
CREATE NONCLUSTERED INDEX idx_bugs_primary on bugs_audit (id);
CREATE NONCLUSTERED INDEX idx_campaigns_primary on campaigns_audit (id);
CREATE NONCLUSTERED INDEX idx_cases_primary on cases_audit (id);
CREATE NONCLUSTERED INDEX idx_contacts_primary on contacts_audit (id);
CREATE NONCLUSTERED INDEX idx_leads_primary on leads_audit (id);
CREATE NONCLUSTERED INDEX idx_opportunities_primary on opportunities_audit (id);
CREATE NONCLUSTERED INDEX idx_project_task_primary on project_task_audit (id);

