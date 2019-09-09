<?php

function biblia_get_all()
{
    $biblias_list = [];

    $biblias = scandir(
        Jaris\Modules::directory("biblia") . "biblias"
    );

    foreach ($biblias as $biblia) {
        if ($biblia != "." && $biblia != "..") {
            $db = biblia_open($biblia);

            $result = Jaris\Sql::query("select * from info", $db);

            $data = Jaris\Sql::fetchArray($result);

            if ($data) {
                $biblias_list[$biblia] = $data;
            }

            Jaris\Sql::close($db);
        }
    }

    return $biblias_list;
}

function biblia_get_form_fields($form_name, $biblia)
{
    if (!isset($_REQUEST["libro"])) {
        $_REQUEST["libro"] = "genesis";
        $_REQUEST["capitulo"] = "1";
    }

    $fields = [];

    $libros = biblia_get_libros_machine();

    $libros_list = [];
    foreach ($libros as $libro_machine => $libro_data) {
        $libros_list[$libro_data[1]] = $libro_machine;
    }

    $fields[] = [
        "type" => "select",
        "selected" => $_REQUEST["libro"],
        "name" => "libro",
        "value" => $libros_list,
        "label" => t("Libro:"),
        "id" => "libro",
        "inline" => true
    ];

    $libro_valid = false;
    $capitulo_valid = false;
    $capitulos_list = [];
    $versiculos_list = [];

    if (
        isset($_REQUEST["libro"])
        &&
        in_array($_REQUEST["libro"], $libros_list)
    ) {
        $capitulos_list = array_filter(
            array_merge(
                [0],
                range(
                    1,
                    biblia_get_capitulos($_REQUEST["libro"], $biblia)
                )
            )
        );

        $libro_valid = true;
    }

    if (
        $libro_valid
        &&
        isset($_REQUEST["capitulo"])
        &&
        in_array($_REQUEST["capitulo"], $capitulos_list)
    ) {
        $capitulo_valid = true;

        $fields[] = [
            "type" => "select",
            "selected" => isset($_REQUEST["capitulo"]) ?
                $_REQUEST["capitulo"] : "",
            "name" => "capitulo",
            "value" => $capitulos_list,
            "label" => t("Capítulo:"),
            "id" => "capitulo",
            "inline" => true
        ];

        $versiculos_count = biblia_get_versiculos(
            $_REQUEST["capitulo"],
            $_REQUEST["libro"],
            $biblia
        );

        $versiculos_list = ["Todo" => ""];

        for ($i=1; $i<=$versiculos_count; $i++) {
            $versiculos_list[$i] = $i;
        }
    } else {
        $fields[] = [
            "type" => "select",
            "selected" => "",
            "name" => "capitulo",
            "value" => $capitulos_list,
            "label" => t("Capítulo:"),
            "id" => "capitulo",
            "inline" => true
        ];
    }

    if ($libro_valid && $capitulo_valid && count($versiculos_list) > 0) {
        $fields[] = [
            "type" => "select",
            "selected" => isset($_REQUEST["versiculo"])
                &&
                in_array($_REQUEST["versiculo"], $versiculos_list) ?
                $_REQUEST["versiculo"] : "",
            "name" => "versiculo",
            "value" => $versiculos_list,
            "label" => t("Versículo:"),
            "id" => "versiculo",
            "inline" => true
        ];
    } else {
        $fields[] = [
            "type" => "select",
            "selected" => "",
            "name" => "versiculo",
            "value" => [],
            "label" => t("Versículo:"),
            "id" => "versiculo",
            "inline" => true
        ];
    }

    $biblias = biblia_get_all();

    $biblias_list = [];

    foreach ($biblias as $biblia => $biblia_data) {
        $biblias_list[$biblia_data["codigo"]] = $biblia;
    }

    $fields[] = [
        "type" => "select",
        "selected" => $_REQUEST["biblia"] ?? "",
        "name" => "biblia",
        "value" => $biblias_list,
        "label" => t("Versión:"),
        "id" => "biblia",
        "inline" => true
    ];

    $fields[] = [
        "type" => "submit",
        "name" => "btnView",
        "value" => "Ver"
    ];

    $api_url = Jaris\Uri::url(
        Jaris\Modules::getPageUri("api/biblia", "biblia")
    );

    $fields[] = [
        "type" => "other",
        "html_code" => '<script>'
            . '$(document).ready(function(){'
            . '$.bibliaNavigation('
            . '{'
            . 'api_url: "'.$api_url.'",'
            . 'form_name: "'.$form_name.'",'
            . 'biblia: "'.$biblia.'"'
            . '}'
            . ');'
            . '});'
            . '</script>'
    ];

    Jaris\View::addScript(
        Jaris\Modules::directory("biblia") . "scripts/navigation.js"
    );

    return $fields;
}

function biblia_get_title($bible)
{
    if (biblia_exists($bible)) {
        $db = biblia_open($bible);

        $result = Jaris\Sql::query("select * from info", $db);

        $data = Jaris\Sql::fetchArray($result);

        Jaris\Sql::close($db);

        if ($data) {
            return $data["nombre"] . " - " . $data["yyyy"];
        }
    }

    return "";
}

function biblia_open($bible)
{
    if (!file_exists(Jaris\Modules::directory("biblia") . "biblias/$bible")) {
        Jaris\Uri::go("biblia");
    }

    return Jaris\Sql::open(
        $bible,
        Jaris\Modules::directory("biblia") . "biblias"
    );
}

function biblia_exists($bible)
{
    return file_exists(
        Jaris\Modules::directory("biblia") . "biblias/$bible"
    );
}

function biblia_get_capitulos($libro, $biblia)
{
    $libros = biblia_get_libros_machine();

    $codigo = $libros[$libro][0];

    $db = biblia_open($biblia);

    $result = Jaris\Sql::query(
        "select capitulo from bible "
        . "where libro='$codigo' "
        . "group by capitulo "
        . "order by capitulo desc "
        . "limit 0,1",
        $db
    );

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data["capitulo"];
}

function biblia_get_versiculos($capitulo, $libro, $biblia)
{
    Jaris\Sql::escapeVar($capitulo, "int");

    $libros = biblia_get_libros_machine();

    $codigo = $libros[$libro][0];

    $db = biblia_open($biblia);

    $result = Jaris\Sql::query(
        "select versiculo from bible "
        . "where libro='$codigo' and "
        . "capitulo=$capitulo "
        . "group by versiculo "
        . "order by versiculo desc "
        . "limit 0,1",
        $db
    );

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data["versiculo"];
}

function biblia_get_capitulo($capitulo, $libro, $biblia)
{
    Jaris\Sql::escapeVar($capitulo, "int");

    $libros = biblia_get_libros_machine();

    $codigo = $libros[$libro][0];

    $db = biblia_open($biblia);

    $result = Jaris\Sql::query(
        "select versiculo, texto from bible "
        . "where capitulo=$capitulo and "
        . "libro = '$codigo' ",
        $db
    );

    while ($data = Jaris\Sql::fetchArray($result)) {
        yield $data;
    }

    Jaris\Sql::close($db);
}

function biblia_get_versiculo($versiculo, $capitulo, $libro, $biblia)
{
    Jaris\Sql::escapeVar($versiculo, "int");
    Jaris\Sql::escapeVar($capitulo, "int");

    $libros = biblia_get_libros_machine();

    $codigo = $libros[$libro][0];

    $db = biblia_open($biblia);

    $result = Jaris\Sql::query(
        "select versiculo, texto from bible "
        . "where versiculo=$versiculo and "
        . "capitulo=$capitulo and "
        . "libro = '$codigo' ",
        $db
    );

    while ($data = Jaris\Sql::fetchArray($result)) {
        yield $data;
    }

    Jaris\Sql::close($db);
}

function biblia_get_libro_label($libro, $biblia)
{
    $libros = biblia_get_libros_machine();

    return $libros[$libro][1];
}

function biblia_get_versiculo_text($versiculos)
{
    $versiculos_parts = explode("-", $versiculos);
    $versiculos_comma_parts = explode(",", $versiculos);
    $versiculos_text = "";

    if (count($versiculos_parts) > 1) {
        $versiculos_text = intval($versiculos_parts[0])
            . "-"
            . $versiculos_parts[1]
        ;
    } elseif (count($versiculos_comma_parts) > 1) {
        foreach ($versiculos_comma_parts as $value) {
            $versiculos_text .= intval($value) . ",";
        }

        $versiculos_text = rtrim($versiculos_text, ",");
    } else {
        $versiculos_text = intval($versiculos);
    }

    return $versiculos_text;
}

function biblia_get_previous_link($capitulo, $libro, $biblia)
{
    if ($capitulo > 1) {
        return '<a href="'
            . Jaris\Uri::url(
                Jaris\Modules::getPageUri("biblia", "biblia")
                . "/"
                . $biblia
                . "/"
                . $libro
                . "/"
                . ($capitulo-1)
            )
            . '">'
            . "&lt;&lt; "
            . biblia_get_libro_label($libro, $biblia)
            . " "
            . ($capitulo-1)
            . "</a>"
        ;
    } else {
        $libros = biblia_get_libros_machine();
        $codigo = $libros[$libro][0];
        $libros = biblia_get_libros();
        $prev = false;
        foreach ($libros as $libro_code => $libro_label) {
            if ($libro_code == $codigo) {
                break;
            }

            $prev = $libro_label;
        }

        if ($prev) {
            $libro_machine = preg_replace(
                "/ +/",
                "-",
                str_replace(
                    [
                        "á", "é", "í", "ó", "ú", "ñ",
                        "Á", "É", "Í", "Ó", "Ú", "Ñ",
                        "ü", "Ü", "-"
                    ],
                    [
                        "a", "e", "i", "o", "u", "n",
                        "a", "e", "i", "o", "u", "n",
                        "u", "u", " "
                    ],
                    trim(strtolower($prev))
                )
            );

            $capitulo =  biblia_get_capitulos($libro_machine, $biblia);

            return '<a href="'
                . Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $biblia
                    . "/"
                    . $libro_machine
                    . "/"
                    . $capitulo
                )
                . '">'
                . "&lt;&lt; "
                . $prev
                . " "
                . $capitulo
                . "</a>"
            ;
        }
    }

    return "<a>&nbsp;</a>";
}

function biblia_get_next_link($capitulo, $libro, $biblia)
{
    $capitulos_count = biblia_get_capitulos($libro, $biblia);

    if ($capitulo < $capitulos_count) {
        return '<a href="'
            . Jaris\Uri::url(
                Jaris\Modules::getPageUri("biblia", "biblia")
                . "/"
                . $biblia
                . "/"
                . $libro
                . "/"
                . ($capitulo+1)
            )
            . '">'
            . biblia_get_libro_label($libro, $biblia)
            . " "
            . ($capitulo+1)
            . " &gt;&gt;"
            . "</a>"
        ;
    } else {
        $libros = biblia_get_libros_machine();
        $codigo = $libros[$libro][0];
        $libros = biblia_get_libros();
        $next = false;
        $one_more = false;
        foreach ($libros as $libro_code => $libro_label) {
            if ($one_more) {
                $next = $libro_label;
                break;
            }

            if ($libro_code == $codigo) {
                $one_more = true;
            }
        }

        if ($next) {
            $libro_machine = preg_replace(
                "/ +/",
                "-",
                str_replace(
                    [
                        "á", "é", "í", "ó", "ú", "ñ",
                        "Á", "É", "Í", "Ó", "Ú", "Ñ",
                        "ü", "Ü", "-"
                    ],
                    [
                        "a", "e", "i", "o", "u", "n",
                        "a", "e", "i", "o", "u", "n",
                        "u", "u", " "
                    ],
                    trim(strtolower($next))
                )
            );

            return '<a href="'
                . Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $biblia
                    . "/"
                    . $libro_machine
                    . "/"
                    . "1"
                )
                . '">'
                . $next
                . " "
                . 1
                . " &gt;&gt;"
                . "</a>"
            ;
        }
    }

    return "<a>&nbsp;</a>";
}

function biblia_get_settings($type)
{
    $settings = [];
    if (!($settings = Jaris\Settings::get($type, "biblia"))) {
        $settings["enabled"] = false;
        $settings["biblia"] = "sagradas-escrituras-1569";
    } else {
        $settings = unserialize($settings);

        $settings["enabled"] = $settings["enabled"] ?
            $settings["enabled"]
            :
            false
        ;

        $settings["biblia"] = $settings["biblia"] ?
            $settings["biblia"]
            :
            "sagradas-escrituras-1569"
        ;
    }

    return $settings;
}

function biblia_convertir_versos($texto, $biblia="sagradas-escrituras-1569")
{
    $acentos = [
        "G\\&eacute;nesis",
        "\\&Eacute;xodo",
        "Lev\\&iacute;tico",
        "N\\&uacute;meros",
        "Josu\\&eacute;",
        "1 Cr\\&oacute;nicas",
        "2 Cr\\&oacute;nicas",
        "Nehem\\&iacute;as",
        "Eclesiast\\&eacute;s",
        "Isa\\&iacute;as",
        "Jerem\\&iacute;as",
        "Am\\&oacute;s",
        "Abd\\&iacute;as",
        "Jon\\&aacute;s",
        "Sofon\\&iacute;as",
        "Zacar\\&iacute;as",
        "Malaqu\\&iacute;as",
        "G\\&aacute;latas",
        "Filem\\&oacute;n",
        "Genesis",
        "Exodo",
        "Levitico",
        "Numeros",
        "Josue",
        "1 Cronicas",
        "2 Cronicas",
        "Nehemias",
        "Eclesiastes",
        "Isaias",
        "Jeremias",
        "Amos",
        "Abdias",
        "Jonas",
        "Sofonias",
        "Zacarias",
        "Malaquias",
        "Galatas",
        "Filemon"
    ];

    $books = biblia_get_libros();
    $books_union = implode("|", array_merge($books, $acentos));

    $content = preg_replace_callback(
        "/($books_union) +([0-9]+) *:* *([0-9\\-\\,]*)/",
        function ($matches) use ($biblia) {
            $libro_machine = preg_replace(
                "/ +/",
                "-",
                str_replace(
                    [
                        "&aacute;", "&eacute;", "&iacute;", "&oacute;", "&iacute;",
                        "á", "é", "í", "ó", "ú", "ñ",
                        "Á", "É", "Í", "Ó", "Ú", "Ñ",
                        "ü", "Ü", "-"
                    ],
                    [
                        "a", "e", "i", "o", "u",
                        "a", "e", "i", "o", "u", "n",
                        "a", "e", "i", "o", "u", "n",
                        "u", "u", " "
                    ],
                    trim(strtolower($matches[1]))
                )
            );

            if (count($matches) == 4 && !empty($matches[3])) {
                $link = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $biblia
                    . "/"
                    . $libro_machine
                    . "/"
                    . $matches[2]
                    . "/"
                    . $matches[3]
                );
            } elseif (count($matches) >= 3) {
                $link = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $biblia
                    . "/"
                    . $libro_machine
                    . "/"
                    . $matches[2]
                );
            }

            return '<a target="_blank" href="'.$link.'">'.$matches[0].'</a>';
        },
        $texto
    );

    return $content;
}

function biblia_get_libros_machine()
{
    $libros = [];

    foreach (biblia_get_libros() as $codigo => $libro) {
        $libro_machine = preg_replace(
            "/ +/",
            "-",
            str_replace(
                [
                    "á", "é", "í", "ó", "ú", "ñ",
                    "Á", "É", "Í", "Ó", "Ú", "Ñ",
                    "ü", "Ü", "-"
                ],
                [
                    "a", "e", "i", "o", "u", "n",
                    "a", "e", "i", "o", "u", "n",
                    "u", "u", " "
                ],
                trim(strtolower($libro))
            )
        );

        $libros[$libro_machine] = [
            $codigo,
            $libro
        ];
    }

    return $libros;
}

function biblia_get_libros()
{
    $libros = [
        "01O" => "Génesis",
        "02O" => "Éxodo",
        "03O" => "Levítico",
        "04O" => "Números",
        "05O" => "Deuteronomio",
        "06O" => "Josué",
        "07O" => "Jueces",
        "08O" => "Rut",
        "09O" => "1 Samuel",
        "10O" => "2 Samuel",
        "11O" => "1 Reyes",
        "12O" => "2 Reyes",
        "13O" => "1 Crónicas",
        "14O" => "2 Crónicas",
        "15O" => "Esdras",
        "16O" => "Nehemías",
        "17O" => "Ester",
        "18O" => "Job",
        "19O" => "Salmos",
        "20O" => "Proverbios",
        "21O" => "Eclesiastés",
        "22O" => "Cantares",
        "23O" => "Isaías",
        "24O" => "Jeremías",
        "25O" => "Lamentaciones",
        "26O" => "Ezequiel",
        "27O" => "Daniel",
        "28O" => "Oseas",
        "29O" => "Joel",
        "30O" => "Amós",
        "31O" => "Abdías",
        "32O" => "Jonás",
        "33O" => "Miqueas",
        "34O" => "Nahum",
        "35O" => "Habacuc",
        "36O" => "Sofonías",
        "37O" => "Hageo",
        "38O" => "Zacarías",
        "39O" => "Malaquías",
        "40N" => "Mateo",
        "41N" => "Marcos",
        "42N" => "Lucas",
        "43N" => "Juan",
        "44N" => "Hechos",
        "45N" => "Romanos",
        "46N" => "1 Corintios",
        "47N" => "2 Corintios",
        "48N" => "Gálatas",
        "49N" => "Efesios",
        "50N" => "Filipenses",
        "51N" => "Colosenses",
        "52N" => "1 Tesalonicenses",
        "53N" => "2 Tesalonicenses",
        "54N" => "1 Timoteo",
        "55N" => "2 Timoteo",
        "56N" => "Tito",
        "57N" => "Filemón",
        "58N" => "Hebreos",
        "59N" => "Santiago",
        "60N" => "1 Pedro",
        "61N" => "2 Pedro",
        "62N" => "1 Juan",
        "63N" => "2 Juan",
        "64N" => "3 Juan",
        "65N" => "Judas",
        "66N" => "Apocalipsis"
    ];

    return $libros;
}
