<?php
// created: 2011-02-24 14:47:10
$dictionary["ibm_RelatedContent"]["fields"]["opportunities_ibm_relatedcontent"] = array (
  'name' => 'opportunities_ibm_relatedcontent',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_relatedcontent',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_RELATEDCONTENT_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["ibm_RelatedContent"]["fields"]["opportunities_ibm_relatedcontent_name"] = array (
  'name' => 'opportunities_ibm_relatedcontent_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_OPPORTUNITIES_IBM_RELATEDCONTENT_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'opportunit7d96unities_ida',
  'link' => 'opportunities_ibm_relatedcontent',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["ibm_RelatedContent"]["fields"]["opportunit7d96unities_ida"] = array (
  'name' => 'opportunit7d96unities_ida',
  'type' => 'link',
  'relationship' => 'opportunities_ibm_relatedcontent',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_OPPORTUNITIES_IBM_RELATEDCONTENT_FROM_IBM_RELATEDCONTENT_TITLE',
);
