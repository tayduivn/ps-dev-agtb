<?php

class SugarOAuthDataDB
{
    public $table;

    public static $ops_table = array(
        '$lt' => '<',
        '$gt' => '>',
        '$lte' => '<=',
        '$gte' => '>=',
    );

    public static $encoded = array('authdata' => 1);

    public function __construct($table)
    {
        $this->table = $table;
    }

    protected function encode($k, $value)
    {
        if(isset(self::$encoded[$k])) {
            return base64_encode(serialize($value));
        } else {
            return $GLOBALS['db']->quote($value);
        }
    }

    protected function decode($k, $value)
    {
        if(isset(self::$encoded[$k])) {
            return unserialize(base64_decode($value));
        } else {
            return $value;
        }
    }

    protected function dataToWhere($data)
    {
        $where = array();
        foreach($data as $k => $v) {
            if(!isset(self::$encoded[$k]) && is_array($v)) {
                foreach($v as $op => $value) {
                    $op = self::$ops_table[$op];
                    if(empty($op)) {
                        return null;
                    }
                    $where[] = "c_$k $op '".$this->encode($k, $value)."'";
                }
            } else {
                $where[] = "c_$k = '".$this->encode($k, $v)."'";
            }
        }
        return $where;
    }

    public function findOne($data)
    {
        $where = join(" AND ", $this->dataToWhere($data));
        $res = $GLOBALS['db']->query("SELECT * FROM oauth_{$this->table} WHERE $where");
        $row = $GLOBALS['db']->fetchByAssoc($res);
        if(!empty($row)) {
            $resrow=array();
            foreach(array_keys($row) as $k) {
                $newk = substr($k, 2);
                $resrow[$newk] = $this->decode($newk, $row[$k]);
            }
            return $resrow;
        }
        return $row;
    }

    public function remove($data)
    {
         $where = join(" AND ", $this->dataToWhere($data));
         return $GLOBALS['db']->query("DELETE FROM oauth_{$this->table} WHERE $where");
    }

    public function update($search, $data, $options)
    {
        $found = $this->findOne($search);
        if(empty($found)) {
            if(isset($options['upsert'])) {
                return $this->insert($data);
            }
        } else {
            $cond = join(" AND ", $this->dataToWhere($search));
            $upd = join(",", $this->dataToWhere($data));
            if(empty($upd) || empty($cond)) {
                return false;
            }
            $GLOBALS['db']->query("UPDATE oauth_{$this->table} SET $upd WHERE $cond");
        }
        return true;
    }

    public function insert($data)
    {
        $values = array();
        $keys = array();
        foreach($data as $k => $v) {
             $values[] = "'".$this->encode($k, $v)."'";
             $keys[] = "c_$k";
        }
        $values = join(",", $values);
        $keys = join(",", $keys);
        return $GLOBALS['db']->query("INSERT INTO oauth_{$this->table} ($keys) VALUES ($values)");
    }

    public function ensureIndex()
    {
        // do nothing
    }
}
