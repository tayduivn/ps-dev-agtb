-- From 61x to 62x --
ALTER TABLE meetings ALTER COLUMN status SET DEFAULT 'Planned';
ALTER TABLE calls ALTER COLUMN status SET DEFAULT 'Planned';
ALTER TABLE tasks ALTER COLUMN status SET DEFAULT 'Not Started';


--From 62x to 63x
DROP INDEX idx_folders_rel_folder_id on folders_rel;
ALTER TABLE folders_rel ADD INDEX idx_fr_id_deleted_poly (folder_id, deleted, polymorphic_id);

ALTER TABLE bugs ADD INDEX idx_bugs_assigned_user (assigned_user_id);