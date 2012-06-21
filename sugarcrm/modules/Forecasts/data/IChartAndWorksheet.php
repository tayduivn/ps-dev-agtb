<?php

interface IChartAndWorksheet
{

/**
 * @abstract
 *
 * @param $id String Optional string id in the event there may be multiple worksheet data definitions
 * @return mixed
 */
public function getChartDefinition($id='');

//public function saveChartDefinition(User $user, IChartAndWorksheet $definition);

/**
 * @abstract
 *
 * @param $id String Optional string id in the event there may be multiple worksheet data definitions
 * @return mixed
 */
public function getWorksheetDefinition($id='');

//public function saveWorksheetDefinition(User $user, IChartAndWorksheet $definition);

}