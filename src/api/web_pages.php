<?php
    namespace Vendor\Almoxarifado\api;
        
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    header("Access-Control-Allow-Headers: *");
    
    session_start();

    require_once '../../vendor/autoload.php';
    require_once '../config/funcoes.php';

    use Defuse\Crypto\Crypto;
    //use Defuse\Crypto\Key;
    
    //$keyCrypto = Key::createNewRandomKey();
    //$key_string = $keyCrypto->saveToAsciiSafeString();
    //echo "<pre>"; var_dump($key_string); die();

    if ($_GET["metodo"] == "RedirecionarPagina") {
        $keyCrypto = loadEncryptionKeyFromConfig();

        $valido = 1;
        $msg = "";

        $url = trim((string)$_GET["url"]);
        $param = array();

        if (isset($_GET["param"])) {
            $param = $_GET["param"];
        }

        $query = "";
        foreach ($param as $key => $value) {
            if (trim((string)$key) == "editar") {
                $key = "edt";
                $crypt = "1";
            } else if (trim((string)$key) == "detalhar") {
                $key = "det";
                $crypt = "1";
            } else {
                $crypt = Crypto::Encrypt($value, $keyCrypto);
            }
            
            $query .= $key."=".$crypt."&";
        }

        if ($query != "") {
            $query = substr($query, 0, -1);
            $url = $url."?".$query;
        }

        $arrRetorno = array();
        $arrRetorno['valido']     = (int)$valido;
        $arrRetorno['mensagem']   = $msg;
        $arrRetorno['url']        = $url;

        echo json_encode($arrRetorno);
        die();
    }

?>