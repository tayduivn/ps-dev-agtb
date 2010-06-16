
      EXEC sp_fulltext_database 'enable';

	  --If Catalog Name exists, then tables were not dropped, no need to recreate fts meta
	  If  Exists(select * from sys.fulltext_catalogs where name ='sugar_fts_catalog')
		BEGIN
			select * from sys.fulltext_catalogs where name ='sugar_fts_catalog'
		END
	  ELSE
	 	BEGIN
		      CREATE FULLTEXT CATALOG sugar_fts_catalog
		      ALTER TABLE kbcontents ADD kb_index INT NOT NULL IDENTITY
		
		      CREATE UNIQUE Index fts_unique_idx on kbcontents(kb_index)
		      CREATE FULLTEXT INDEX ON kbcontents
		            (
		                        kbdocument_body
		                        Language 1033
		            )
		            KEY INDEX fts_unique_idx ON sugar_fts_catalog
		            WITH CHANGE_TRACKING AUTO
		
		      
		      INSERT INTO kbtags (id,tag_name) values('FAQs','FAQs')      

	 	END;



