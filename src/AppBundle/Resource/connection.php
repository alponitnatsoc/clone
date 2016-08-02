<?php 
namespace AppBundle\Resource;
function connect(){
        $link =  mysql_connect('localhost', 'root', '');
        if (!$link) {
            return "could not connect: ".mysql_errno();
        }
        $database= mysql_select_db('test', $link);
        if (!$database) {
            return "could not connect: ".mysql_errno();
        }
        return "Connections succesfull!!!!! :)";
    }
?>