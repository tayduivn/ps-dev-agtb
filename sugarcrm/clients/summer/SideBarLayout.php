<?php

class SideBarLayout {

    protected $containers = array('top' => array(), 'bottom' => array(), 'main' => array(), 'side' => array());
    protected $spans = array('main' => 8, 'side' => 4);
    protected $layout = array(
        'type' => 'simple',
        'components' =>
        array(),
    );

    public function __construct() {}

    public function push($section, $component, $index = -1) {
        $this->containers[$section] = $this->insert($this->containers[$section], $component, $index);
    }

    public function setSectionSpan($section, $span) {
        $this->spans[$section] = $span;
    }

    protected function getMainLayout() {
        $components = array();

        if (empty($this->containers['main']) && empty($this->containers['side'])) {
            return array();
        }

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
                    'components' => array(array(
                        'layout' => array(
                            'css_class' => 'sidebar-pane active',
                            'components' => $this->containers['side'],
                        ),
                    )),
                    'css_class' => 'sidebar-content folded',
                )
            );
        }

        return array(
            'layout' =>
            array(
                'type' => 'fluid',
                'components' => $components,
            ),
        );
    }

    public function getLayout() {
        if (empty($this->containers['side'])) {
            $this->spans['main'] = 12;
        }

        if(empty($this->containers['side']) && empty($this->containers['top']) && empty($this->containers['bottom'])) {
            $this->layout = array(
                    'type' => 'simple',
                    'span' => $this->spans['main'],
                    'components' => $this->containers['main'],
                );

        } else {
            $this->layout['components'] = array_merge($this->containers['top'], array($this->getMainLayout()), $this->containers['bottom']);
        }
        return $this->layout;
    }

    protected function insert($components, $component, $index = -1) {
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
