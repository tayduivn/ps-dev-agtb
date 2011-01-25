ALTER TABLE `email_addr_bean_rel` MODIFY COLUMN `bean_module` varchar(100) NULL;

ALTER TABLE `emails_beans` MODIFY COLUMN `bean_module` varchar(100) NULL;

ALTER TABLE meetings ALTER COLUMN status SET DEFAULT 'Planned';
ALTER TABLE calls ALTER COLUMN status SET DEFAULT 'Planned';
ALTER TABLE tasks ALTER COLUMN status SET DEFAULT 'Not Started';