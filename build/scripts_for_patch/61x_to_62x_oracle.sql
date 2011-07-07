ALTER TABLE email_addr_bean_rel ADD (bean_module2 varchar2(100));

UPDATE email_addr_bean_rel SET bean_module2 = bean_module;

ALTER TABLE email_addr_bean_rel DROP COLUMN bean_module;

ALTER TABLE email_addr_bean_rel RENAME COLUMN bean_module2 TO bean_module;

ALTER TABLE emails_beans ADD (bean_module2 varchar2(100));

UPDATE emails_beans SET bean_module2 = bean_module;

ALTER TABLE emails_beans DROP COLUMN bean_module;

ALTER TABLE emails_beans RENAME COLUMN bean_module2 TO bean_module;

ALTER TABLE meetings MODIFY status DEFAULT 'Planned';

ALTER TABLE calls MODIFY status DEFAULT 'Planned';

ALTER TABLE tasks MODIFY status DEFAULT 'Not Planned';