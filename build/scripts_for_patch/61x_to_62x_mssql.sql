ALTER TABLE email_addr_bean_rel ALTER COLUMN bean_module varchar(100) NULL;

ALTER TABLE emails_beans ALTER COLUMN bean_module varchar(100) NULL;

ALTER TABLE meetings ADD DEFAULT ('Planned') FOR status;

ALTER TABLE calls ADD DEFAULT ('Planned') FOR status;

ALTER TABLE tasks ADD DEFAULT ('Not Started') For status;