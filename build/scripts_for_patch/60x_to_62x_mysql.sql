ALTER TABLE accounts_audit
    ADD INDEX idx_accounts_primary (id);
ALTER TABLE bugs_audit
    ADD INDEX idx_bugs_primary (id);
ALTER TABLE campaigns_audit
    ADD INDEX idx_campaigns_primary (id);
ALTER TABLE cases_audit
    ADD INDEX idx_cases_primary (id);
ALTER TABLE contacts_audit
    ADD INDEX idx_contacts_primary (id);
ALTER TABLE leads_audit
    ADD INDEX idx_leads_primary (id);
ALTER TABLE opportunities_audit
    ADD INDEX idx_opportunities_primary (id);
ALTER TABLE project_task_audit
    ADD INDEX idx_project_task_primary (id);

-- //BEGIN SUGARCRM flav=pro ONLY
ALTER TABLE contracts_audit
    ADD INDEX idx_contracts_primary (id);
ALTER TABLE kbcontents_audit
    ADD INDEX idx_kbcontents_primary (id);
ALTER TABLE products_audit
    ADD INDEX idx_products_primary (id);
ALTER TABLE quotes_audit
    ADD INDEX idx_quotes_primary (id);

ALTER TABLE team_sets_teams
    ADD PRIMARY KEY (id);
-- //END SUGARCRM flav=pro ONLY

ALTER TABLE meetings ALTER COLUMN status SET DEFAULT 'Planned';
ALTER TABLE calls ALTER COLUMN status SET DEFAULT 'Planned';
ALTER TABLE tasks ALTER COLUMN status SET DEFAULT 'Not Started';