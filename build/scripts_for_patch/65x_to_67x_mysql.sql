ALTER TABLE forecast_worksheets ADD INDEX idx_forecast_worksheets_tmst_id (team_set_id),  ADD INDEX idx_worksheets_parent (parent_id,parent_type),  ADD INDEX idx_worksheets_assigned_del (deleted,assigned_user_id), ADD INDEX idx_worksheets_assigned_del_time_draft (assigned_user_id,date_closed_timestamp,draft,deleted);

ALTER TABLE forecast_worksheets ADD PRIMARY KEY(id);

ALTER TABLE forecast_manager_worksheets   ADD INDEX idx_forecast_manager_worksheets_tmst_id (team_set_id),  ADD INDEX idx_manager_worksheets_user_timestamp_assigned_user (assigned_user_id,user_id,timeperiod_id,draft,deleted);

ALTER TABLE forecast_manager_worksheets ADD PRIMARY KEY(id);

ALTER TABLE worksheet   ADD INDEX idx_worksheet_user_id (user_id),  ADD INDEX idx_worksheet_rel_id_del (related_id,user_id,deleted,version,revision);