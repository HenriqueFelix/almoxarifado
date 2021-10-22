//var GL_URLPAGE = "http://localhost/";
var GL_URLPAGE = "http://192.168.0.101/";
var GL_NOME_SISTEMA = "Ctrl+A";
var GL_VERSAO_SISTEMA = "1.0.0";
var GL_USUARIO_LOGADO;

function validarPerfil(tela, progressId, menuId, resolvePromise) {
    var parametros = {
        "metodo": "ValidarPerfil", 
        "tela" : tela,
    };

    var urlRequest = "../src/api/usuario.php";
    if (tela == "index.html") {
        urlRequest = "src/api/usuario.php";
    }

    $.ajax({
        url: urlRequest,
        type: "GET",
        ContentType: 'application/json',
        data: parametros,
        beforeSend: function () {
            if (verificarObjeto(progressId)) {
                $("#"+progressId).show();
            }
        },
        success: function (ret) {
            console.log(ret);

            try {
                if (parseInt(ret.valido) != 1) {
                    alert(ret.mensagem);

                    window.location.replace(GL_URLPAGE+"/almoxarifado");
                } else {
                    GL_USUARIO_LOGADO = ret.usuario;

                    loadMenu(progressId, menuId, tela, true, resolvePromise);

                    var profileImage = $("#profileImage");
                    if (verificarObjeto(profileImage)) {
                        if (verificarObjeto(GL_USUARIO_LOGADO.foto) && GL_USUARIO_LOGADO.foto != "") {
                            profileImage.attr('src', GL_USUARIO_LOGADO.foto);
                        }
                    }
                }
            } catch (e) {
                console.error(e);
                alert("Ops! Não foi possível realizar validação de perfil.");

                window.location.replace(GL_URLPAGE+"/almoxarifado");
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            alert("Ops! Não foi possível completar a validação do perfil.");

            if (verificarObjeto(progressId)) {
                $("#"+progressId).hide();
            }

            window.location.replace(GL_URLPAGE+"/almoxarifado");
        }
    });
}

function loadMenu(progressId, menuId, telaAtual, redirecionarErro, resolvePromise) {
    var urlRequest = "../src/api/usuario.php";
    if (telaAtual == "index.html") {
        urlRequest = "src/api/usuario.php";
    }

    var parametros = {
        "metodo": "CarregarMenu"
    };

    $.ajax({
        url: urlRequest,
        type: "GET",
        ContentType: 'application/json',
        data: parametros,
        beforeSend: function () {
            if (verificarObjeto(progressId)) {
                $("#"+progressId).show();
            }
        },
        success: function (ret) {
            console.log(ret);

            try {
                if (parseInt(ret.valido) != 1) {
                    alert(ret.mensagem);

                    if (redirecionarErro) {
                        window.location.replace(GL_URLPAGE+"/almoxarifado");
                    }
                } else {
                    var menuHTML = "";
                    var arrMenu = ret.telas.menu;

                    for (let i = 0; i < arrMenu.length; i++) {
                        const menu = arrMenu[i];

                        var url = "";
                        var urlAtiva = "";

                        if (menu.sub_menu == 0 && verificarObjeto(menu.telas[0])) {
                            url = menu.telas[0].diretorio;

                            if (telaAtual == url) {
                                urlAtiva = "active";
                            } else {
                                for (let m = 0; m < menu.telas.length; m++) {
                                    const telasMenu = menu.telas[m];
                                    if (telaAtual == telasMenu.diretorio) {
                                        urlAtiva = "active";
                                    }
                                }
                            }
                            
                            if (verificarObjeto(url) && url != "") {
                                menuHTML += '<a href="../'+url+'" class="nav_link '+urlAtiva+'">';
                                menuHTML +=     '<i class="'+menu.icone+' nav_icon"></i>';
                                menuHTML +=     '<span class="nav_name">'+menu.titulo+'</span>';
                                menuHTML += '</a>';
                            }
                        }
                    }

                    $("#"+menuId).html(menuHTML);

                    if (verificarObjeto(menuHTML) && menuHTML.trim() != "") {
                        if (verificarObjeto(progressId)) {
                            $("#"+progressId).hide();
                        }
    
                        if (verificarObjeto(resolvePromise)) {
                            setInterval(function(){
                                resolvePromise('1');
                            }, 500);
                        }
                    } else {
                        alert("Não foi possível identificar as telas do usuário.");

                        if (redirecionarErro) {
                            window.location.replace(GL_URLPAGE+"/almoxarifado");
                        }
                    }
                }
            } catch (e) {
                console.error(e);
                alert("Ops! Não foi possível realizar validação de perfil.");

                if (redirecionarErro) {
                    window.location.replace(GL_URLPAGE+"/almoxarifado");
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            alert("Ops! Não foi possível completar a validação do perfil do usuário.");
            
            if (redirecionarErro) {
                window.location.replace(GL_URLPAGE+"/almoxarifado");
            }
        }
    });
}

function logoutSistema(progressId) {
    if (confirm("Deseja sair do "+GL_NOME_SISTEMA+"?")) {
        var urlRequest = "../src/api/usuario.php";

        var parametros = {
            "metodo": "LogoutSistema"
        };

        $.ajax({
            url: urlRequest,
            type: "GET",
            ContentType: 'application/json',
            data: parametros,
            beforeSend: function () {
                if (verificarObjeto(progressId)) {
                    $("#"+progressId).show();
                }
            },
            success: function (ret) {
                console.log(ret);
                
                if (verificarObjeto(ret)) {
                    setInterval(function(){
                        window.location.replace(GL_URLPAGE+"/almoxarifado");
                    }, 3000);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                //console.log(xhr.status);
                console.error(xhr.responseText);
                console.error(thrownError);
            }
        });
    }
}

function verificarSessao(tela, progressId) {
    var urlRequest = "../src/api/usuario.php";
    if (tela == "index.html") {
        urlRequest = "src/api/usuario.php";
    }

    var parametros = {
        "metodo": "ValidarSessao", 
        "tela": tela
    };

    $.ajax({
        url: urlRequest,
        type: "GET",
        ContentType: 'application/json',
        data: parametros,
        beforeSend: function () {
            if (verificarObjeto(progressId)) {
                $("#"+progressId).show();
            }
        },
        success: function (ret) {
            console.log(ret);

            try {
                if (parseInt(ret.valido) == 1) {
                    window.location.replace("operacional/inicio.html");
                } else {
                    if (verificarObjeto(progressId)) {
                        $("#"+progressId).hide();
                    }
                }
            } catch (e) {
                console.error(e);
                alert("Ops! Erro ao validar a sessão.");
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            if (verificarObjeto(progressId)) {
                $("#"+progressId).hide();
            }
        }
    });
}

function verificarObjeto(variavel) {
    if (variavel != null && variavel != undefined) {
        return true;
    }

    return false;
}