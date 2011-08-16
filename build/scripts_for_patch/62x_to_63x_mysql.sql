DROP INDEX idx_folders_rel_folder_id on folders_rel;
ALTER TABLE folders_rel ADD INDEX idx_fr_id_deleted_poly (folder_id, deleted, polymorphic_id);

ALTER TABLE bugs ADD INDEX idx_bugs_assigned_user (assigned_user_id);

DROP INDEX idx_target_id on campaign_log;
ALTER TABLE campaign_log ADD INDEX idx_target_deleted (target_id, deleted);


