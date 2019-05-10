<?php

class Message {

  public $update_id;
  public $chat_id;
  public $text;
  public $message_id;
  public $message_date;
  public $user_id;
  public $first_name;
  public $last_name;
  public $language_code;
  public $username;
  public $type_chat;

  /**
  * Constructor
  * @param array $update
  */
  public function __construct ($update = NULL) {
    $this -> update_id = $update["update_id"];
    $this -> chat_id = $update["message"]['chat']['id'];
    $this -> text = $update["message"]['text'];
    $this -> message_id = $update["message"]['message_id'];
    $this -> message_date = $update["message"]['date'];
    $this -> user_id = $update["message"]['from']['id'];
    $this -> first_name = $update["message"]['chat']['first_name'];
    $this -> last_name = $update["message"]['chat']['last_name'];
    $this -> language_code = $update["message"]['from']['language_code'];
    $this -> username = $update["message"]['chat']['username'];
    $this -> type_chat = $update["message"]['chat']['type'];
  }

  /**
  * Send message.
  * @param string $this_text
  * @param string $chat_id
  * @param array $this_keyboard
  */
  public function send_message ($this_text, $this_keyboard, $chat_id = NULL) {
    if ($chat_id == NULL) {
      $chat_id = $this -> chat_id;
    }
    $this_text .= "\n".SIGN;
    $reply_markup = array (
      'resize_keyboard' =>true,
      'keyboard'=>$this_keyboard
    );
    Curl::message_request_json("sendMessage", array (
      'chat_id' => $chat_id,
      'text' => $this_text,
      disable_web_page_preview => false,
      parse_mode => 'HTML',
      'reply_markup' =>$reply_markup
      )
    );
  }

  /**
  * Send message.
  * @param string $activity
  * @param string $activity_number
  * @param string $bot_text
  */
  public function insert_log_message ($activity, $activity_number = "", $bot_text = "") {
    $update_id = $this -> update_id;
    $message_id = $this -> message_id;
    $user_id = $this -> user_id;
    $message_date = $this -> message_date;
    $text = $this -> text;
    $db = Database::getInstance();
    $db->query("INSERT INTO
      log (updateID, messageID, fromID, messageDate,
      activity, activityNumber, userText, botText)
      VALUES ('$update_id', '$message_id', '$user_id', '$message_date',
      '$activity', '$activity_number', '$text', '$bot_text')");
    $db->close();
  }

  /**
  * Insert user if didnt start previously and update if started.
  */
  public function insert_user () {
    $db = Database::getInstance();
    if ($this->user_exists()) {
      $db->query("UPDATE users
        SET firstName = '{$this->first_name}', lastName = '{$this->last_name}',
        languageCode = '{$this->language_code}', username = '{$this->username}',
        typeChat = '{$this->type_chat}', lastUpdate = NOW()
        WHERE userID = '{$this->user_id}'");
    }
    else {
      $db->query("INSERT INTO
        users (userID, firstName, lastName,
        languageCode, username, typeChat)
        VALUES ('{$this->user_id}', '{$this->first_name}', '{$this->last_name}',
        '{$this->language_code}', '{$this->username}', '{$this->type_chat}')");
    }
  }

  /**
  * Check this user started bot previously or didnt.
  * @return boolean
  */
  public function user_exists () {
    $user_id = $this -> user_id;
    $db = Database::getInstance();
    $user_exists = $db->query("SELECT *
      FROM users
      WHERE userID = '$user_id'");
    return (empty ($user_exists))? FLASE : TRUE;
  }

  /**
  * Find previous activity of this user.
  * @return string
  */
  public function get_previous_activity () {
    $user_id = $this -> user_id;
    $db = Database::getInstance();
  	$previous_activity_query = $db->query("SELECT *
      FROM log
      WHERE fromID = '$user_id'
      ORDER BY ID DESC
      LIMIT 1");
  	return $previous_activity_query[0]['activity'];
  }


}
