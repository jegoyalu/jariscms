<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function (&$content, &$content_title, &$content_data) {
        if (!Jaris\Pages::isSystem("", $content_data)) {
            $settings = Jaris\Settings::getAll("video_embed");

            $settings["content_types"] = unserialize(
                $settings["content_types"]
            );

            if (
                !empty($settings["content_types"])
                &&
                !in_array($content_data["type"], $settings["content_types"])
            ) {
                return;
            }

            $width = !empty($settings["default_width"]) ?
                $settings["default_width"]
                :
                640
            ;

            $height = !empty($settings["default_height"]) ?
                $settings["default_height"]
                :
                385
            ;

            video_embed_parse_videos($content, $width, $height);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/settings") {
            $tabs_array[0][t("Video Embed")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/video-embed",
                    "video_embed"
                ),
                "arguments" => []
            ];
        }
    }
);

function video_embed_parse_videos(&$content, $width=640, $height=385)
{
    //Youtube
    $content = preg_replace(
        '%                # Match any youtube URL in the wild.
        (?:https?://)?    # Optional scheme. Either http or https
        (?:www\.)?        # Optional www subdomain
        (?:               # Group host alternatives
          youtu\.be/      # Either youtu.be,
        | youtube\.com    # or youtube.com
          (?:             # Group path alternatives
            /embed/       # Either /embed/
          | /v/           # or /v/
          | /watch\?v=    # or /watch\?v=
          )               # End path alternatives.
        )                 # End host alternatives.
        ([\w\-]{10,12})   # Allow 10-12 for 11 char youtube id.
        \b                # Anchor end to word boundary.
        %x',
        '<iframe class="youtube-player" type="text/html" width="'.$width.'" height="'.$height.'" src="//www.youtube.com/embed/$1?rel=0" frameborder="0" allowfullscreen></iframe>',
        $content
    );

    //Vimeo ex: http://vimeo.com/34267
    $content = preg_replace(
        "%
        (?:https?://)?
        (?:www\.)?
        vimeo\.com/
        (\d+)
        \b
        %x",
        '<iframe src="//player.vimeo.com/video/$1" width="'.$width.'" height="'.$height.'" frameborder="0" allowfullscreen></iframe>',
        $content
    );

    //Videozer ex: http://www.videozer.com/video/CA3TwM
    $content = preg_replace(
        '%
        (?:https?://)?
        (?:www\.)?
        videozer\.com/video/
        ([a-zA-Z\d]+)
        \b
        %x',
        '<object id="player" width="'.$width.'" height="'.$height.'" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ><param name="movie" value="//www.videozer.com/embed/$1" ></param><param name="allowFullScreen" value="true" ></param><param name="allowscriptaccess" value="always"></param><embed src="//www.videozer.com/embed/$1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>',
        $content
    );

    //Videobb ex: http://www.videobb.com/video/Z6316GH7usKM or http://www.videobb.com/watch_video.php?v=Z6316GH7usKM
    $content = preg_replace(
        '%
        (?:https?://)?              # Optional scheme. Either http or https
        (?:www\.)?                  # Optional www subdomain
        (?:                         # Group host alternatives
          videobb\.com              # videobb.com
          (?:                       # Group path alternatives
            /video/                 # Either /video/
          | /watch_video\.php\?v=   # or /watch_video.php?v=
          )                         # End path alternatives.
        )                           # End host alternatives.
        ([A-Za-z\d]+)               # Video id.
        \b                          # Anchor end to word boundary.
        %x',
        '<object id="player" width="'.$width.'" height="'.$height.'" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ><param name="movie" value="//www.videobb.com/e/$1" ></param><param name="allowFullScreen" value="true" ></param><param name="allowscriptaccess" value="always"></param><embed src="//www.videobb.com/e/$1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>',
        $content
    );

    //Dailymotion ex: http://www.dailymotion.com/video/xmts74_dirty-radio-ground-shake_music
    preg_match_all(
        '%
        (?:https?://)?              # Optional scheme. Either http or https
        (?:www\.)?
        dailymotion\.com/video/
        ([A-Za-z\d\-\_]+)
        (?:\#[A-Za-z\d\-\_]+)?
        \b                          # Anchor end to word boundary.
        %x',
        $content,
        $daily_matches
    );

    foreach ($daily_matches[1] as $id => $daily_match) {
        $dailymotion_id = strtok($daily_match, "_");

        $content = str_replace(
            $daily_matches[0][$id],
            '<iframe frameborder="0" width="'.$width.'" height="'.$height.'" src="//www.dailymotion.com/embed/video/' . $dailymotion_id . '" allowfullscreen></iframe>',
            $content
        );
    }

    //Megavideo ex: http://www.megavideo.com/?v=DX449QLU
    $content = preg_replace(
        '%
        (?:https?://)?
        (?:www\.)?
        megavideo\.com/\?v=
        ([a-zA-Z\d]+)
        \b
        %x',
        '<object width="'.$width.'" height="'.$height.'"><param name="movie" value="//www.megavideo.com/v/$1"></param><param name="allowFullScreen" value="true"></param><embed src="//www.megavideo.com/v/$1" type="application/x-shockwave-flash" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>',
        $content
    );
}
