<?php 

    const DBDRIVE = "mysql";
	const DBHOST = "localhost";
	const DBNAME = "projeto_audax";
	const DBUSER = "root";
	const DBPASS = "123456";
	const DBPORT = "3306";

    function DBConnectMy() {
        $connPdo = new \PDO(DBDRIVE.': host='.DBHOST.'; dbname='.DBNAME, DBUSER, DBPASS);
        return $connPdo;
    }

    function DBClose($connPdo) {
        $connPdo = null;
    }
    
?>