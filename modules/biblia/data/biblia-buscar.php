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
        print t("Buscar en la Biblia")
    ?>
    field;

    field: content
    <?php
        Jaris\View::addStyle(
            Jaris\Modules::directory("biblia") . "styles/biblia.css"
        );

        $parameters = array();
        $parameters["name"] = "biblia";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $fields = array();

        $fields[] = array(
            "type" => "text",
            "name" => "q",
            "label" => "Palabra:",
            "value" => $_REQUEST["q"],
            "placeholder" => "palabra o palabras a buscar..."
        );

        $libros = biblia_get_libros_machine();

        $libros_list = array("Todos" => "");
        foreach($libros as $libro_machine => $libro_data)
        {
            $libros_list[$libro_data[1]] = $libro_machine;
        }

        $fields[] = array(
            "type" => "select",
            "selected" => $_REQUEST["libro"],
            "name" => "libro",
            "value" => $libros_list,
            "label" => t("Libro:"),
            "id" => "libro",
            "inline" => true
        );

        $biblias = biblia_get_all();
        $biblias_list = array();

        foreach($biblias as $biblia => $biblia_data)
        {
            $biblias_list[$biblia_data["codigo"]] = $biblia;
        }

        $fields[] = array(
            "type" => "select",
            "selected" => $_REQUEST["biblia"],
            "name" => "biblia",
            "value" => $biblias_list,
            "label" => t("Versión:"),
            "id" => "biblia",
            "inline" => true
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "search",
            "value" => "Buscar"
        );

        $fieldset = array();

        $fieldset[] = array(
            "fields" => $fields
        );

        print Jaris\Forms::generate($parameters, $fieldset);

        if(isset($_REQUEST["q"]))
        {
            $page = 1;

            if(isset($_REQUEST["page"]))
            {
                $page = intval($_REQUEST["page"]);
            }

            $db = biblia_open($_REQUEST["biblia"]);

            $query = $_REQUEST["q"];
            Jaris\Sql::escapeVar($query);

            $query = preg_replace(
                "/ +/",
                " ",
                $query
            );

            $palabras = explode(" ", $query);

            $like = "";

            foreach($palabras as $palabra)
            {
                $like = "texto like '%$query%' "
                    . "or "
                ;
            }

            $like = rtrim($like, "or ");

            $libro = "";
            if(isset($_REQUEST["libro"]) && trim($_REQUEST["libro"]) != "")
            {
                $libro .= "and libro='".$libros[$_REQUEST["libro"]][0]."'";
            }

            $results_count = Jaris\Sql::countColumn(
                $_REQUEST["biblia"],
                "bible",
                "versiculo",
                "where $like $libro",
                Jaris\Modules::directory("biblia") . "biblias"
            );

            $results = Jaris\Sql::getDataList(
                $_REQUEST["biblia"],
                "bible",
                $page-1,
                30,
                "where $like $libro",
                "*",
                Jaris\Modules::directory("biblia") . "biblias"
            );


            print '<div id="versiculos-buscar">';
            foreach(
                $results
                as
                $versiculo
            )
            {
                $libro = $libro_machine = preg_replace(
                    "/ +/",
                    "-",
                    str_replace(
                        array(
                            "á", "é", "í", "ó", "ú", "ñ",
                            "Á", "É", "Í", "Ó", "Ú", "Ñ",
                            "ü", "Ü", "-"
                        ),
                        array(
                            "a", "e", "i", "o", "u", "n",
                            "a", "e", "i", "o", "u", "n",
                            "u", "u", " "
                        ),
                        trim(
                            strtolower(
                                biblia_get_libros()[$versiculo["libro"]]
                            )
                        )
                    )
                );
                $link = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $_REQUEST["biblia"]
                    . "/"
                    . $libro
                    . "/"
                    . $versiculo["capitulo"]
                    . "#"
                    . $versiculo["versiculo"]
                );

                $palabras = array_map(
                    function($string){
                        return strtolower($string);
                    },
                    $palabras
                );

                $palabras_new = $palabras;
                foreach($palabras as $palabra)
                {
                    $palabras_new[] = ucwords($palabra);
                }

                $palabras_strong = array_map(
                    function($string){
                        return "<strong style=\"color: red;\">".$string."</strong>";
                    },
                    $palabras_new
                );

                $versiculo_text = str_replace(
                    $palabras_new,
                    $palabras_strong,
                    $versiculo["texto"]
                );

                print '<div class="versiculo v'.$versiculo["versiculo"].'">'
                    . '<div>'
                    . '<div>'
                    . '<strong>'
                    . '<a href="'.$link.'">'
                    . biblia_get_libros()[$versiculo["libro"]]
                    . " "
                    . $versiculo["capitulo"]
                    . ":"
                    . $versiculo["versiculo"]
                    . '</a>'
                    . '</strong> '
                    . '</div>'
                    . $versiculo_text
                    . '</div>'
                    . '</div>'
                ;
            }
            print '</div>';

            Jaris\System::printNavigation(
                $results_count,
                $page,
                "biblia/buscar",
                "biblia",
                30,
                array(
                    "libro" => $_REQUEST["libro"],
                    "biblia" => $_REQUEST["biblia"],
                    "q" => $_REQUEST["q"]
                )
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
