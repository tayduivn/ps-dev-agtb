<?php
require_once('PMSEImporter.php');

/**
 * Imports a record of encrypted file.
 *
 * This class extends the class ADAMImporter to import
 * records to an encrypted file BPMEmailTemplate table.
 * @package PMSE
 * @codeCoverageIgnore
 */
class PMSEEmailTemplateImporter extends PMSEImporter
{

    public function __construct()
    {
        $this->bean = BeanFactory::getBean('pmse_Emails_Templates'); //new BpmEmailTemplate();
        $this->name = 'name';
        $this->id = 'id';
    }
}