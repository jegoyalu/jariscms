<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

//Register autoloader for PHPMailer
include_once("src/PHPMailer/PHPMailerAutoload.php");

/**
 * Functions to send emails.
 */
class Mail
{

/**
 * Sends an email using phpmailer with system configurations
 * for mail on admin/settings/mailer.
 *
 * @param array $to In the format to["John Smith"] = "jsmith@domain.com".
 * @param string $subject
 * @param string $html_message html code to send
 * @param string $alt_message optional plain text message in case email client doesn't supports html
 * @param array $attachments files path list to attach
 * @param array $reply_to In the format reply_to["John Smith"] = "jsmith@domain.com".
 * @param array $bcc In the format bcc["John Smith"] = "jsmith@domain.com"
 * @param array $cc In the format cc["John Smith"] = "jsmith@domain.com"
 * @param array $from In the format cc["John Smith"] = "jsmith@domain.com"
 *
 * @return bool True if sent false if not.
 * @original send_email
 */
static function send(
    $to, $subject, $html_message, $alt_message = null, $attachments = array(),
    $reply_to = array(), $bcc = array(), $cc = array(), $from = array()
)
{
    $mail = new \PHPMailer();

    $lang = Language::getCurrent();

    if($lang != "en")
    {
        $mail->setLanguage(
            $lang,
            "include/third_party/phpmailer/language/"
        );
    }

    $sender = Settings::get('mailer_from_email', 'main');

    $mail->isHTML();
    $mail->CharSet = "utf-8";
    $mail->Subject = $subject;
    $mail->AltBody = $alt_message;
    $mail->msgHTML($html_message);
    $mail->WordWrap = 50;
    $mail->Sender = $sender;

    if(count($from) > 0)
    {
        foreach($from as $from_name => $from_email)
        {
            $mail->setFrom($from_email, $from_name);

            break;
        }
    }
    else
    {
        $mail->setFrom(
            $sender,
            Settings::get('mailer_from_name', 'main')
        );
    }

    switch(Settings::get('mailer', 'main'))
    {
        case 'sendmail':
            $mail->isSendmail();
            break;
        case 'smtp':{
            include_once("include/third_party/phpmailer/class.smtp.php");

            $mail->isSMTP();

            $mail->SMTPAuth = (bool) Settings::get('smtp_auth', 'main');
            if(Settings::get('smtp_ssl', 'main'))
            {
                $mail->SMTPSecure = 'ssl';
            }
            $mail->Host = Settings::get('smtp_host', 'main');
            $mail->Port = intval(Settings::get('smtp_port', 'main'));

            $mail->Username = Settings::get('smtp_user', 'main');
            $mail->Password = Settings::get('smtp_pass', 'main');
            break;
        }
        default:
            $mail->isMail();
    }

    foreach($reply_to as $name => $email)
    {
        $mail->addReplyTo($email, $name);
    }

    //Add email addresses
    foreach($to as $name => $email)
    {
        $mail->addAddress($email, $name);
    }

    //Add hidden carbon copies
    foreach($bcc as $name => $email)
    {
        $mail->addBCC($email, $name);
    }

    //Add carbon copies
    foreach($cc as $name => $email)
    {
        $mail->addCC($email, $name);
    }

    foreach($attachments as $file_name => $file_path)
    {
        if(!is_int($file_name))
            $mail->addAttachment($file_path, $file_name);
        else
            $mail->addAttachment($file_path);
    }

    return $mail->send();
}

/**
 * Sends user an email ntofication when he or she resets a password.
 *
 * @param string $username The current name used to log mailed also to the user.
 * @param array $user_data All the user data including its full name, email, etc.
 * @param string $password The new password wich will the user be able to log in again.
 *
 * @return bool True on succes or false on fail.
 * @original send_user_reset_password_notification
 */
static function sendPasswordNotification($username, $user_data, $password)
{
    $username = strtolower($username);
    $to[$user_data["name"]] = $user_data["email"];
    $subject = t("Your password has been reset.");

    $url = Uri::url("admin/user");

    $message = t("Hi") . " " . $user_data["name"] . "<br /><br />";
    $message .= t("Your current username is:") . " <b>" . $username . "</b><br />";
    $message .= t("The new password for your account is:") . " <b>" . $password . "</b><br />";
    $message .= t("Is recommended that you log in and change the password as soon as possible.") . "<br />";
    $message .= t("To log in access the following url:") . " <a href=\"$url\">" . $url . "</a>";

    return self::send($to, $subject, $message);
}

/**
 * Sends an email notification to all administrators when a
 * new user is registered and the registration needs approval
 * flag is turned on. Used on the register page.
 *
 * @param string $username
 * @original send_registration_notification
 */
static function sendRegistrationNotification($username)
{
    $user_data = Users::get($username);

    $select = "select * from users where user_group='administrator'";

    $db = Sql::open("users");

    $result = Sql::query($select, $db);

    $to = array();
    while($data = Sql::fetchArray($result))
    {
        $admin_data = Users::get($data["username"]);
        $to[$admin_data["name"]] = $data["email"];
    }

    Sql::close($db);

    $html_message = t("A new account has been created and is pending for administration approval.") . "<br /><br />";
    $html_message .= "<b>" . t("Fullname:") . "</b>" . " " . $user_data["name"] . "<br />";
    $html_message .= "<b>" . t("Username:") . "</b>" . " " . $username . "<br />";
    $html_message .= "<b>" . t("E-mail:") . "</b>" . " " . $user_data["email"] . "<br /><br />";
    $html_message .= t("For more details or approve this registration visit the users management page:") . "<br />";

    $html_message .= "<a target=\"_blank\" href=\"" .
        Uri::url("admin/user", array("return" => "admin/users/list")) .
        "\">" . Uri::url("admin/user", array("return" => "admin/users/list")) .
        "</a>"
    ;

    self::send($to, t("New registration pending for approval"), $html_message);
}

/**
 * Sends an email notification to all administrators when
 * new content is published that requires approval.
 *
 * @param string $uri
 * @param string $type
 */
static function sendContentApproveNotification($uri, $type)
{
    $page_data = Pages::get($uri);

    $select = "select * from users where user_group='administrator'";

    $db = Sql::open("users");

    $result = Sql::query($select, $db);

    $to = array();
    while($data = Sql::fetchArray($result))
    {
        $admin_data = Users::get($data["username"]);
        $to[$admin_data["name"]] = $data["email"];
    }

    Sql::close($db);

    $html_message = t("New content has been created and is pending for administration approval.") . "<br /><br />";
    $html_message .= "<b>" . t("Content Title:") . "</b>" . " " . $page_data["title"] . "<br />";
    $html_message .= "<b>" . t("Content Type:") . "</b>" . " " . $page_data["type"] . "<br />";
    $html_message .= "<b>" . t("Submitted by:") . "</b>" . " " . $page_data["author"] . "<br />";
    $html_message .= t("For more details or to approve this content visit the approve content page:") . "<br /><br />";

    $html_message .= "<a target=\"_blank\" href=\"" .
        Uri::url("admin/user", array("return" => "admin/pages/approve")) .
        "\">" . Uri::url("admin/user", array("return" => "admin/pages/approve")) .
        "</a>"
    ;

    self::send($to, t("New content pending for approval"), $html_message);
}

static function sendEmailActivation($username_or_email)
{
    $user_data = array();
    $username = $username_or_email;

    if(strstr($username_or_email, "@") !== false)
    {
        $user_data = Users::getByEmail($username_or_email);
        $username = $user_data["username"];
    }
    else
    {
        $user_data = Users::get($username_or_email);
    }

    $activation_code = Users::generatePassword(12);

    $user_data["activation_code"] = $activation_code;
    $user_data["email_activated"] = "0";

    if(Users::edit($username, $user_data["group"], $user_data) !== "true")
        return false;

    $html_message = t("To finish the registration process please click the following link to activate your account:")
        . "<br /><br />"
    ;

    $html_message .= "<a target=\"_blank\" href=\""
        . Uri::url(
            "account/activate",
            array(
                "u" => $username,
                "c" => $activation_code
            )
        )
        . "\">"
        . Uri::url(
            "account/activate",
            array(
                "u" => $username,
                "c" => $activation_code
            )
        )
        . "</a>"
    ;

    $to = array(
        $user_data["name"] => $user_data["email"]
    );

    return self::send($to, t("Account Activation"), $html_message);
}

static function sendWelcomeMessage($username_or_email)
{
    $message = Settings::get("registration_welcome_message", "main");

    if(trim($message) == "")
    {
        return true;
    }

    $user_data = array();
    $username = $username_or_email;

    if(strstr($username_or_email, "@"))
    {
        $user_data = Users::getByEmail($username_or_email);
        $username = $user_data["username"];
    }
    else
    {
        $user_data = Users::get($username_or_email);
    }

    $html_message = str_replace(
        array(
            "{name}",
            "{username}",
            "{email}",
            "{gender}",
            "{group}"
        ),
        array(
            $user_data["name"],
            $username,
            $user_data["email"],
            $user_data["gender"],
            $user_data["group"]
        ),
        $message
    );

    $html_message = System::evalPHP(
        $html_message
    );

    $to = array(
        $user_data["name"] => $user_data["email"]
    );

    return self::send($to, t("Welcome!"), $html_message);
}

}
