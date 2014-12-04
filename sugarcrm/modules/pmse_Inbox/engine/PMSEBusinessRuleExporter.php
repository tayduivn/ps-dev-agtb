<?php
require_once('PMSEExporter.php');

/**
 * Exports a record of table BusinessRules
 *
 * This class extends the class ADAMExporter to export a record
 * from the table BPMRuleSet to transport it from one instance to another.
 * @package PMSE
 * @codeCoverageIgnore
 */
class PMSEBusinessRuleExporter extends PMSEExporter
{
    protected $bean;
    protected $id;
    protected $uid;
    protected $name;
    protected $extension;

    public function __construct()
    {
        $this->bean = BeanFactory::getBean('pmse_Business_Rules'); //new BpmRuleSet();
        $this->uid = 'id';
        $this->name = 'name';
        $this->extension = 'pbr';
    }

}