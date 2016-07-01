<div style="margin-top: 15px;"></div>

<div class="content">

<?php if($header){?><div class="content-header"><?php print $header ?></div><?php } ?>

    <table>
        <tr>
            <?php if($left){?><td class="content-left"><?php print $left ?></td><?php } ?>
            <td class="content">
                <?php if($center){?>
                <div class="content-center">
                    <?php print $center ?>
                </div>
                <?php } ?>


                <div>
                    <?php
                        print $content;

                        $content_data["option_name"] = unserialize($content_data["option_name"]);
                        $content_data["option_value"] = unserialize($content_data["option_value"]);
                    ?>

                    <?php
                        if(!isset($_COOKIE["poll"][Jaris\Uri::get()]) && !poll_expired(Jaris\Uri::get()))
                        {
                            $poll_data = array();
                            foreach($content_data["option_name"] as $id=>$name)
                            {
                                $poll_data[t($name)] = $id;
                            }

                            $parameters["class"] = "poll-vote";
                            $parameters["action"] = Jaris\Uri::url(Jaris\Modules::getPageUri("admin/polls/vote", "polls"));
                            $parameters["method"] = "get";

                            $fields[] = array("type"=>"hidden", "name"=>"uri", "id"=>"uri", "value"=>Jaris\Uri::get());
                            $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "id"=>"actual_uri", "value"=>Jaris\Uri::get());
                            $fields[] = array("type"=>"radio", "name"=>"id", "id"=>"id", "value"=>$poll_data, "horizontal_list"=>true);
                            $fields[] = array("type"=>"submit", "value"=>t("Vote"));

                            $fieldset[] = array("fields"=>$fields);

                            print Jaris\Forms::generate($parameters, $fieldset);

                            print "<hr />";
                        }
                    ?>

                    <h2><?php print t("Results:") ?></h2>

                    <?php
                        $total_votes = 0;
                        foreach($content_data["option_value"] as $value)
                        {
                            $total_votes += $value;
                        }

                        $option_percent = array();
                        foreach($content_data["option_value"] as $value)
                        {
                            if($value <= 0)
                            {
                                $option_percent[] = 0;
                            }
                            else
                            {
                                $option_percent[] = floor(($value / $total_votes) * 100);
                            }
                        }

                        for($i=0; $i<count($content_data["option_name"]); $i++)
                        {
                            print "<h4>" . t($content_data['option_name'][$i]) . "</h4>\n";

                            if($content_data['option_value'][$i] != 0)
                            {
                                print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}% " . $content_data['option_value'][$i] . " " . t("of") . " " . $total_votes . " " . t("votes") . "</div>\n";
                            }
                            else
                            {
                                print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}%</div>\n";
                            }

                        }
                    ?>

                    <br /><br />

                    <b><?php print t("Total votes:") ?></b> <?php print $total_votes ?>

                </div>

            </td>
            <?php if($right){?><td class="content-right"><?php print $right ?></td><?php } ?>
        </tr>
    </table>

<?php if($footer){?><div class="content-footer"><?php print $footer ?></div><?php } ?>

</div>