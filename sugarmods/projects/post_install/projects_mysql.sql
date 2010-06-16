-- //FILE SUGARCRM flav=pro ONLY 
-- project
ALTER TABLE `project` ADD COLUMN `estimated_start_date` date NOT NULL AFTER `deleted`;
ALTER TABLE `project` ADD COLUMN `status` varchar(255) NULL AFTER `estimated_start_date`;
ALTER TABLE `project` ADD COLUMN `priority` varchar(255) NULL AFTER `status`;
ALTER TABLE `project` ADD COLUMN `is_template` tinyint(1) NULL default '0' AFTER `priority`;
ALTER TABLE `project` ADD COLUMN `estimated_end_date` date NOT NULL AFTER `is_template`;
UPDATE `project` SET `status` = 'Draft';
UPDATE `project` SET `priority` = 'Medium';

-- project_task
ALTER TABLE `project_task` ADD COLUMN `project_id` char(36) NULL AFTER `date_modified`;
UPDATE `project_task` SET `project_id` = `parent_id`;
ALTER TABLE `project_task` ADD COLUMN `project_task_id` int(11) AFTER `project_id`;
ALTER TABLE `project_task` ADD COLUMN `resource_id` text NULL AFTER `description`;
ALTER TABLE `project_task` ADD COLUMN `predecessors` text NULL AFTER `resource_id`;

ALTER TABLE `project_task` ADD COLUMN `time_start_backed` time NULL AFTER `deleted`;
UPDATE `project_task` SET `time_start_backed` = `time_start`;
ALTER TABLE `project_task` CHANGE COLUMN `time_start` `time_start` int(11) NULL;

ALTER TABLE `project_task` ADD COLUMN `time_finish` int(11) NULL AFTER `time_start`;
UPDATE `project_task` SET `time_finish` = `time_due`;
ALTER TABLE `project_task` ADD COLUMN `date_finish` date NULL AFTER `time_finish`;
UPDATE `project_task` SET `date_finish` = `date_due`;

ALTER TABLE `project_task` ADD COLUMN `duration` int(11) AFTER `date_finish`;
ALTER TABLE `project_task` ADD COLUMN `duration_unit` text AFTER `duration`;
UPDATE `project_task` SET `duration_unit` = 'Days';

ALTER TABLE `project_task` ADD COLUMN `actual_duration` int(11) NULL AFTER `duration_unit`;
UPDATE `project_task` SET `actual_duration` = CEILING(actual_effort / 8);
ALTER TABLE `project_task` CHANGE COLUMN `percent_complete` `percent_complete` int(11) NULL;
UPDATE `project_task` SET `percent_complete` = 0 WHERE `percent_complete` IS NULL;
ALTER TABLE `project_task` ADD COLUMN `parent_task_id` int(11) NULL AFTER `percent_complete`;

UPDATE `project_task` set `milestone_flag` = '1' WHERE `milestone_flag` = 'on';
UPDATE `project_task` set `milestone_flag` = '0' WHERE `milestone_flag` = 'off';

UPDATE `project_task` SET `project_task`.`team_id` = (SELECT `project`.`team_id` FROM `project` WHERE `project`.`id` = `project_task`.`project_id`);