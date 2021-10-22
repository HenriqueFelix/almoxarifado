<?php    
    namespace Vendor\Almoxarifado\controller;
    
    use Vendor\Almoxarifado\model\Usuario;
    
    class UsuarioController {
        public function login($ConexaoMy, $login, $senha) {
            $usuario = array();

            $SQL = "SELECT u.* 
                    FROM usuario u 
                    WHERE (
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

                    $usuario['codigo'] = (int)$Aux['codigo'];
                    $usuario['nome'] = trim((string)$Aux['nome']);
                    $usuario['email'] = trim((string)$Aux['email']);
                    $usuario['cpf'] = trim((string)$Aux['cpf']);
                    $usuario['foto'] = "";
                    $usuario['perfil']['codigo'] = (int)$Aux['perfil'];
                    $usuario['perfil']['telas'] = UsuarioController::verificarPerfil($ConexaoMy, $usuario, 1);

                    if (file_exists("../../upload/usuario/perfil/".$usuario['codigo'].".jpg")) {
                        $usuario['foto'] = "../upload/usuario/perfil/".$usuario['codigo'].".jpg";
                    }
                } else {
                    throw new \Exception("Login ou senha inválido(s).");
                }
            } else {
                throw new \Exception("Ops! Falha ao realizar login.");
            }

            return $usuario;
		}

        public function verificarPerfil($ConexaoMy, $usuario, $retornarTela) {
            $telas = array();
            $menu = array();
            $perfilTelas = array();

            $codigoPerfil = (int)$usuario['perfil']['codigo'];

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
                                //$stmt->bindValue(':codigosMenu', $codigosMenus);

                                if ($stmt->execute()) {
                                    if ($stmt->rowCount() > 0) {
                                        $resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                                        for ($t=0; $t < count($resultQuery); $t++) { 
                                            $Aux = $resultQuery[$t];

                                            $menu[$t]['codigo']    = (int)$Aux['codigo'];
                                            $menu[$t]['titulo']    = trim($Aux['titulo']);
                                            $menu[$t]['icone']     = trim($Aux['icone']);
                                            $menu[$t]['sub_menu']  = (int)$Aux['sub_menu'];
                                            $menu[$t]['telas']     = array();
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
                                            array_push($menu[$keyMenu]['telas'], $valueTela);
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

        public function consultarUsuarios($ConexaoMy, $usuarioLogado, $query, $pagina, $itemsPorPagina) {
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

            $offset = ((int)$pagina - 1) * (int)$itemsPorPagina;

            $SQL = "SELECT u.codigo, 
                        u.nome, 
                        u.email, 
                        u.perfil, 
                        p.descricao AS perfil_descricao 
                    FROM usuario u 
                        LEFT JOIN perfil p ON p.codigo = u.perfil 
                    WHERE u.ativo = '1' 
                        ".$filtroSQL." 
                    ORDER BY u.nome ASC 
                    LIMIT ".(int)$offset.", ".(int)$itemsPorPagina."; ";
			
            $stmt = $ConexaoMy->prepare($SQL);
            $stmt->bindValue(':queryNome', '%'.$query.'%');
            $stmt->bindValue(':queryCPF', $query.'%');
            $stmt->bindValue(':queryEmail', $query.'%');

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $usuarios = array();

                    $resultQuery = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    for ($i=0; $i < count($resultQuery); $i++) {
                        $Aux = $resultQuery[$i];

                        $usuarios[$i]['codigo']                 = (int)$Aux['codigo'];
                        $usuarios[$i]['nome']                   = trim((string)$Aux['nome']);
                        $usuarios[$i]['email']                  = trim((string)$Aux['email']);
                        $usuarios[$i]['perfil']['codigo']       = (int)$Aux['perfil'];
                        $usuarios[$i]['perfil']['descricao']    = trim((string)$Aux['perfil_descricao']);
                    }

                    //QUANTIDADE TOTAL
                    $totalRegistros = $i;

                    $SQL = "SELECT u.codigo 
                            FROM usuario u 
                            WHERE u.ativo = '1' 
                                ".$filtroSQL."; ";

                    $stmt = $ConexaoMy->prepare($SQL);
                    $stmt->bindValue(':queryNome', '%'.$query.'%');
                    $stmt->bindValue(':queryCPF', $query.'%');
                    $stmt->bindValue(':queryEmail', $query.'%');

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

        public function cadastrarUsuario($ConexaoMy, $usuarioLogado) {

        }
    }
?>