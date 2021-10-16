//var GL_URLPAGE = "http://localhost/";
var GL_URLPAGE = "http://192.168.0.108/";
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
            if (progressId != null && progressId != undefined) {
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
                }
            } catch (e) {
                alert("Ops! Não foi possível realizar validação de perfil.");
                console.error(e);

                window.location.replace(GL_URLPAGE+"/almoxarifado");
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert("Ops! Não foi possível completar a validação do perfil.");

            if (progressId != null && progressId != undefined) {
                $("#"+progressId).hide();
            }

            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

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
            if (progressId != null && progressId != undefined) {
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

                        if (menu.sub_menu == 0 && menu.telas[0] != null && menu.telas[0] != undefined) {
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
                            
                            if (url != null && url != undefined && url != "") {
                                menuHTML += '<a href="../'+url+'" class="nav_link '+urlAtiva+'">';
                                menuHTML +=     '<i class="'+menu.icone+' nav_icon"></i>';
                                menuHTML +=     '<span class="nav_name">'+menu.titulo+'</span>';
                                menuHTML += '</a>';
                            }
                        }
                    }

                    $("#"+menuId).html(menuHTML);

                    if (menuHTML != null && menuHTML != undefined && menuHTML.trim() != "") {
                        if (progressId != null && progressId != undefined) {
                            $("#"+progressId).hide();
                        }
    
                        if (resolvePromise != null && resolvePromise != undefined) {
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
                alert("Ops! Não foi possível realizar validação de perfil.");
                console.error(e);

                if (redirecionarErro) {
                    window.location.replace(GL_URLPAGE+"/almoxarifado");
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert("Ops! Não foi possível completar a validação do perfil do usuário.");
            
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            if (redirecionarErro) {
                window.location.replace(GL_URLPAGE+"/almoxarifado");
            }
        }
    });
}

function logoutSistema() {
    if (confirm("Deseja sair?")) {
        window.location.replace(GL_URLPAGE+"/almoxarifado");   
    }
}