//var GL_URLPAGE = "http://localhost/";
var GL_URLPAGE = "http://192.168.0.106/";
var GL_NOME_SISTEMA = "Almoxarifado";
var GL_VERSAO_SISTEMA = "1.0.0";
var GL_USUARIO_LOGADO;

$(function () {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        $(".modal-custom div.modal-dialog").removeClass("modal-dialog-centered");
    }
});

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

                setInterval(function(){
                    window.location.replace(GL_URLPAGE+"/almoxarifado");
                }, 3000);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            alert("Ops! Não foi possível completar a validação do perfil.");

            setInterval(function(){
                window.location.replace(GL_URLPAGE+"/almoxarifado");
            }, 3000);
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

                        if (menu.sub_menu == 0 && verificarObjeto(menu.paginas[0])) {
                            url = menu.paginas[0].diretorio;

                            if (telaAtual == url) {
                                urlAtiva = "active";
                            } else {
                                for (let m = 0; m < menu.paginas.length; m++) {
                                    const telasMenu = menu.paginas[m];
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
                    }, 2000);
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

function redirecionarPagina(URL, Parametros) {
	var form = document.createElement('form');
	form.setAttribute('method',"post");
    form.setAttribute('action',URL);
	form.acceptCharset="UTF-8";
	
	if(typeof Parametros != "undefined") {
		var ParametrosQueb = Parametros.split('&');
		for(var x = 0; x < ParametrosQueb.length; x++) {
			var input = document.createElement('input');
			var Param = ParametrosQueb[x].split('=');

			input.setAttribute('type','hidden');
			input.setAttribute('name',Param[0]);
			input.setAttribute('value',Param[1]);
			
			form.appendChild(input);
		}
	}
	
	try {
		document.getElementById('formTempSubmit').appendChild(form);
	} catch(e) {
        console.error(e);
        
        alert("Erro ao redirecionar página!")
        return false;
	}
	
	form.submit();
	
	try {
		document.getElementById('formTempSubmit').removeChild(form);
	} catch(e) {
        console.error(e);
	}
}

function verificarObjeto(variavel) {
    if (variavel != null && variavel != undefined) {
        return true;
    }

    return false;
}

function redirecionarPaginaAjax(progressId, url, param){
    var parametros = {
        "metodo": "RedirecionarPagina", 
        "url" : url,
        "param" : param,
    };

    var urlRequest = "../src/api/web_pages.php";

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

            if (verificarObjeto(progressId)) {
                $("#"+progressId).hide();
            }

            try {
                if (parseInt(ret.valido) != 1) {
                    alert(ret.mensagem);
                } else {
                    window.location.replace(ret.url);
                }
            } catch (e) {
                console.error(e);
                alert("Ops! Não foi possível abrir a página.");
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //console.log(xhr.status);
            console.error(xhr.responseText);
            console.error(thrownError);

            if (verificarObjeto(progressId)) {
                $("#"+progressId).hide();
            }
            alert("Ops! Erro ao abrir página.");
        }
    });
}

function setTokenGetUrl(data) {

}