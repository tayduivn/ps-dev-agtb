<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class to handle Nested Set field in studio.
 */
class TemplateNestedset extends TemplateField
{

    public function get_db_type()
    {
        return 'varchar';
    }

    /**
     * {@inheritDoc}
     */
    public function get_field_def()
    {
        $def = parent::get_field_def();
        $def['category_provider'] = $this->ext2;
        $def['module'] = $this->ext2;
        $def['id_name'] = $this->ext3;
        $def['category_root'] = $this->ext4;
        $def['dbType'] = $this->get_db_type();
        $def['quicksearch'] = 'enabled';
        $def['studio'] = 'visible';
        $def['source'] = 'non-db';
        $def['rname'] = 'name';
        return $def;
    }

    /**
     * {@inheritDoc}
     */
    public function applyVardefRules()
    {
        parent::applyVardefRules();
        $this->category_provider = $this->ext2;
        $this->module = $this->ext2;
        $this->id_name = $this->ext3;
        $this->category_root = $this->ext4;
    }

    /**
     * {@inheritDoc}
     * @see TemplateRelatedTextField::save
     */
    public function save($df)
    {
        $this->createIdName($df);
        parent::save($df);
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldMetaDataMapping()
    {
        $default = parent::getFieldMetaDataMapping();
        return array_merge(
            $default,
            array(
                'category_provider' => 'ext2',
                'id_name' => 'ext3',
                'category_root' => 'ext4',
                'module' => 'ext2',
            )
        );
    }

    /**
     * Create ID field.
     * @param DynamicField $df
     */
    protected function createIdName($df)
    {
        if (!$df->fieldExists($this->name)) {
            $id = new TemplateId();
            $id->len = 36;
            $id->label = strtoupper("LBL_{$this->name}_" . BeanFactory::getBeanName($this->ext2) . "_ID");
            $id->vname = $id->label;
            $this->saveIdLabel($id->label, $df);

            $count = 0;
            $basename = strtolower(get_singular_bean_name($this->ext2)) . '_id' ;
            $idName = $basename . '_c' ;

            while ($df->fieldExists($idName, 'id')) {
                $idName = $basename . ++$count . '_c' ;
            }
            $id->name = $idName ;
            $id->reportable = false;
            $id->save($df);

            // record the id field's name, and save
            $this->ext3 = $id->name;
            $this->id_name = $id->name;
        }
    }

    /**
     * {@inheritDoc}
     * @see TemplateRelatedTextField::delete
     */
    public function delete($df)
    {
        $fieldId = null;
        if ($df instanceof DynamicField) {
            $fieldId = $df->getFieldWidget($df->module, $this->id_name);
        } elseif ($df instanceof MBModule) {
            $fieldId = $df->getField($this->id_name);
        } else {
            LoggerManager::getLogger()->fatal('Unsupported DynamicField type');
        }

        // the field may have already been deleted
        if ($fieldId) {
            $this->deleteIdLabel($fieldId, $df);
            $fieldId->delete($df);
        }
        parent::delete($df);
    }

    /**
     * Save label for id field.
     * @see TemplateRelatedTextField::saveIdLabel
     * @param String $idLabelName
     * @param DynamicField $df
     */
    protected function saveIdLabel($idLabelName, $df)
    {
        if ($df instanceof DynamicField) {
            $module = $df->module;
        } elseif ($df instanceof MBModule) {
            $module = $df->name;
        } else {
            LoggerManager::getLogger()->fatal('Unsupported DynamicField type');
        }
        $viewPackage = isset($df->package) ? $df->package : null;

        $idLabelValue = string_format(
            $GLOBALS['mod_strings']['LBL_RELATED_FIELD_ID_NAME_LABEL'],
            array($this->label_value, $GLOBALS['app_list_strings']['moduleListSingular'][$this->ext2])
        );

        $idFieldLabelArr = array(
            "label_{$idLabelName}" => $idLabelValue
        );

        foreach (ModuleBuilder::getModuleAliases($module) as $moduleName) {
            if ($df instanceof DynamicField) {
                $parser = new ParserLabel($moduleName, $viewPackage);
                $parser->handleSave($idFieldLabelArr, $GLOBALS['current_language']);
            } elseif ($df instanceof MBModule) {
                $df->setLabel($GLOBALS ['current_language'], $idLabelName, $idLabelValue);
                $df->save();
            }
        }
    }
}
