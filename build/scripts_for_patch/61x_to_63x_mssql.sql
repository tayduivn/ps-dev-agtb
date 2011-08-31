ALTER TABLE meetings ADD DEFAULT ('Planned') FOR status;
ALTER TABLE calls ADD DEFAULT ('Planned') FOR status;
ALTER TABLE tasks ADD DEFAULT ('Not Started') For status;

DROP INDEX idx_folders_rel_folder_id on folders_rel;
CREATE NONCLUSTERED INDEX idx_fr_id_deleted_poly on folders_rel (folder_id, deleted, polymorphic_id);
CREATE NONCLUSTERED INDEX idx_bugs_assigned_user on bugs (assigned_user_id);

CREATE NONCLUSTERED INDEX idx_target_id_deleted on campaign_log (target_id, deleted);