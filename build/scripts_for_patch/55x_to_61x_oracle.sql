create index idx_accounts_parent_id on accounts_audit (parent_id);

create index idx_bugs_parent_id on bugs_audit (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 

create index idx_campaigns_parent_id on campaigns_audit (parent_id);

-- //END SUGARCRM flav=pro ONLY 

create index idx_cases_parent_id on cases_audit (parent_id);

create index idx_contacts_parent_id on contacts_audit (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 

create index idx_contracts_parent_id on contracts_audit (parent_id);

-- //END SUGARCRM flav=pro ONLY 

DROP TABLE dashboards;

ALTER TABLE INBOUND_EMAIL ADD (mailbox2 CLOB NULL);

UPDATE INBOUND_EMAIL SET mailbox2 = mailbox;

ALTER TABLE INBOUND_EMAIL DROP COLUMN mailbox;

ALTER TABLE INBOUND_EMAIL RENAME COLUMN mailbox2 TO mailbox;

-- //BEGIN SUGARCRM flav=pro ONLY 

create index idx_kbcontents_parent_id on kbcontents_audit (parent_id);

-- //END SUGARCRM flav=pro ONLY 

create index idx_leads_parent_id on leads_audit (parent_id);

create index dx_opportunities_parent_id on opportunities_audit (parent_id);

-- //BEGIN SUGARCRM flav=pro ONLY 

create index idx_products_parent_id on products_audit (parent_id);

create index dx_project_task_parent_id on project_task_audit (parent_id);

ALTER TABLE PRODUCT_TEMPLATES ADD (pricing_factor2 NUMBER(20,2));

UPDATE PRODUCT_TEMPLATES SET pricing_factor2 = CAST(pricing_factor AS NUMBER(38,3));

ALTER TABLE PRODUCT_TEMPLATES DROP COLUMN pricing_factor;

ALTER TABLE PRODUCT_TEMPLATES RENAME COLUMN pricing_factor2 TO pricing_factor;

ALTER TABLE PROJECT_TASK ADD (duration_unit2 CLOB NULL);

UPDATE PROJECT_TASK SET duration_unit2 = duration_unit;

ALTER TABLE PROJECT_TASK DROP COLUMN duration_unit;

ALTER TABLE PROJECT_TASK RENAME COLUMN duration_unit2 TO duration_unit;

alter table report_cache drop constraint report_cache_pk;

-- //END SUGARCRM flav=pro ONLY 

alter table users drop column user_preferences;
ALTER TABLE PRODUCTS DROP COLUMN team_id;
ALTER TABLE PRODUCTS DROP COLUMN team_set_id;