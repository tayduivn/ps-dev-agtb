ALTER TABLE meetings ADD DEFAULT ('Planned') FOR status;

ALTER TABLE calls ADD DEFAULT ('Planned') FOR status;

ALTER TABLE tasks ADD DEFAULT ('Not Started') For status;