<?php

namespace Vendor\Almoxarifado\model;

use JsonSerializable;

class Perfil implements JsonSerializable {
    private $codigo;
    private $descricao;
    private $ativo = 1;
    private $telas;

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    function getDescricao() {
        return $this->descricao;
    }

    function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

    function getAtivo() {
        return $this->ativo;
    }

    function setTelas($telas) {
        $this->telas = $telas;
    }
    
    function getTelas() {
        return $this->telas;
    }

    public function jsonSerialize(){
        return [
            'codigo'   => $this->getCodigo(),
            'descricao' => $this->getDescricao(),
            'telas' => $this->getTelas(),
            'ativo' => $this->getAtivo()
        ];
    }
}
