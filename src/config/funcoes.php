<?php

use Defuse\Crypto\Key;

/*
Autor: Henrique Felix
Data: 24/10/2021
Objetivo: Verificar se a string foi preenchida
*/
function checkValue($str) {
    if ($str == null || trim((string)$str) == "" || str_replace(" ", "", $str) == "") {
        return false;
    }

    return true;
}


/*
Autor: Henrique Felix
Data: 24/10/2021
Objetivo: Retornar os filtros utilizado no pagination.js
*/
function getFilterPagination($getRequest) {
    $arrFiltro = array();

    if ($getRequest != null) {
        foreach ($getRequest as $key => $value) {
            if ($key != null 
                && trim(strtolower($key)) != "metodo" 
                && trim(strtolower($key)) != "_" 
                && trim(strtolower($key)) != "maximum"
                && trim(strtolower($key)) != "page") {
                if (trim(strtolower($key)) != "query") {
                    $arrFiltro[$key] = $value;
                } else {
                    $arrFiltro['search'] = $value;
                }
            }
        }
    }

    return $arrFiltro;
}


/*
Autor: Henrique Felix
Data: 25/10/2021
Objetivo: Chave de criptografia
*/
function encryptionKey() {
    $keyAscii = trim(file_get_contents("../config/secret-key.txt"));
    return $keyAscii;
}


/*
Autor: Henrique Felix
Data: 25/10/2021
Objetivo: Pegar a chave da criptografia
*/
function loadEncryptionKeyFromConfig() {
    return Key::loadFromAsciiSafeString(encryptionKey());
}


?>