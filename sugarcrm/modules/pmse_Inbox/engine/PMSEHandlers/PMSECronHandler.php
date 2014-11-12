<?php


class PMSECronHandler
{
    protected  $request;
    protected  $preProcessor;
    
    public function __construct()
    {
        $this->request = new PMSERequest();
        $this->request->setType('cron');
        $this->request->setElements(array());

        $this->preProcessor = PMSEPreProcessor::getInstance();
    }
}
