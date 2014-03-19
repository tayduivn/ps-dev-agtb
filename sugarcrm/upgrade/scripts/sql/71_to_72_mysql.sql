alter table `dashboards` change `view` `view_name` varchar(100) NULL;
alter table `weblogichooks` change `module_name` `webhook_target_module` varchar(100) NULL;
