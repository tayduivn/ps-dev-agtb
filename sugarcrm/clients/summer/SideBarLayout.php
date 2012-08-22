<?php

class SideBarLayout
{


    function __construct(){

    }

    protected $containers = array('top' => array(), 'bottom' => array(), 'main' => array(), 'side' => array());
    protected $spans = array('main' => 7, 'side' => 5);
    protected $layout = array(
        'type' => 'simple',
        'components' =>
        array(

        ),
    );


    function push($section, $comonent, $index = -1)
    {
        $this->containers[$section] = $this->insert($this->containers[$section], $comonent, $index);

    }

    function setSectionSpan($section, $span)
    {
        $this->spans[$section] = $span;
    }

    protected function getMainLayout()
    {
        $components = array();
        if (empty($this->containers['main']) && empty($this->containers['side'])) return array();
        if (!empty($this->containers['main'])) {
            $components[] = array(
                'layout' =>
                array(
                    'type' => 'simple',
                    'span' => $this->spans['main'],
                    'components' => $this->containers['main'],
                ),
            );
        }
        if (!empty($this->containers['side'])) {
            $components[] = array(
                'layout' =>
                array(
                    'type' => 'simple',
                    'span' => $this->spans['side'],
                    'components' => $this->containers['side'],
                )
            );
        }
        return array(
            'layout' =>
            array(
                'type' => 'fluid',
                'components' => $components
            ),
        );

    }

    function getLayout()

    {
        if (empty($this->containers['side'])) {
            $this->spans['main'] = 12;

        }
        $this->layout['components'] = array_merge($this->containers['top'], array($this->getMainLayout()), $this->containers['bottom']);

        return $this->layout;
    }

    protected function insert($components, $component, $index = -1)
    {
        if ($index == -1) {
            $components[] = $component;
            return $components;
        } else {
            if (isset($components[$index])) {
                $start = array_slice($components, 0, $index);
                $end = array_slice($components, $index);
                return array_merge($start, array($component), $end);

            }
            $components[$index] = $component;
            return $components;
        }
    }


}
