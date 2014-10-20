<?php


interface PMSERunnable
{
    public function run($flowData, $bean, $externalAction, $arguments);
}
