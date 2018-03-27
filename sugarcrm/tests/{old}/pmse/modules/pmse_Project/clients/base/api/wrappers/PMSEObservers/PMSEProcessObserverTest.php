<?php
//FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class PMSEProcessObserverTest extends TestCase
{
    public function testUpdate()
    {
        $processObserverMock = $this->getMockBuilder('PMSEProcessObserver')
                ->disableOriginalConstructor()
                ->setMethods(array('processRelatedDependencies', 'getRelatedDependencyBean'))
                ->getMock();
        
        $relatedDependencyMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();
        
        $relatedDependencyMock->pro_module = 'Leads';
        
        $processObserverMock->expects($this->any())
                ->method('getRelatedDependencyBean')
                ->will($this->returnValue($relatedDependencyMock));
        
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('info', 'debug', 'error'))
                ->getMock();
        
        $subjectMock = $this->getMockBuilder('PMSESubject')
                ->disableOriginalConstructor()
                ->setMethods(array('getEvent', 'getEventDefinition', 'getProcessDefinition'))
                ->getMock();
        
        $processDefMock = $this->getMockBuilder('pmse_BpmProcessDefinition')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        
        $processDefMock->fetched_row = array(
            'id' => 'pro01',
            'prj_id' => 'prj01',
            'pro_status' => 'ACTIVE',
            'pro_module' => 'Leads',
            'pro_locked_variables' => '[]',
            'pro_terminate_variables' => '[]'
        );
        
        $subjectMock->expects($this->once())
                ->method('getProcessDefinition')
                ->will($this->returnValue($processDefMock));
        
        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('select', 'from', 'where', 'queryAnd', 'execute', 'addRaw'))
                ->getMock();
        
        $sugarQueryMock->expects($this->any())
                ->method('where')
                ->will($this->returnSelf());
        $sugarQueryMock->expects($this->any())
                ->method('queryAnd')
                ->will($this->returnSelf());
        $sugarQueryMock->expects($this->any())
                ->method('addRaw')
                ->will($this->returnSelf());
                

        $sugarQueryMock->expects($this->once())
                ->method('execute')
                ->will($this->returnValue(array(
                    array('id' => 'rel01'),
                    array('id' => 'rel02'),
                    array('id' => 'rel03'),
                )));
        
                
        
        $processObserverMock->setSugarQuery($sugarQueryMock);
        $processObserverMock->setLogger($loggerMock);
        
        $processObserverMock->update($subjectMock);
    }
    //put your tests code here
}
