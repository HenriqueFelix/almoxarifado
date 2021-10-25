<?php 
    namespace Vendor\Almoxarifado\api;
    
    header("Access-Control-Allow-Origin: *");
	header('Content-Type: application/json');
    header("Access-Control-Allow-Headers: *");
    
    session_start();

    require_once '../../vendor/autoload.php';
    require_once '../config/conection.php';
    require_once '../config/funcoes.php';
	
    use Vendor\Almoxarifado\model\Usuario;
    use Vendor\Almoxarifado\controller\UsuarioController;
    use Vendor\Almoxarifado\model\Perfil;

    $usuarioController = new UsuarioController;

    if ($_GET != null && isset($_GET["metodo"])) {
        if ($_GET["metodo"] == "Login") {
            $ConexaoMy = DBConnectMy();
    
            try {
                $valido = 1;
                $msg = "Login realizado com sucesso!";
                $usuario = null;
        
                $login = trim((string)$_GET["login"]);
                $senha = $_GET["senha"];
        
                if ($login == null || $login == "") {
                    $valido = 0;
                    $msg = "Informe o e-mail!";
                } else if ($senha == null || trim((string)$senha) == "") {
                    $valido = 0;
                    $msg = "Informe a senha!";
                } else {
                    $usuario = $usuarioController->login($ConexaoMy, $login, $senha);
                    if ($usuario != null && $usuario->getCodigo() > 0) {
                        $_SESSION['usuario']        = serialize($usuario);
                        $_SESSION['token_sessao']   = (string)md5($_SERVER['SERVER_NAME'].$usuario->getCodigo()).date("YmdHis");
                    } else {
                        $valido = 0;
                        $msg = "Usuário inválido!";
                    }
                }
    
                if ((int)$valido != 1) {
                    session_destroy();
                }
    
                $arrRetorno = array();
                $arrRetorno['valido']     = (int)$valido;
                $arrRetorno['mensagem']   = $msg;
                $arrRetorno['usuario']    = $usuario;
    
                DBClose($ConexaoMy);
    
                echo json_encode($arrRetorno);
                die();
            } catch (\Exception $e) {
                http_response_code(200);
    
                DBClose($ConexaoMy);
                session_destroy();
    
                echo json_encode(array('valido' => 0, 'mensagem' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
                die();
            }
        }
    
        if ($_GET["metodo"] == "LogoutSistema") {
            $_SESSION = null;
            session_destroy();
    
            $arrRetorno = array();
            $arrRetorno['valido']   = 1;
            $arrRetorno['mensagem'] = "Deslogado do sistema.";
    
            echo json_encode($arrRetorno);
            die();
        }
        
        if ($_GET["metodo"] == "ValidarPerfil") {
            $ConexaoMy = DBConnectMy();
    
            try {
                $valido = 1;
                $msg = "Perfil liberado.";
    
                $tela_atual = trim((string)$_GET["tela"]);
    
                $usuarioLogado = unserialize($_SESSION['usuario']);
    
                if ($_SESSION == null || count($_SESSION) <= 0) {
                    $valido = 0;
                    $msg = "Sessão inválida!";
                } else if ($_SESSION['token_sessao'] == null || trim((string)$_SESSION['token_sessao']) == "") {
                    $valido = 0;
                    $msg = "Token da sessão é inválido!";
                } else if ($tela_atual == null || $tela_atual == "") {
                    $valido = 0;
                    $msg = "Tela inválida!";
                } else if ($usuarioLogado == null || $usuarioLogado->getCodigo() <= 0) {
                    $valido = 0;
                    $msg = "Usuário não identificado na sessão!";
                } else {
                    $validToken = (string)md5($_SERVER['SERVER_NAME'].$usuarioLogado->getCodigo());
                    $startToken = substr_replace($_SESSION['token_sessao'], "", -14);
                    $timeToken = substr($_SESSION['token_sessao'], -14);
    
                    if ($validToken != $startToken) {
                        $valido = 0;
                        $msg = "Token inválido!";
                    } else {
                        $perfil = $usuarioController->verificarPerfil($ConexaoMy, $usuarioLogado);
                    }
                }
    
                if ((int)$valido != 1) {
                    $_SESSION = null;
                    session_destroy();
                } else if ($usuarioLogado->getPerfil() != null) {
                    $validaTela = 0;
                    $arrTelaAtiva = $usuarioLogado->getPerfil()->getTelas()['tela'];
                    foreach ($arrTelaAtiva as $keyTela => $value) {
                        if (trim($value['diretorio']) == trim($tela_atual)) {
                            $validaTela = 1;
                            break;
                        }
                    }
                    
                    if ($validaTela != 1) {
                        $valido = 0;
                        $msg = "Você não tem permissão para acessar a tela atual!";
                    }
                }
    
                if ((int)$valido != 1) {
                    $_SESSION = null;
                    session_destroy();
                }
    
                $arrRetorno = array();
                $arrRetorno['valido']   = (int)$valido;
                $arrRetorno['mensagem'] = $msg;
                $arrRetorno['usuario']  = $usuarioLogado;
    
                DBClose($ConexaoMy);
    
                echo json_encode($arrRetorno);
                die();
            } catch (\Exception $e) {
                http_response_code(200); // 404
    
                $_SESSION = null;
                session_destroy();
    
                DBClose($ConexaoMy);
    
                echo json_encode(array('valido' => 0, 'mensagem' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
                die();
            }
        }
    
        if ($_GET["metodo"] == "CarregarMenu") {
            $ConexaoMy = DBConnectMy();
    
            try {
                $usuarioLogado = unserialize($_SESSION['usuario']);
    
                $valido = 1;
                $msg = "Telas liberadas.";
    
                $arrRetorno = array();
                $arrRetorno['valido']   = (int)$valido;
                $arrRetorno['mensagem'] = $msg;
                $arrRetorno['telas']    = $usuarioLogado->getPerfil()->getTelas();
    
                DBClose($ConexaoMy);
    
                echo json_encode($arrRetorno);
                die();
            } catch (\Exception $e) {
                http_response_code(200); // 404
    
                DBClose($ConexaoMy);
    
                $_SESSION = null;
                session_destroy();
    
                echo json_encode(array('valido' => 0, 'mensagem' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
                die();
            }
        }
    
        if ($_GET["metodo"] == "ValidarSessao") {
            $valido = 1;
            $msg = "Sessão valida.";
    
            $tela_atual = trim((string)$_GET["tela"]);
    
            if ($_SESSION == null || count($_SESSION) <= 0) {
                $valido = 0;
                $msg = "Sessão inválida!";
            } else if ($_SESSION['token_sessao'] == null || trim((string)$_SESSION['token_sessao']) == "") {
                $valido = 0;
                $msg = "Token da sessão é inválido!";
            } else if ($tela_atual == null || $tela_atual == "") {
                $valido = 0;
                $msg = "Tela inválida!";
            } else if ($_SESSION['usuario'] == null || unserialize($_SESSION['usuario'])->getCodigo() <= 0) {
                $valido = 0;
                $msg = "Usuário não identificado na sessão!";
            }
    
            $arrRetorno = array();
            $arrRetorno['valido']   = (int)$valido;
            $arrRetorno['mensagem'] = $msg;
    
            sleep(1);
    
            echo json_encode($arrRetorno);
            die();
        }
    
        if ($_GET["metodo"] == "ConsultarUsuarios") {
            $ConexaoMy = DBConnectMy();
    
            try {
                $arrRetorno = array();
                
                $valido = 1;
                $msg = "Listagem realizada com sucesso.";
    
                $query = "";
                $queryNome = "";
                $queryEmail = "";
                $queryCPF = "";
                $queryPerfil = 0;
                $queryAtivo = 1;
    
                if (isset($_GET['query'])) {
                    $query = trim((string)$_GET['query']);
                }
    
                if (isset($_GET['nome'])) {
                    $queryNome = trim((string)$_GET['nome']);
                }
    
                if (isset($_GET['email'])) {
                    $queryEmail = trim((string)$_GET['email']);
                }
    
                if (isset($_GET['cpf'])) {
                    $queryCPF = trim((string)$_GET['cpf']);
                }
    
                if (isset($_GET['perfil'])) {
                    $queryPerfil = $_GET['perfil'];
                }
    
                if (isset($_GET['ativo']) && checkValue($_GET['ativo'])) {
                    $queryAtivo = (int)$_GET['ativo'];
                }
    
                $page = (int)$_GET['page'];
                $max = (int)$_GET['maximum'];
    
                $usuarioFiltro = new Usuario();
                $usuarioFiltro->setCodigo(0);
                $usuarioFiltro->setNome($queryNome);
                $usuarioFiltro->setEmail($queryEmail);
                $usuarioFiltro->setCpf($queryCPF);
                $usuarioFiltro->setAtivo($queryAtivo);
    
                $perfilFiltro = new Perfil();
                $perfilFiltro->setCodigo($queryPerfil);
    
                $usuarioFiltro->setPerfil($perfilFiltro);
    
                $usuarios = $usuarioController->consultarUsuarios($ConexaoMy, unserialize($_SESSION['usuario']), $query, $usuarioFiltro, $page, $max);
                if ($usuarios == null) {
                    $valido = 0;
                    $msg    = "Erro ao consultar usuário(s).";
                } else {
                    $arrRetorno['total']    = $usuarios['amount'];
                    $arrRetorno['filtered'] = count($usuarios['object']);
                    $arrRetorno['data']     = $usuarios['object'];
                    $arrRetorno['query']    = getFilterPagination($_GET);
                }
    
                $arrRetorno['valido']   = (int)$valido;
                $arrRetorno['mensagem'] = $msg;
    
                DBClose($ConexaoMy);
    
                sleep(3);
    
                echo json_encode($arrRetorno);
                die();
            } catch (\Exception $e) {
                http_response_code(200);
    
                DBClose($ConexaoMy);
    
                echo json_encode(array('valido' => 0, 'mensagem' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
                die();
            }
        }
    } else if ($_POST != null && isset($_POST["metodo"])) {
        if ($_POST["metodo"] == "CadastrarUsuario") {
            $ConexaoMy = DBConnectMy();
    
            try {
                $valido = 1;
                $msg = "Cadastrado realizado com sucesso.";
    
                $arrRetorno = array();
                $arrRetorno['valido']   = (int)$valido;
                $arrRetorno['mensagem'] = $msg;
    
                echo json_encode($arrRetorno);
                die();
            } catch (\Exception $e) {
                http_response_code(200);
    
                DBClose($ConexaoMy);
    
                echo json_encode(array('valido' => 0, 'mensagem' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
                die();
            }
        }
    } else {
        $arrRetorno = array();
        $arrRetorno['valido']   = 0;
        $arrRetorno['mensagem'] = "Ops! Requisição inválida.";
        echo json_encode($arrRetorno);
        die();
    }

    $arrRetorno = array();
    $arrRetorno['valido']   = 0;
    $arrRetorno['mensagem'] = "Requisição não identificada.";
    echo json_encode($arrRetorno);
    die();
?>