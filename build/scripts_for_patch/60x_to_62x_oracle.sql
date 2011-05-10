ALTER TABLE email_addr_bean_rel ADD (bean_module2 varchar2(100));

UPDATE email_addr_bean_rel SET bean_module2 = bean_module;

ALTER TABLE email_addr_bean_rel DROP COLUMN bean_module;

ALTER TABLE email_addr_bean_rel RENAME COLUMN bean_module2 TO bean_module;

ALTER TABLE emails_beans ADD (bean_module2 varchar2(100));

UPDATE emails_beans SET bean_module2 = bean_module;

ALTER TABLE emails_beans DROP COLUMN bean_module;

ALTER TABLE emails_beans RENAME COLUMN bean_module2 TO bean_module;

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

ALTER TABLE meetings MODIFY status DEFAULT 'Planned';

ALTER TABLE calls MODIFY status DEFAULT 'Planned';

ALTER TABLE tasks MODIFY status DEFAULT 'Not Planned';