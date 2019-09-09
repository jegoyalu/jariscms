<?php exit; ?>


row: 0

	field: title
		Control menu style
	field;

	field: content
		<?php
		if(!Jaris\Authentication::isUserLogged())
		    return;
		
		$menu_colors = Jaris\Settings::getAll("control_menu");
		    ?>
		    <?php if(isset($menu_colors["main_bar_background"])){ ?>
		/*<style>*/
		#control-menu
		{
		    background-color: #<?php print $menu_colors["main_bar_background"] ?>;
		    border: solid 1px #<?php print $menu_colors["main_bar_border"] ?>;
		}
		
		#control-menu .user
		{
		    background-color: #<?php print $menu_colors["user_button"] ?>;
		    color: #<?php print $menu_colors["user_button_text"] ?>;
		}
		
		#control-menu .right a:hover
		{
		    background-color: #<?php print $menu_colors["image_hover"] ?>;
		}
		
		#control-menu .about
		{
		    background: transparent url(<?php print Jaris\Uri::url(Jaris\Modules::directory("control_menu") . "styles/about" . $menu_colors["image_color"] . ".png") ?>) no-repeat center center;
		}
		
		#control-menu .help
		{
		    background: transparent url(<?php print Jaris\Uri::url(Jaris\Modules::directory("control_menu") . "styles/help" . $menu_colors["image_color"] . ".png") ?>) no-repeat center center;
		}
		
		#control-menu .logout
		{
		    background: transparent url(<?php print Jaris\Uri::url(Jaris\Modules::directory("control_menu") . "styles/logout" . $menu_colors["image_color"] . ".png") ?>) no-repeat center center;
		}
		
		#control-menu ul li a
		{
		    color: #<?php print $menu_colors["main_menu_text"] ?>;
		}
		
		#control-menu ul li a:hover
		{
		    color: #<?php print $menu_colors["main_menu_text_hover"] ?>;
		    background-color: #<?php print $menu_colors["main_menu_background_hover"] ?>;
		}
		
		#control-menu ul li ul
		{
		    background-color: #<?php print $menu_colors["submenu_background"] ?>;
		
		    border-top: solid 1px #<?php print $menu_colors["submenu_border"] ?>;
		    border-left: solid 1px #<?php print $menu_colors["submenu_border"] ?>;
		    border-right: solid 1px #<?php print $menu_colors["submenu_border"] ?>;
		}
		
		#control-menu ul li ul li a
		{
		    color: #<?php print $menu_colors["submenu_text"] ?>;
		    border-bottom: dotted 1px #<?php print $menu_colors["submenu_text_border"] ?>;
		}
		
		#control-menu ul li ul li a:hover
		{
		    color: #<?php print $menu_colors["submenu_text_hover"] ?>;
		    background-color: #<?php print $menu_colors["submenu_text_background_hover"] ?>;
		}
		/*</style>*/
		    <?php } ?>
	field;

	field: rendering_mode
		css
	field;

	field: is_system
		1
	field;

	field: users
		N;
	field;

	field: groups
		N;
	field;

	field: categories
		N;
	field;

row;


