-- //FILE SUGARCRM flav=ent ONLY 

CREATE INDEX kbdoc_body_fts_index ON kbcontents(kbdocument_body) INDEXTYPE IS CTXSYS.CONTEXT  parameters ('sync (on commit)');


INSERT INTO kbtags (id,tag_name) SELECT 'FAQs','FAQs' FROM dual WHERE NOT EXISTS (SELECT * FROM kbtags WHERE id='FAQs');
