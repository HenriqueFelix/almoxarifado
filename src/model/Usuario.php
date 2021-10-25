<?php

namespace Vendor\Almoxarifado\model;

use JsonSerializable;
use Vendor\Almoxarifado\model\Perfil;

class Usuario implements JsonSerializable {
    private $codigo;
    private $nome;
    private $email;
    private $cpf;
    private $senha;
    private $perfil;
    private $ativo = 1;
    private $foto;

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function setNome($nome) {
        $this->nome = $nome;
    }

    function getNome() {
        return $this->nome;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function getEmail() {
        return $this->email;
    }

    function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    function getCpf() {
        return $this->cpf;
    }

    function setSenha($senha) {
        $this->senha = $senha;
    }

    function getSenha() {
        return $this->senha;
    }

    function setPerfil(?Perfil $perfil) {
        $this->perfil = $perfil;
    }

    function getPerfil(): Perfil {
        return $this->perfil;
    }

    function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

    function getAtivo() {
        return $this->ativo;
    }

    function setFoto($foto) {
        $this->foto = $foto;
    }
    
    function getFoto() {
        return $this->foto;
    }

    public function jsonSerialize(){
        return [
            'codigo'   => $this->getCodigo(),
            'nome' => $this->getNome(),
            'email' => $this->getEmail(),
            'senha' => $this->getSenha(),
            'ativo' => $this->getAtivo(),
            'perfil' => $this->getPerfil(),
            'foto' => $this->getFoto(),
        ];
    }
}
