-- //FILE SUGARCRM flav=pro ONLY 
	--if kbcontents table does not exist, then remove the catalog table
	If  Exists(select name from sys.databases where name = 'kbcontents')
		BEGIN
			select name from sys.databases where name = 'kbcontents'
		END
	ELSE
	 	BEGIN
		      DROP FULLTEXT CATALOG sugar_fts_catalog
	 	END;

-- knowledge base
