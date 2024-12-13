<?php
$webhook_config = array(
    "new_game" => array(
        "threads" => true,
        "allowed_mentions" => array(
            "parse" => ["roles"],
            "roles" => ["1225836017974509600"]
        ),
        "tags" => array(
            "engine" => array(
                ENGINE_RPG2K => "1316979582242521109",
                ENGINE_RGSS => "1316979643793936456",
                ENGINE_RPGMV => "1316979671178547342",
                ENGINE_OTHER => "1316979763495305267"
            ),
            "language" => array(
                LANGUAGE_VIETNAMESE => "1316979799171797063",
                LANGUAGE_ENGLISH => "1316979815722778705",
                LANGUAGE_JAPANESE => "1316979841379336233",
                LANGUAGE_CHINESE => "1316979857858494464",
                LANGUAGE_OTHER => "1316979893292109865",
                LANGUAGE_MULTIPLE => "1316979923566727228"
            ),
            "os" => array(
                "pc" => "1317004094090776687",
                "mobile" => "1317004103649591326"
            )
        )
    ),
    "moderation" => array(
        "allowed_mentions" => array(
            "parse" => ["roles"],
            "roles" => ["1194297303142518844"]
        )
    )
);
?>