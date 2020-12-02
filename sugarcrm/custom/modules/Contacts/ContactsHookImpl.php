<?php

class ContactsHookImpl
{
    protected $countries = array (
        'Americas' =>
            array (
                1 => 'BRAZIL',
                2 => 'CANADA',
                3 => 'COLOMBIA',
                4 => 'USA',
            ),
        'Asia_Pacific' =>
            array (
                1 => 'AUSTRALIA',
                2 => 'CHINA',
                3 => 'HONG KONG',
                4 => 'INDIA',
                5 => 'INDONESIA',
                6 => 'JAPAN',
                7 => 'MALAYSIA',
                8 => 'PHILIPPINES',
                9 => 'SINGAPORE',
                10 => 'KOREA, SOUTH',
                11 => 'TAIWAN',
                12 => 'THAILAND',
            ),
        'CEE' =>
            array (
                1 => 'AUSTRIA',
                2 => 'BULGARIA',
                3 => 'CROATIA',
                4 => 'CZECH REPUBLIC',
                5 => 'POLAND',
                6 => 'ROMANIA',
                7 => 'RUSSIA',
                8 => 'SLOVAKIA',
                9 => 'TURKEY',
            ),
        'Africa' =>
            array (
                1 => 'KENYA',
                2 => 'LEBANON',
                3 => 'MOROCCO',
                4 => 'REUNION',
                5 => 'SAUDI ARABIA',
                6 => 'SOUTH AFRICA',
                7 => 'TUNISIA',
                8 => 'UNITED ARAB EMIRATES',
            ),
        'Europe' =>
            array (
                1 => 'BELGIUM',
                2 => 'FRANCE',
                3 => 'GERMANY',
                4 => 'GREECE',
                5 => 'IRELAND',
                6 => 'ITALY',
                7 => 'LUXEMBOURG',
                8 => 'NORWAY',
                9 => 'SPAIN',
                10 => 'SWEDEN',
                11 => 'SWITZERLAND',
                12 => 'UNITED KINGDOM',
                13 => 'DENMARK',
                14 => 'NETHERLANDS',
                15 => 'FINLAND',
                16 => 'PORTUGAL',
            ),
        );

    public function before_save($bean, $event, $arguments)
    {
        $this->calcMatchFields($bean, $event, $arguments);
    }

    protected function calcMatchFields($bean, $event, $arguments)
    {
        $this->calcCountryMatch($bean, $event, $arguments);
        $this->calcFunctionMatch($bean, $event, $arguments);
    }

    protected function calcCountryMatch($bean, $event, $arguments)
    {
        $result = array();

        if(strlen($bean->primary_address_country)) {
            $result[] = $bean->primary_address_country;
        }

        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_1_c, $bean->geo_mobility_region_1_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_2_c, $bean->geo_mobility_region_2_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_3_c, $bean->geo_mobility_region_3_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_4_c, $bean->geo_mobility_region_4_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_5_c, $bean->geo_mobility_region_5_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_6_c, $bean->geo_mobility_region_6_c));
        $result = array_unique($result);

        if(count($result)) {
            $bean->gtb_country_match_c = '^'.implode('^,^', $result).'^';
        }
    }

    protected function getCountriesFromMultiEnum($country_field, $region_field)
    {
        $result = explode('^,^', trim($country_field, '^')) ?? [];
        if(in_array('All', $result)) {
            unset($result[array_search('All', $result)]);
            if(in_array($region_field, array_keys($this->countries))) {
                $result = array_merge($result, $this->countries[$region_field]);
            } elseif ($region_field == 'Worldwide') {
                foreach($this->countries as $key => $region_countries) {
                    $result = array_merge($result, $region_countries);
                }
            }
        }
        return $result;
    }

    protected function calcFunctionMatch($bean, $event, $arguments)
    {
        $result = array();

        if(strlen($bean->function_c)) {
            $result[] = $bean->function_c;
        }

        $result = array_merge($result, explode('^,^', trim($bean->functional_mobility_c, '^')));
        $result = array_unique($result);

        if(count($result)) {
            $bean->gtb_function_match_c = '^'.implode('^,^', $result).'^';
        }
    }
}
