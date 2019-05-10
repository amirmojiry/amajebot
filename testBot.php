<?php

//Temp
if ($user_id == ADMIN_ID) {
    $file_id = $update["message"]["photo"]["0"]["file_id"];
    $forward_from_message_id = $update["message"]["forward_from_message_id"];
    $message_id = $update["message"]["message_id"];
    $text = "forward id: ".$forward_from_message_id." -- file ID: ".$file_id;

    $db = Database::getInstance();
    $insert = $db->query("INSERT INTO
                                forwardMesaages (forwardFromMessage, fileID)
                                VALUES ('$forward_from_message_id', '$file_id')");
    message_request_json("sendMessage", array('chat_id' =>$chat_id,'text'=>$text,
        disable_web_page_preview=>false,parse_mode=>'HTML'
    ));
} else {
    message_request_json("sendMessage", array('chat_id' =>$chat_id,'text'=>'ربات آماژه در حال بروزرسانی است. لطفا یک ساعت دیگر مراجعه کنید.',
        disable_web_page_preview=>false,parse_mode=>'HTML'
    ));

}
