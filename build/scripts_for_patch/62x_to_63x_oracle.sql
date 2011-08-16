
DROP INDEX idx_folders_rel_folder_id;

create index idx_fr_id_deleted_poly on folders_rel (folder_id, deleted, polymorphic_id);

create index idx_bugs_assigned_user on bugs (assigned_user_id);

create index idx_target_deleted on campaign_log (target_id, deleted);