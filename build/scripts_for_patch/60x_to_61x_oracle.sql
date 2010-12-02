create index idx_accounts_primary on accounts_audit (id);
create index idx_bugs_primary on bugs_audit (id);
create index idx_campaigns_primary on campaigns_audit (id);
create index idx_cases_primary on cases_audit (id);
create index idx_contacts_primary on contacts_audit (id);
create index idx_leads_primary on leads_audit (id);
create index idx_opportunities_primary on opportunities_audit (id);
create index idx_project_task_primary on project_task_audit (id);
create index idx_contracts_primary on contracts_audit (id);
create index idx_kbcontents_primary on kbcontents_audit (id);
create index idx_products_primary on products_audit (id);
create index idx_quotes_primary on quotes_audit (id);

ALTER TABLE team_sets_teams ADD PRIMARY KEY (id);
