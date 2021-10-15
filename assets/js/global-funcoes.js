
function validarPerfil(tela, progressId, menuId) {
    var parametros = {
        "metodo": "ValidarPerfil", 
        "tela" : tela,
    };

    $.ajax({
        url: "http://localhost/almoxarifado/src/api/usuario.php",
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

                    window.location.replace("http://localhost/almoxarifado");
                } else {
                    loadMenu(progressId, menuId, tela, true);
                }
            } catch (e) {
                alert("Ops! Não foi possível realizar validação de perfil.");
                console.error(e);

                window.location.replace("http://localhost/almoxarifado");
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

            window.location.replace("http://localhost/almoxarifado");
        }
    });
}

function loadMenu(progressId, menuId, telaAtual, redirecionarErro) {
    var parametros = {
        "metodo": "CarregarMenu"
    };

    $.ajax({
        url: "http://localhost/almoxarifado/src/api/usuario.php",
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
                        window.location.replace("http://localhost/almoxarifado");
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

                    if (progressId != null && progressId != undefined) {
                        $("#"+progressId).hide();
                    }
                }
            } catch (e) {
                alert("Ops! Não foi possível realizar validação de perfil.");
                console.error(e);

                if (redirecionarErro) {
                    window.location.replace("http://localhost/almoxarifado");
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert("Ops! Não foi possível completar a validação do perfil.");
            
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            if (redirecionarErro) {
                window.location.replace("http://localhost/almoxarifado");
            }
        }
    });
}

function logoutSistema() {
    if (confirm("Deseja sair?")) {
        window.location.replace("http://localhost/almoxarifado");   
    }
}