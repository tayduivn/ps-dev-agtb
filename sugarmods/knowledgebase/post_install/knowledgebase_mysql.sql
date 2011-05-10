
ALTER TABLE `kbcontents` ENGINE =`MYISAM`;
ALTER TABLE `kbcontents` ADD FULLTEXT (kbdocument_body);

INSERT INTO `kbtags` (id,tag_name) VALUES ('FAQs', 'FAQs') ON DUPLICATE KEY UPDATE id = id;

