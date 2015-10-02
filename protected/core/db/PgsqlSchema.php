<?php

class PgsqlSchema extends CPgsqlSchema
{
    use SmartColumnTypeTrait;
    
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'serial';
        $this->columnTypes['longbinary'] = 'bytea';
        $this->columnTypes['decimal'] = 'numeric (10,0)'; // Same default than MySql (not used)
    }
    
    public function createDatabase($name) {
        try {
            $this->dbConnection->createCommand("CREATE DATABASE \"$name\" ENCODING 'UTF8'")->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;        
    }



}
