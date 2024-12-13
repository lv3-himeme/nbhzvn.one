<?php
$new_game_webhook_url = $_ENV["DISCORD_NEW_GAME_WEBHOOK"]; $moderation_webhook_url = $_ENV["DISCORD_MODERATION_WEBHOOK"];
require __DIR__ . "/../../webhook_config.php";

function process_webhook_mentions($type) {
    global $webhook_config;
    $mentions = [];
    $config = $webhook_config[$type];
    if ($config["allowed_mentions"]) {
        if ($config["allowed_mentions"]["roles"]) {
            foreach ($config["allowed_mentions"]["roles"] as $role) $mentions[] = "<@&" . $role . ">";
        }
        if ($config["allowed_mentions"]["users"]) {
            foreach ($config["allowed_mentions"]["users"] as $user) $mentions[] = "<@" . $user . ">";
        }
    }
    return implode(" ", $mentions);
}

function process_webhook_tags(Nbhzvn_Game $game) {
    global $webhook_config;
    $tags = [];
    $config = $webhook_config["new_game"];
    if ($config["tags"]) {
        if ($config["tags"]["engine"]) {
            $tag = $config["tags"]["engine"][$game->engine];
            if ($tag) $tags[] = $tag;
        }
        if ($config["tags"]["language"]) {
            $tag = $config["tags"]["language"][$game->language];
            if ($tag) $tags[] = $tag;
        }
        if ($config["tags"]["os"]) {
            $pc = false; $mobile = false;
            foreach (explode(",", $game->supported_os) as $os) {
                if (in_array($os, [OS_WINDOWS, OS_MACOS, OS_LINUX]) && !$pc) {
                    $tag = $config["tags"]["os"]["pc"];
                    if ($tag) $tags[] = $tag;
                    $pc = true;
                }
                if (in_array($os, [OS_ANDROID, OS_IOS]) && !$mobile) {
                    $tag = $config["tags"]["os"]["mobile"];
                    if ($tag) $tags[] = $tag;
                    $mobile = true;
                }
            }
        }
    }
    return $tags;
}

function send_moderation_webhook(Nbhzvn_Game $game) {
    global $moderation_webhook_url;
    if (!$moderation_webhook_url) return FAILED;
    $mentions = process_webhook_mentions("moderation");
    $webhook = new Discord_Webhook($moderation_webhook_url);
    $http = (empty($_SERVER["HTTPS"]) ? "http" : "https");
    $host = $_SERVER["HTTP_HOST"];
    $site = $http . "://" . $host;
    $message = new Discord_Message();
    $message->content = $mentions . " Một game mới vừa mới được tải lên và đang chờ Quản Trị Viên phê duyệt.";
    $row = new Discord_ActionRow();
    $approve_button = new Discord_Button();
    $approve_button->style = BUTTON_STYLE_LINK;
    $approve_button->url = $site . "/approve/" . $game->id;
    $approve_button->emoji = new Discord_Emoji(null, "✅", false);
    $approve_button->label = "Phê Duyệt";
    $delete_button = new Discord_Button();
    $delete_button->style = BUTTON_STYLE_LINK;
    $delete_button->url = $site . "/delete_game/" . $game->id;
    $delete_button->emoji = new Discord_Emoji(null, "❎", false);
    $delete_button->label = "Xoá";
    $row->add_components($approve_button, $delete_button);
    $message->add_components($row);
    $embed = $game->discord_embed();
    $embed->image = new Discord_EmbedImage($site . "/uploads/" . $game->image);
    $message->add_embeds($embed);
    $result = $webhook->send($message);
    if ($result == "") return SUCCESS;
    else return FAILED;
}

function send_newgame_webhook(Nbhzvn_Game $game) {
    global $new_game_webhook_url;
    if (!$new_game_webhook_url) return FAILED;
    $mentions = process_webhook_mentions("new_game");
    $webhook = new Discord_Webhook($new_game_webhook_url);
    $http = (empty($_SERVER["HTTPS"]) ? "http" : "https");
    $host = $_SERVER["HTTP_HOST"];
    $site = $http . "://" . $host;
    $message = new Discord_Message();
    $message->thread_name = $game->name;
    $message->applied_tags = process_webhook_tags($game);
    $row = new Discord_ActionRow();
    $button = new Discord_Button();
    $button->style = BUTTON_STYLE_LINK;
    $button->url = $site . "/games/" . $game->id . "#downloadSection";
    $button->label = "Tải Xuống";
    $row->add_components($button);
    $message->add_components($row);
    $message->add_embeds($game->discord_embed());
    $message->content = $mentions;
    $img_message = new Discord_Message();
    $img_message->content = $site . "/uploads/" . $game->image;
    $img_result = $webhook->send($img_message);
    $result = $webhook->send($message);
    if ($img_result == "" && $result == "") return SUCCESS;
    else return FAILED;
}
?>