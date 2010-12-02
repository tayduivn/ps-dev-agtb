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
