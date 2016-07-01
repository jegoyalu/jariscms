

    <tr>
        <?php if($content_data["thumbnail_show"]){ ?>
        <td class="image"><?php print $image ?></td>
        <?php } ?>

        <?php if($content_data["display_title"] || $content_data["display_summary"]){ ?>
        <td class="preview">
            <div class="title"><?php print $title ?></div>
            <div class="summary"><?php print $summary ?></div>
        </td>
        <?php } ?>

        <?php if($content_data["display_more"]){ ?>
        <td class="view-more"><?php print $view_more ?></td>
        <?php } ?>
    </tr>