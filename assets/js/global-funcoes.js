
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
                    loadMenu(progressId, menuId);
                }
            } catch (e) {
                alert("Ops! Não foi possível realizar validação de perfil.");
                console.error(e);

                window.location.replace("http://localhost/almoxarifado");
            }

            if (progressId != null && progressId != undefined) {
                $("#"+progressId).hide();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert("Ops! Não foi possível completar a validação do perfil.");
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            window.location.replace("http://localhost/almoxarifado");
        }
    });
}

function loadMenu(progressId, menuId) {
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

                    window.location.replace("http://localhost/almoxarifado");
                } else {
                    var menuHTML = "";
                    var arrMenu = ret.telas.menu;
                    var arrTelas = ret.telas.tela;
                    for (let i = 0; i < arrMenu.length; i++) {
                        var url = "";
                        const menu = arrMenu[i];
                        //console.log(menu);
                        for (let t = 0; t < arrTelas.length; t++) {
                            const tela = arrTelas[t];
                            if (menu == tela.menu) {
                                //console.log(tela);
                                url = "../"+tela.diretorio;
                                break;
                            }
                        }

                        if (url != null && url != undefined && url != "") {
                            menuHTML += '<a class="list-group-item list-group-item-action list-group-item-light p-3" href="'+url+'">'+menu+'</a>';
                        }
                    }

                    $("#"+menuId).html(menuHTML);
                }
            } catch (e) {
                alert("Ops! Não foi possível realizar validação de perfil.");
                console.error(e);

                window.location.replace("http://localhost/almoxarifado");
            }

            if (progressId != null && progressId != undefined) {
                $("#"+progressId).hide();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert("Ops! Não foi possível completar a validação do perfil.");
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            window.location.replace("http://localhost/almoxarifado");
        }
    });
}