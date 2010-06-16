-- //FILE SUGARCRM flav=pro ONLY 
-- project
ALTER TABLE [project] DROP CONSTRAINT [DF_estimated_start_date];
ALTER TABLE [project] DROP COLUMN [estimated_start_date];
ALTER TABLE [project] DROP COLUMN [status];
ALTER TABLE [project] DROP COLUMN [priority];
ALTER TABLE [project] DROP COLUMN [is_template];
ALTER TABLE [project] DROP CONSTRAINT [DF_estimated_end_date];
ALTER TABLE [project] DROP COLUMN [estimated_end_date];

-- project_task
UPDATE [project_task] SET [parent_id] = [project_id];

ALTER TABLE [project_task] DROP COLUMN [project_id];
ALTER TABLE [project_task] DROP COLUMN [project_task_id];
ALTER TABLE [project_task] DROP COLUMN [resource_id];
ALTER TABLE [project_task] DROP COLUMN [predecessors];

ALTER TABLE [project_task] DROP COLUMN [time_start];
ALTER TABLE [project_task] ADD [time_start] datetime;
UPDATE [project_task] SET [time_start] = [time_start_backed];
ALTER TABLE [project_task] DROP COLUMN [time_start_backed];

ALTER TABLE [project_task] DROP COLUMN [time_finish];
ALTER TABLE [project_task] DROP COLUMN [date_finish];

ALTER TABLE [project_task] DROP COLUMN [duration];
ALTER TABLE [project_task] DROP COLUMN [duration_unit];

ALTER TABLE [project_task] DROP COLUMN [actual_duration];
ALTER TABLE [project_task] DROP COLUMN [parent_task_id];

ALTER TABLE [project_task] ALTER COLUMN [parent_id] [varchar] (36) NOT NULL;

UPDATE [project_task] SET [milestone_flag] = "on" WHERE [milestone_flag] LIKE "1";
UPDATE [project_task] SET [milestone_flag] = "off" WHERE [milestone_flag] LIKE "0";