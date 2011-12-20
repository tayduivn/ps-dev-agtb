<?php
/**
 * Created by JetBrains PhpStorm.
 * User: admin
 * Date: 11/2/11
 * Time: 12:43 PM
 * To change this template use File | Settings | File Templates.
 */
 interface ISugarSearchEngine{
     /**
      *
      * search()
      *
      * Perform a search against the Full Text Search Engine
      * @abstract
      * @param $query
      * @param int $offset
      * @param int $limit
      * @return void
      */
     public function search($query, $offset = 0, $limit = 20);

     /**
      * connect()
      *
      * Make a connection to the Full Text Search Engine
      * @abstract
      * @param $config
      * @return void
      */
     public function connect($config);

     /**
      * flush()
      *
      * Save the data to the Full Text Search engine backend
      * @abstract
      * @return void
      */
     public function flush();

     /**
      * indexBean()
      *
      * Pass in a bean and go through the list of fields to pass to the engine
      * @abstract
      * @param $bean
      * @return void
      */
     public function indexBean($bean);
 }
