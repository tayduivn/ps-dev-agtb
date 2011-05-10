-- //FILE SUGARCRM flav=pro ONLY 
-- project
ALTER TABLE `project` DROP COLUMN `estimated_start_date`;
ALTER TABLE `project` DROP COLUMN `status`;
ALTER TABLE `project` DROP COLUMN `priority`;
ALTER TABLE `project` DROP COLUMN `is_template`;
ALTER TABLE `project` DROP COLUMN `estimated_end_date`;

-- project_task
ALTER TABLE `project_task` CHANGE COLUMN `time_start` `time_start_new` int(11);
ALTER TABLE `project_task` CHANGE COLUMN `time_start_backed` `time_start` time NULL;

UPDATE `project_task` SET `parent_id` = `project_id`;

ALTER TABLE `project_task` DROP COLUMN `project_id`;
ALTER TABLE `project_task` DROP COLUMN `project_task_id`;
ALTER TABLE `project_task` DROP COLUMN `resource_id`;
ALTER TABLE `project_task` DROP COLUMN `predecessors`;

ALTER TABLE `project_task` DROP COLUMN `time_start_new`;
ALTER TABLE `project_task` DROP COLUMN `time_finish`;
ALTER TABLE `project_task` DROP COLUMN `date_finish`;

ALTER TABLE `project_task` DROP COLUMN `duration`;
ALTER TABLE `project_task` DROP COLUMN `duration_unit`;
ALTER TABLE `project_task` DROP COLUMN `actual_duration`;
ALTER TABLE `project_task` DROP COLUMN `parent_task_id`;

UPDATE `project_task` set `milestone_flag` = 'on' WHERE `milestone_flag` = '1';
UPDATE `project_task` set `milestone_flag` = 'off' WHERE `milestone_flag` = '0';