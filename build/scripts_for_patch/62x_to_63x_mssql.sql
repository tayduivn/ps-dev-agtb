DROP INDEX idx_folders_rel_folder_id on folders_rel;
CREATE NONCLUSTERED INDEX idx_fr_id_deleted_poly on folders_rel (folder_id, deleted, polymorphic_id);

CREATE NONCLUSTERED INDEX idx_bugs_assigned_user on bugs (assigned_user_id);