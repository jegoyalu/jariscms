<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the view user post page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        if(!isset($_REQUEST["biblia"]))
        {
            print "Lista de biblias de dominio público";
        }
        elseif(
            isset($_REQUEST["libro"])
            &&
            isset($_REQUEST["capitulo"])
            &&
            isset($_REQUEST["versiculo"])
        )
        {
            print biblia_get_libro_label(
                    $_REQUEST["libro"], $_REQUEST["biblia"]
                )
                . " "
                . intval($_REQUEST["capitulo"])
                . ":"
                . biblia_get_versiculo_text($_REQUEST["versiculo"])
            ;
        }
        elseif(isset($_REQUEST["libro"]) && isset($_REQUEST["capitulo"]))
        {
            print biblia_get_libro_label(
                    $_REQUEST["libro"], $_REQUEST["biblia"]
                ) . " " . intval($_REQUEST["capitulo"])
            ;
        }
        elseif(isset($_REQUEST["libro"]))
        {
            print biblia_get_libro_label(
                    $_REQUEST["libro"], $_REQUEST["biblia"]
                )
            ;
        }
        else
        {
            print biblia_get_title($_REQUEST["biblia"]);
        }
    ?>
    field;

    field: content
    <?php
        Jaris\View::addStyle(
            Jaris\Modules::directory("biblia") . "styles/biblia.css"
        );

        if(!isset($_REQUEST["biblia"]))
        {
            print '<div id="biblias">';

            $biblias = biblia_get_all();

            foreach($biblias as $biblia_doc => $biblia)
            {
                print '<div class="biblia">';

                $title_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/" . $biblia_doc
                );

                print '<div class="titulo">'
                    . "<a href=\"$title_url\">"
                    . "<h3>" . $biblia["nombre"] . "</h3>"
                    . "</a>"
                    . "</div>"
                ;

                print '<div class="autor-fecha">'
                    . '<div class="autor">'
                    . '<strong>Autor:</strong> '
                    . $biblia["autor"]
                    . '</div>'
                    . '<div class="fecha">'
                    . '<strong>Año:</strong> '
                    . $biblia["yyyy"]
                    . '</div>'
                    . '</div>'
                ;

                print '<div class="descripcion">'
                    . $biblia["descripcion"]
                    . '</div>'
                ;

                print '<div class="leer">'
                    . '<a href="'.$title_url.'">'
                    . 'Leer Ahora'
                    . '</a>'
                    . '</div>'
                ;

                print "</div>";
            }

            print '</div>';
        }
        elseif(
            isset($_REQUEST["biblia"]) && isset($_REQUEST["versiculo"])
        )
        {
            print "<h3 class=\"version-titulo\">"
                . biblia_get_title($_REQUEST["biblia"])
                . "</h3>"
            ;

            $versiculos_parts = explode("-", $_REQUEST["versiculo"]);
            $versiculos_comma_parts = explode(",", $_REQUEST["versiculo"]);
            $versiculos_text = biblia_get_versiculo_text($_REQUEST["versiculo"]);

            if(count($versiculos_parts) > 1)
            {
                $versiculos = range($versiculos_parts[0], $versiculos_parts[1]);
            }
            elseif(count($versiculos_comma_parts) > 1)
            {
                $versiculos = $versiculos_comma_parts;
            }
            else
            {
                $versiculos = array($_REQUEST["versiculo"]);
            }

            print '<div id="versiculos">';
            foreach($versiculos as $versiculo_numero)
            {
                foreach(
                    biblia_get_versiculo(
                        $versiculo_numero,
                        $_REQUEST["capitulo"],
                        $_REQUEST["libro"],
                        $_REQUEST["biblia"]
                    )
                    as
                    $versiculo
                )
                {
                    print '<div class="versiculo v'.$versiculo["versiculo"].'">'
                        . '<a id="'.$versiculo["versiculo"].'"></a>'
                        . '<strong>' . $versiculo["versiculo"] . '</strong> '
                        . '<div>'
                        . $versiculo["texto"]
                        . '</div>'
                        . '</div>'
                    ;
                }
            }
            print '</div>';

            $link = Jaris\Uri::url(
                Jaris\Modules::getPageUri("biblia", "biblia")
                . "/"
                . $_REQUEST["biblia"]
                . "/"
                . $_REQUEST["libro"]
                . "/"
                . $_REQUEST["capitulo"]
            );

            print '<div id="libro-navegacion" class="libro-navegacion-center">'
                . '<a href="'.$link.'">'
                . biblia_get_libro_label(
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                )
                . " "
                . intval($_REQUEST["capitulo"])
                . " "
                . "(Completo)"
                . "</a>"
                . '</div>'
            ;
        }
        elseif(
            isset($_REQUEST["biblia"])
        )
        {
            print "<h3 class=\"version-titulo\">"
                . biblia_get_title($_REQUEST["biblia"])
                . "</h3>"
            ;

            $parameters = array();
            $parameters["name"] = "biblia";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri("biblia", "biblia")
            );
            $parameters["method"] = "post";

            $fields = biblia_get_form_fields("biblia", $_REQUEST["biblia"]);

            $fieldset = array();

            $fieldset[] = array(
                "fields" => $fields
            );

            print Jaris\Forms::generate($parameters, $fieldset);

            $link = Jaris\Uri::url(
                Jaris\Modules::getPageUri("biblia/buscar", "biblia"),
                array(
                    "libro" => $_REQUEST["libro"],
                    "biblia" => $_REQUEST["biblia"]
                )
            );

            print '<div style="text-align: center; margin-top: 10px; margin-bottom: 10px;">'
                . "<a href=\"$link\">"
                . "- Buscador de Palabras -"
                . "</a>"
                . '</div>'
            ;

            print '<div id="versiculos">';
            foreach(
                biblia_get_capitulo(
                    $_REQUEST["capitulo"],
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                )
                as
                $versiculo
            )
            {
                $link = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $_REQUEST["biblia"]
                    . "/"
                    . $_REQUEST["libro"]
                    . "/"
                    . $_REQUEST["capitulo"]
                    . "/"
                    . $versiculo["versiculo"]
                );

                print '<div class="versiculo v'.$versiculo["versiculo"].'">'
                    . '<a id="'.$versiculo["versiculo"].'"></a>'
                    . '<strong>'
                    . '<a href="'.$link.'">'
                    . $versiculo["versiculo"]
                    . '</a>'
                    . '</strong> '
                    . '<div>'
                    . $versiculo["texto"]
                    . '</div>'
                    . '</div>'
                ;
            }
            print '</div>';

            print '<div id="libro-navegacion">'
                . biblia_get_previous_link(
                    intval($_REQUEST["capitulo"]),
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                )
                . "<strong>"
                . biblia_get_libro_label(
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                )
                . " "
                . intval($_REQUEST["capitulo"])
                . "</strong>"
                . biblia_get_next_link(
                    intval($_REQUEST["capitulo"]),
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                )
                . '</div>'
            ;
        }
    ?>
    <script>
    $(document).ready(function(){
        var hash = window.location.hash.substr(1);

        if(hash != "") {
            $(".v"+hash).addClass("active")

            $("#biblia-versiculo").val(hash);
        }
    });
    </script>
    field;

    field: is_system
        1
    field;
row;
