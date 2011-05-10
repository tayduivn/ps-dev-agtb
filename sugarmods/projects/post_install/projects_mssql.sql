-- //FILE SUGARCRM flav=pro ONLY 
-- project
ALTER TABLE [project] ADD [estimated_start_date] [datetime] CONSTRAINT [DF_estimated_start_date] DEFAULT getdate() NOT NULL;
ALTER TABLE [project] ADD [status] [varchar] (255);
UPDATE [project] SET [status] = "Draft";
ALTER TABLE [project] ADD [priority] [varchar] (255);
UPDATE [project] SET [priority] = "Medium";
ALTER TABLE [project] ADD [is_template] [bit];
UPDATE [project] SET [is_template] = 0;
ALTER TABLE [project] ADD [estimated_end_date] [datetime] CONSTRAINT [DF_estimated_end_date] DEFAULT getdate() NOT NULL;

-- project_task
ALTER TABLE [project_task] ADD [project_id] [varchar] (36);
UPDATE [project_task] SET [project_id] = [parent_id];
ALTER TABLE [project_task] ADD [project_task_id] [int];
ALTER TABLE [project_task] ADD [resource_id] [text];
ALTER TABLE [project_task] ADD [predecessors] [text];

ALTER TABLE [project_task] ADD [time_start_backed] datetime;
UPDATE [project_task] SET [time_start_backed] = [time_start];
ALTER TABLE [project_task] DROP COLUMN [time_start];
ALTER TABLE [project_task] ADD [time_start] [int];

ALTER TABLE [project_task] ADD [time_finish] [int];
UPDATE [project_task] SET [time_finish] = CONVERT([int], DATEPART(hh, [time_due]));
ALTER TABLE [project_task] ADD [date_finish] [datetime];
UPDATE [project_task] SET [date_finish] = [date_due];

ALTER TABLE [project_task] ADD [duration] [int];
ALTER TABLE [project_task] ADD [duration_unit] [text];
UPDATE [project_task] SET [duration_unit] = "Days";

ALTER TABLE [project_task] ADD [actual_duration] [int];
UPDATE [project_task] SET [actual_duration]=CEILING(actual_effort/8);
ALTER TABLE [project_task] ALTER COLUMN [percent_complete] [int];
UPDATE [project_task] SET [percent_complete]=0 WHERE [percent_complete] IS NULL;
ALTER TABLE [project_task] ADD [parent_task_id] [int];

ALTER TABLE [project_task] ALTER COLUMN [parent_id] [varchar] (36) NULL;

UPDATE [project_task] SET [milestone_flag] = "1" WHERE [milestone_flag] LIKE "on";
UPDATE [project_task] SET [milestone_flag] = "0" WHERE [milestone_flag] LIKE "off";

UPDATE [project_task] SET [project_task].[team_id] = (SELECT [project].[team_id] FROM [project] WHERE [project].[id] = [project_task].[project_id]);