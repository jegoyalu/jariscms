<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
*/
?>
<!DOCTYPE html>
<html lang="<?php print get_current_language() ?>">

<head>
<title><?php print $title; ?></title>
<?php print $header_info ?>
<?php print $meta ?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<?php print $styles ?>
<?php print $scripts ?>
</head>

<body>

<div id="mobile-menu">
    <div class="container">
        <div class="login">
            <a href="<?php print print_url("admin/user") ?>">
            <?php print t("My Account") ?>
            </a>
        </div>
        <div class="menu">
            <a>
                <img src="<?php print $theme_path ?>/images/mobile/menu.png" />
            </a>
        </div>
    </div>
</div>

<div id="mobile-main-menu">
    <?php print $primary_links ?>
</div>

<!--Header-->
<div id="pre-header">
    <div class="container">
        <div class="language">
            <?php
                $en_active="";
                $en_active="";
                if (get_current_language() == "en") {
                    $en_active .= 'class="active"';
                } else {
                    $es_active .= 'class="active"';
                }
            ?>
            <a <?=$en_active?> href="?language=en">EN</a>
            <a <?=$es_active?> href="?language=es">ES</a>
        </div>
        <div class="search">
            <form action="<?php print $base_url ?>/search">
                <input type="hidden" name="search" value="1" />
                <input type="text" name="keywords" placeholder="<?php print t("search...") ?>" />
                <input type="image" src="<?php print $theme_path ?>/images/search.png" />
            </form>
        </div>
        <div class="account">
            <a href="<?php print $base_url ?>/admin/user">
            <?php if (!is_user_logged()) { ?>
                <div><?php print t("Sign-in") ?></div>
                <div><?php print t("Your Account") ?></div>
            <?php } else { ?>
                <div><?php print t("Hello,") ?></div>
                <div><?php print get_user_data(current_user())["name"] ?></div>
            <?php } ?>
            </a>
        </div>
        <?php if (is_module_installed("ecommerce")) { ?>
        <div class="cart">
            <a title="cart" href="<?php print $base_url ?>/cart">
                <span>
                    <?php print ecommerce_cart_get_products_count() ?>
                </span>
            </a>
        </div>
        <?php } ?>
    </div>
</div>

<table id="header">
    <tr>
        <td class="logo">
            <a title="home" href="<?php print $base_url ?>/">
                <img alt="JarisCMS" src="<?php print $theme_path ?>/images/logo.png" />
            </a>
        </td>
        <td class="options">
            <div class="top">
                <ul>
                    <li>
                        <a href="<?php print $base_url ?>/">
                            <img src="<?php print $theme_path ?>/images/home.png" />
                        </a>
                    </li>
                </ul>
                <?php print $secondary_links ?>
            </div>
            <div class="bottom">
                <?php print $primary_links ?>
            </div>
        </td>
    </tr>
</table>

<?php if ($header) { ?>
<div id="header-blocks">
    <?php echo $header; ?>
</div>
<?php } ?>

<?php if ($center) { ?>
<div id="center-blocks">
    <?php echo $center; ?>
</div>
<?php } ?>

<?php if ((get_uri() != "home" && get_uri() != get_setting("home_page", "main")) || is_admin_logged()) { ?>
<table id="content">
    <tr>
        <?php if ($left) { ?>
        <td id="left-blocks" class="left">
            <?php echo $left; ?>
        </td>
        <?php }?>

        <td class="center">
            <h1><?php print $content_title; ?></h1>

            <?php if ($breadcrumb) {?>
            <div id="breadcrumb"><?php print $breadcrumb; ?></div>
            <?php } ?>

            <?php if ($messages) {?>
            <div id="messages"><?php print $messages; ?></div>
            <?php } ?>

            <?php if ($tabs) {?>
            <div id="tabs-menu"><?php print $tabs; ?></div>
            <?php } ?>

            <?php print $content; ?>
        </td>

        <?php if ($right) { ?>
        <td id="right-blocks" class="right">
            <?php echo $right; ?>
        </td>
        <?php }?>
    </tr>
</table>
<?php } ?>

<?php if ($footer) { ?>
<div id="footer-blocks">
    <div class="container">
        <?php echo $footer; ?>
    </div>
</div>
<?php } ?>

<div id="pre-footer">
    <div class="mailing">
        <img src="<?php print $theme_path ?>/images/rocket.png" />
        <div class="text">
            <div><?php print t("Receive Tips and Updates") ?></div>
            <div><?php print t("right at your inbox it's free") ?></div>
        </div>
        <div class="form">
            <form action="" method="GET">
                <input type="text" name="email" placeholder="<?php print t("Enter your email") ?>" />
                <input type="submit" value="<?php print t("Yes! Suscribe Me") ?> »" />
            </form>
        </div>
    </div>
    <div class="links">
        <div>
            <h4>Jaris</h4>
            <?php print theme_links(sort_data(get_menu_items_list("about"), "order"), "about") ?>
        </div>
        <div>
            <h4><?php print t("Extend") ?></h4>
            <?php print theme_links(sort_data(get_menu_items_list("extend"), "order"), "extend") ?>
        </div>
        <div>
            <h4><?php print t("Support") ?></h4>
            <?php print theme_links(sort_data(get_menu_items_list("support"), "order"), "support") ?>
        </div>
        <div>
            <h4><?php print t("Follow US") ?></h4>
            <div>
                <a title="facebook" href="https://facebook.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/facebook.png" />
                </a>
                <a title="twitter" href="https://twitter.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/twitter.png" />
                </a>
                <a title="youtube" href="https://youtube.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/youtube.png" />
                </a>
                <a title="instagram" href="https://instagram.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/instagram.png" />
                </a>
                <a title="google plus" href="https://plus.google.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/google.png" />
                </a>
                <a title="linkedin" href="https://linkedin.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/linkedin.png" />
                </a>
                <a title="soundcloud" href="https://soundcloud.com/" target="_blank">
                    <img src="<?php print $theme_path ?>/images/soundcloud.png" />
                </a>
                <a title="rss" href="<?php print $base_url ?>/rss">
                    <img src="<?php print $theme_path ?>/images/rss.png" />
                </a>
            </div>
        </div>
    </div>
</div>

<div id="footer">
    <div class="copyright">
        <?php echo $footer_message; ?>
    </div>

    <div class="developer">
        <a href="http://jegoyalu.com/" target="_blank">
            <?php print t("Developed by JegoYalu") ?>
        </a>
    </div>
</div>

</body>

</html>
