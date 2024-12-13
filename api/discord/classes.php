<?php
/*
=================================================================
I prefer writing my own API to using external ones.
However this one is incomplete and only be used for supporting
basic webhooks sending, if you want more proper support you
can use external libraries or extend these classes below.
=================================================================
*/

const BUTTON_STYLE_PRIMARY = 1;
const BUTTON_STYLE_SECONDARY = 2;
const BUTTON_STYLE_SUCCESS = 3;
const BUTTON_STYLE_DANGER = 4;
const BUTTON_STYLE_LINK = 5;
const BUTTON_STYLE_PREMIUM = 6;

class Discord_EmbedImage {
    public $url;
    public $proxy_url;
    public $height;
    public $width;

    function __construct(string $url) {
        $this->url = $url;
    }
}

class Discord_EmbedVideo {
    public $url;
    public $proxy_url;
    public $height;
    public $width;

    function __construct(string $url) {
        $this->url = $url;
    }
}

class Discord_EmbedProvider {
    public $name;
    public $url;

    function __construct(string $name, string $url) {
        $this->name = $name;
        $this->url = $url;
    }
}

class Discord_EmbedAuthor {
    public $name;
    public $url;
    public $icon_url;
    public $proxy_url;

    function __construct(string $name) {
        $this->name = $name;
    }
}

class Discord_EmbedFooter {
    public $text;
    public $icon_url;
    public $proxy_url;

    function __construct(string $text) {
        $this->text = $text;
    }
}

class Discord_EmbedField {
    public $name;
    public $value;
    public $inline;

    function __construct(string $name, string $value, bool $inline = false) {
        $this->name = $name;
        $this->value = $value;
        $this->inline = $inline;
    }
}

class Discord_Embed {
    public $title;
    public $type;
    public $description;
    public $url;
    public $timestamp;
    public $color;
    public Discord_EmbedFooter $footer;
    public Discord_EmbedImage $image;
    public Discord_EmbedImage $thumbnail;
    public Discord_EmbedVideo $video;
    public Discord_EmbedProvider $provider;
    public Discord_EmbedAuthor $author;
    public $fields = [];

    function __construct() {
        $this->type = "rich";
        $this->color = random_int(0, 16777215);
        $this->fields = [];
    }

    function add_fields(Discord_EmbedField ...$fields) {
        foreach ($fields as $field) $this->fields[] = $field;
    }
}

class Discord_Emoji {
    public $id;
    public $name;
    public $animated;

    function __construct($id, $name, $animated) {
        $this->id = $id;
        $this->name = $name;
        $this->animated = $animated;
    }
}

class Discord_ActionRow {
    public $type = 1;
    public $components;

    function add_components(...$components) {
        foreach ($components as $component) $this->components[] = $component;
    }
}

class Discord_Button {
    public $type = 2;
    public $style;
    public $label;
    public Discord_Emoji $emoji;
    public $custom_id;
    public $sku_id;
    public $url;
}

class Discord_Message {
    public $content;
    public $username;
    public $avatar_url;
    public $tts;
    public $embeds;
    public object $allowed_mentions;
    public $components;
    public $thread_name;
    public $applied_tags;

    function add_components(...$components) {
        foreach ($components as $component) $this->components[] = $component;
    }

    function add_embeds(Discord_Embed ...$embeds) {
        foreach ($embeds as $embed) $this->embeds[] = $embed;
    }
}

class Discord_Webhook {
    public $url;

    function __construct(string $url) {
        $this->url = $url;
    }

    function send(Discord_Message $message) {
        return http_post_request($this->url, json_encode($message), [
            "Content-Type: application/json"
        ]);
    }
}
?>