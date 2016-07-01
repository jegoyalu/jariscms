<?php exit; ?>


row: 0

	field: title
		<?php print t("Delete Contact Form Field") ?>
	field;

	field: content
		<?php
		Jaris\Authentication::protectedPage(array("edit_content"));
		
		if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
		{
		    Jaris\Authentication::protectedPage();
		}
		
		$field_data = contact_get_field_data($_REQUEST["id"], $_REQUEST["uri"]);
		
		if(isset($_REQUEST["btnYes"]))
		{
		    if(contact_delete_field($_REQUEST["id"], $_REQUEST["uri"]))
		    {
		        Jaris\View::addMessage(t("Contact form field successfully deleted."));
		    }
		    else
		    {
		        Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
		    }
		
		    Jaris\Uri::go(
		        Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
		        array("uri" => $_REQUEST["uri"])
		    );
		}
		elseif(isset($_REQUEST["btnNo"]))
		{
		    Jaris\Uri::go(
		        Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
		        array("uri" => $_REQUEST["uri"])
		    );
		}
		    ?>
		
		    <form class="contact-form-field-delete" method="post"
		  action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("admin/pages/contact-form/fields/delete", "contact")) ?>"
		    >
		<input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
		<input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
		<br />
		<div>
		    <?php print t("Are you sure you want to delete the field?") ?>
		    <div>
		        <b>
		            <?php print t("Field:") ?>
		            <?php print t($field_data["name"]) ?>
		        </b>
		    </div>
		</div>
		<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
		<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
		    </form>
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


