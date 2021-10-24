function initSelect2Outline(idElement, typeElement) {
    var classUniqueSelect2 = idElement + "-class";

    var selectElement = $("#" + idElement);
    var label = $("label[for=" + idElement + "]");

    $($("#" + idElement).data("select2").$container).addClass(classUniqueSelect2);

    if (typeElement == 0) {
        if (selectElement.val() != null && selectElement.val() != undefined && selectElement.val() != "") {
            focusedSelect2(label, classUniqueSelect2);
        } else {
            unfocusedSelect2(label, classUniqueSelect2);
        }
    } else if (typeElement == 1) {
        if (selectElement.val() != null && selectElement.val() != undefined && selectElement.val().length > 0) {
            focusedSelect2(label, classUniqueSelect2);
        } else {
            unfocusedSelect2(label, classUniqueSelect2);
        }
    }

    if (typeElement == 0) {
        $("#" + idElement).on("select2:open", function () {
            try {
                $("input.select2-search__field")[0].focus();
            } catch (error) {
                console.error(error);
            }

            setInterval(function () {
                try {
                    if ($("input.select2-search__field")[0] != null && $("input.select2-search__field")[0] != undefined) {
                        $("input.select2-search__field")[0].focus();
                    }
                } catch (error) {
                    console.error(error);
                }
            }, 1000);
        });
    } else {
        $("#" + idElement).on("select2:open", function () {
            $(".select2-dropdown.select2-dropdown--below").addClass("multiple");
        });
    }

    $("#" + idElement).on("select2:select", function (e) {
        //var data = e.params.data;
        //console.log(data);

        focusedSelect2(label, classUniqueSelect2);
    });

    $("#" + idElement).on("select2:unselecting", function (e) {
        //var data = e.params.args.data;
        //console.log(data);

        var qtdSelecionado = 0;
        $("." + classUniqueSelect2 + " .select2-selection__choice").each(function () {
            qtdSelecionado++
        });

        if (qtdSelecionado <= 1) {
            unfocusedSelect2(label, classUniqueSelect2);
        }
    });
}

function focusedSelect2(label, classUnique) {
    label.addClass("label-focus");
    $("." + classUnique + ".select2-container--default .select2-selection").addClass("label-focus");
}

function unfocusedSelect2(label, classUnique) {
    label.removeClass("label-focus");
    $("." + classUnique + ".select2-container--default .select2-selection").removeClass("label-focus");
}