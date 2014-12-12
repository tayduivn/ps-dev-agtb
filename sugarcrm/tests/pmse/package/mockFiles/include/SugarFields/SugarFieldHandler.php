<?php


class SugarField 
{
    public function getEmailTemplateValue($field_value =null, $field= null, $context=null)
    {
        return '';
    }
}

/**
 * Description of SugarFieldHandler
 *
 */
class SugarFieldHandler
{
    public static function getSugarField($name)
    {
        return new SugarField();
    }
}
