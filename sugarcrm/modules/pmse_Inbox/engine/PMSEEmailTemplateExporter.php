<?php
require_once('PMSEExporter.php');
/**
 * Exports a record of table EmailTemplate
 *
 * This class extends the class ADAMExporter to export a record
 * from the table BPMEmailTemplate to transport it from one instance to another.
 * @package PMSE
 * @codeCoverageIgnore
 */
class PMSEEmailTemplateExporter extends  PMSEExporter {

    public function __construct()
    {
        $this->bean = BeanFactory::getBean('pmse_Emails_Templates'); //new BpmEmailTemplate();
        $this->uid = 'id';
        $this->name = 'name';
        $this->extension = 'pet';
    }
}