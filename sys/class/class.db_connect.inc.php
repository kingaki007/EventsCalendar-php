<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DB_Connect{
    public $db;
    
    public function __construct($dbo=NULL) {
        if(is_object($dbo)){
            $this->db = $dbo;
        }
        else{
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            try{
                $this->db = new PDO($dsn,DB_USER,DB_PASS);
            }
            catch(Exception $e){
                die($e->getMessage());
            }
        }
    }
}