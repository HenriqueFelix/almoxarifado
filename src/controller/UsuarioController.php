<?php    
    namespace Vendor\Almoxarifado\controller;
    
    use Vendor\Almoxarifado\model\Usuario;
    
    class UsuarioController {
        public function login($ConexaoMy, $login, $senha) {
            $usuario = array();

            $SQL = "SELECT * 
                    FROM usuario 
                    WHERE email = '".$login."' 
                        AND senha = '".$senha."' 
                        AND ativo = 1 
                    LIMIT 1; ";
			
            $query = mysqli_query($ConexaoMy, utf8_decode($SQL));
            if ($query) {
                if (mysqli_num_rows($query) > 0) {
                    while($Aux = mysqli_fetch_array($query)) {
                        //$Aux = array_map("utf8_encode", $Aux);
		                //$Aux = array_map("strtoupper", $Aux);

                        $usuario['codigo'] = (int)$Aux['codigo'];
                        $usuario['nome'] = trim((string)$Aux['nome']);
                        $usuario['email'] = trim((string)$Aux['email']);
                        $usuario['perfil']['codigo'] = (int)$Aux['perfil'];
                        $usuario['perfil']['telas'] = UsuarioController::verificarPerfil($ConexaoMy, $usuario, 1);
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
                        WHERE codigo = '".$codigoPerfil."' 
                            AND ativo = 1; ";
                
                $query = mysqli_query($ConexaoMy, utf8_decode($SQL));
                if ($query) {
                    if (mysqli_num_rows($query) > 0) {
                        $SQL = "SELECT t.* 
                                FROM perfil_telas pt
                                    LEFT JOIN sistema_telas t ON t.codigo = pt.tela 
                                WHERE pt.perfil = '".$codigoPerfil."' 
                                ORDER BY t.ordem ASC; ";
                        
                        $query = mysqli_query($ConexaoMy, utf8_decode($SQL));
                        if ($query) {
                            if (mysqli_num_rows($query) > 0) {
                                $i = 0;
                                $t = 0;

                                while($Aux = mysqli_fetch_array($query)) {
                                    if (!in_array(trim($Aux['menu']), $menu)) {
                                        $menu[$i] = trim($Aux['menu']);
                                        $i++;
                                    }

                                    $perfilTelas[$t]['codigo']    = $Aux['codigo'];
                                    $perfilTelas[$t]['menu']      = trim($Aux['menu']);
                                    $perfilTelas[$t]['diretorio'] = $Aux['dir'];
                                    $t++;
                                }

                                $telas['menu'] = $menu;
                                $telas['tela'] = $perfilTelas;
                                /*
                                $i = 0;
                                $t = 0;
                                for ($i=0; $i < count($menu); $i++) { 
                                    $descMenu = $menu[$i];
                                    $t = 0;
                                    foreach ($perfilTelas as $key => $value) {
                                        if ($descMenu == $value['menu']) {
                                            $telas[$descMenu][$t]['codigo']    = $value['codigo'];
                                            $telas[$descMenu][$t]['diretorio'] = $value['diretorio'];
                                            $t++;
                                        }
                                    }
                                }
                                */
                            } else {
                                throw new \Exception("Ops! Telas não disponível para o perfil do usuário.");
                            }
                        } else {
                            throw new \Exception("Ops! Erro ao verificar telas do perfil.");
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
    }
?>