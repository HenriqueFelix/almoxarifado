<?php    
    namespace Vendor\Almoxarifado\controller;

use Vendor\Almoxarifado\model\Perfil;
use Vendor\Almoxarifado\model\Usuario;
    
    class UsuarioController {
        public function login($ConexaoMy, $login, $senha): Usuario {
            $usuario = new Usuario();

            $SQL = "SELECT u.*, 
                        p.descricao AS perfil_descricao, 
                        p.ativo AS perfil_ativo 
                    FROM usuario u 
                        LEFT JOIN perfil p ON p.codigo = u.perfil 
                    WHERE u.perfil IS NOT NULL 
                        AND u.perfil > 0 
                        AND (
                            u.email = :login 
                            OR (
                                u.cpf IS NOT NULL 
                                AND u.cpf != '' 
                                AND u.cpf = :login 
                            )
                        ) 
                        AND u.senha = :senha 
                        AND u.ativo = '1' 
                    LIMIT 1; ";

            $stmt = $ConexaoMy->prepare($SQL);
            $stmt->bindValue(':login', $login);
            $stmt->bindValue(':senha', $senha);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $Aux = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ((int)$Aux['perfil_ativo'] != 1) {
                        throw new \Exception("Seu perfil encontra-se inativo.");
                    }

                    $usuario->setCodigo((int)$Aux['codigo']);
                    $usuario->setNome(trim((string)$Aux['nome']));
                    $usuario->setEmail(trim((string)$Aux['email']));
                    $usuario->setCpf(trim((string)$Aux['cpf']));
                    $usuario->setFoto("");
                    $usuario->setAtivo(1);

                    $perfil = new Perfil();
                    $perfil->setCodigo((int)$Aux['perfil']);
                    $perfil->setDescricao(trim((string)$Aux['perfil_descricao']));
                    $perfil->setAtivo(1);
                    
                    $usuario->setPerfil($perfil);

                    $perfil->setTelas($this->verificarPerfil($ConexaoMy, $usuario));
                } else {
                    throw new \Exception("Login ou senha inválido(s).");
                }
            } else {
                throw new \Exception("Ops! Falha ao realizar login.");
            }

            return $usuario;
		}

        public function verificarPerfil($ConexaoMy, ?Usuario $usuario) {
            $telas = array();
            $menu = array();
            $perfilTelas = array();

            $codigoPerfil = $usuario->getPerfil()->getCodigo();

            if ($codigoPerfil <= 0) {
                throw new \Exception("Ops! Perfil não identificado.");
            } else {
                $SQL = "SELECT * 
                FROM perfil 
                WHERE codigo = :codPerfil 
                    AND ativo = '1'; ";

                $stmt = $ConexaoMy->prepare($SQL);
                $stmt->bindValue(':codPerfil', $codigoPerfil);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        $SQL = "SELECT t.* 
                                FROM perfil_telas pt
                                    LEFT JOIN sistema_telas t ON t.codigo = pt.tela 
                                WHERE pt.perfil = :codPerfil 
                                    AND t.menu > 0 
                                    AND t.ativo = '1' 
                                ORDER BY t.ordem ASC; ";
                        
                        $stmt = $ConexaoMy->prepare($SQL);
                        $stmt->bindValue(':codPerfil', $codigoPerfil);

                        if ($stmt->execute()) {
                            if ($stmt->rowCount() > 0) {
                                $codigosMenus = "";

                                $resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                for ($t=0; $t < count($resultQuery); $t++) {
                                    $Aux = $resultQuery[$t];

                                    $perfilTelas[$t]['codigo']    = (int)$Aux['codigo'];
                                    $perfilTelas[$t]['menu']      = (int)$Aux['menu'];
                                    $perfilTelas[$t]['titulo']    = trim($Aux['titulo']);
                                    $perfilTelas[$t]['icone']     = trim($Aux['icone']);
                                    $perfilTelas[$t]['diretorio'] = trim($Aux['dir']);

                                    $codigosMenus .= (int)$Aux['menu'].",";
                                }
                                
                                if ($codigosMenus != "") {
                                    $codigosMenus = substr($codigosMenus, 0, -1);
                                }

                                $SQL = "SELECT m.* 
                                        FROM sistema_menu m 
                                        WHERE m.codigo IN(".$codigosMenus.") 
                                            AND m.ativo = '1' 
                                        ORDER BY m.ordem ASC; ";
                                
                                $stmt = $ConexaoMy->prepare($SQL);

                                if ($stmt->execute()) {
                                    if ($stmt->rowCount() > 0) {
                                        $resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                        for ($t=0; $t < count($resultQuery); $t++) { 
                                            $Aux = $resultQuery[$t];

                                            $menu[$t]['codigo']    = (int)$Aux['codigo'];
                                            $menu[$t]['titulo']    = trim($Aux['titulo']);
                                            $menu[$t]['icone']     = trim($Aux['icone']);
                                            $menu[$t]['sub_menu']  = (int)$Aux['sub_menu'];
                                            $menu[$t]['paginas']   = array();
                                        }
                                    } else {
                                        throw new \Exception("Ops! Menu de telas inexistente para seu perfil.");
                                    }
                                } else {
                                    throw new \Exception("Ops! Erro ao validar menu de telas do seu perfil.");
                                }

                                foreach ($menu as $keyMenu => $valueMenu) {
                                    foreach ($perfilTelas as $keyTela => $valueTela) {
                                        if ($valueMenu['codigo'] == $valueTela['menu']) {
                                            array_push($menu[$keyMenu]['paginas'], $valueTela);
                                        }
                                    }
                                }

                                $telas['menu'] = $menu;
                                $telas['tela'] = $perfilTelas;
                            } else {
                                throw new \Exception("Ops! Telas não disponível para seu perfil.");
                            }
                        } else {
                            throw new \Exception("Ops! Erro ao verificar telas do seu perfil.");
                        }
                    } else {
                        throw new \Exception("Ops! Perfil do usuário não foi identificado.");
                    }
                } else {
                    throw new \Exception("Ops! Erro ao consultar perfil do usuário.");
                }
            }

            return $telas;
        }

        public function consultarUsuarios($ConexaoMy, ?Usuario $usuarioLogado, $query, ?Usuario $usuarioFiltro, $pagina, $itemsPorPagina) {
            $arrRetorno = array();

            $filtroSQL = "";

            if ((int)$pagina < 1) {
                $pagina = 1;
            }

            if ($itemsPorPagina <= 0) {
                $itemsPorPagina = 15;
            }

            if ($query != null) {
                $query = trim($query);
            } else {
                $query = "";
            }

            if ($query != "") {
                $filtroSQL = " AND (
                                    u.nome LIKE :queryNome 
                                    OR 
                                    u.cpf LIKE :queryCPF 
                                    OR 
                                    u.email LIKE :queryEmail 
                                ) ";
            }

            if ($usuarioFiltro != null) {
                if ($usuarioFiltro->getCodigo() > 0) {
                    $filtroSQL .= " AND u.codigo = :filtroCodigo ";
                }

                if (checkValue($usuarioFiltro->getNome())) {
                    $filtroSQL .= " AND u.nome LIKE :filtroNome ";
                }

                if (checkValue($usuarioFiltro->getEmail())) {
                    $filtroSQL .= " AND u.email LIKE :filtroEmail ";
                }

                if (checkValue($usuarioFiltro->getCpf())) {
                    $filtroSQL .= " AND u.cpf = :filtroCPF ";
                }

                if ($usuarioFiltro->getAtivo() >= 0) {
                    $filtroSQL .= " AND u.ativo = :filtroAtivo ";
                }

                if ($usuarioFiltro->getPerfil() != null && $usuarioFiltro->getPerfil()->getCodigo() != null) {
                    $filtroSQL .= " AND u.perfil IN(".implode(",", $usuarioFiltro->getPerfil()->getCodigo()).") ";
                }
            } else {
                $usuarioFiltro = new Usuario();
            }

            $offset = ((int)$pagina - 1) * (int)$itemsPorPagina;

            $SQL = "SELECT u.codigo, 
                        u.nome, 
                        u.email, 
                        u.ativo, 
                        u.perfil, 
                        p.descricao AS perfil_descricao, 
                        p.ativo AS perfil_ativo 
                    FROM usuario u 
                        LEFT JOIN perfil p ON p.codigo = u.perfil 
                    WHERE 1 = 1 
                        ".$filtroSQL." 
                    ORDER BY u.nome ASC 
                    LIMIT ".(int)$offset.", ".(int)$itemsPorPagina."; ";

            $stmt = $ConexaoMy->prepare($SQL);

            if (str_contains($SQL, ":queryNome")) {
                $stmt->bindValue(':queryNome', '%'.$query.'%');
            }

            if (str_contains($SQL, ":queryCPF")) {
                $stmt->bindValue(':queryCPF', $query.'%');
            }

            if (str_contains($SQL, ":queryEmail")) {
                $stmt->bindValue(':queryEmail', $query.'%');
            }

            if (str_contains($SQL, ":filtroCodigo")) {
                $stmt->bindValue(':filtroCodigo', $usuarioFiltro->getCodigo());
            }
            
            if (str_contains($SQL, ":filtroNome")) {
                $stmt->bindValue(':filtroNome', '%'.$usuarioFiltro->getNome().'%');
            }
            
            if (str_contains($SQL, ":filtroEmail")) {
                $stmt->bindValue(':filtroEmail', $usuarioFiltro->getEmail().'%');
            }

            if (str_contains($SQL, ":filtroCPF")) {
                $stmt->bindValue(':filtroCPF', $usuarioFiltro->getCpf());
            }

            if (str_contains($SQL, ":filtroAtivo")) {
                $stmt->bindValue(':filtroAtivo', $usuarioFiltro->getAtivo());
            }

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $usuarios = array();

                    $resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    for ($i=0; $i < count($resultQuery); $i++) {
                        $Aux = $resultQuery[$i];

                        $usu = new Usuario();
                        $usu->setCodigo((int)$Aux['codigo']);
                        $usu->setNome(trim((string)$Aux['nome']));
                        $usu->setEmail(trim((string)$Aux['email']));
                        $usu->setAtivo(trim((int)$Aux['ativo']));
                        
                        $perfil = new Perfil();
                        $perfil->setCodigo((int)$Aux['perfil']);
                        $perfil->setDescricao(trim((string)$Aux['perfil_descricao']));
                        $perfil->setAtivo((int)$Aux['perfil_ativo']);
                        
                        $usu->setPerfil($perfil);

                        $usuarios[$i] = $usu;
                    }

                    //QUANTIDADE TOTAL
                    $totalRegistros = $i;

                    $SQL = "SELECT u.codigo 
                            FROM usuario u 
                            WHERE 1 = 1 
                                ".$filtroSQL."; ";

                    $stmt = $ConexaoMy->prepare($SQL);

                    if (str_contains($SQL, ":queryNome")) {
                        $stmt->bindValue(':queryNome', '%'.$query.'%');
                    }
        
                    if (str_contains($SQL, ":queryCPF")) {
                        $stmt->bindValue(':queryCPF', $query.'%');
                    }
        
                    if (str_contains($SQL, ":queryEmail")) {
                        $stmt->bindValue(':queryEmail', $query.'%');
                    }
        
                    if (str_contains($SQL, ":filtroCodigo")) {
                        $stmt->bindValue(':filtroCodigo', $usuarioFiltro->getCodigo());
                    }
                    
                    if (str_contains($SQL, ":filtroNome")) {
                        $stmt->bindValue(':filtroNome', '%'.$usuarioFiltro->getNome().'%');
                    }

                    if (str_contains($SQL, ":filtroEmail")) {
                        $stmt->bindValue(':filtroEmail', $usuarioFiltro->getEmail().'%');
                    }
        
                    if (str_contains($SQL, ":filtroCPF")) {
                        $stmt->bindValue(':filtroCPF', $usuarioFiltro->getCpf());
                    }

                    if (str_contains($SQL, ":filtroAtivo")) {
                        $stmt->bindValue(':filtroAtivo', $usuarioFiltro->getAtivo());
                    }

                    if ($stmt->execute()) {
                        $totalRegistros = $stmt->rowCount();
                    }

                    $arrRetorno['object'] = $usuarios;
                    $arrRetorno['amount'] = $totalRegistros;
                } else {
                    $arrRetorno['object'] = array();
                    $arrRetorno['amount'] = 0;
                }
            } else {
                throw new \Exception("Ops! Falha ao realizar consulta.");
            }
            
            return $arrRetorno;
        }

        public function cadastrarUsuario($ConexaoMy, ?Usuario $usuarioLogado) {

        }
    }
?>