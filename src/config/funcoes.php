<?php

/*
Autor: Henrique Felix
Data: 24/10/2021
Objetivo: Verificar se existe o texto na string
*/
function str_contains($haystack, $needle) {
    return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}



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




?>