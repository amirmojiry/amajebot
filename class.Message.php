<?php

class Message {

  //array
  public $message;

  /**
  * Constructor
  */
  public function __construct ($update) {
    $this->message ['update_id'] = $update["update_id"];
    $this->message ['chat_id'] = $update["message"]['chat']['id'];
    $this->message ['text'] = $update["message"]['text'];
    $this->message ['message_id'] = $update["message"]['message_id'];
    $this->message ['message_date'] = $update["message"]['date'];
    $this->message ['user_id'] = $update["message"]['from']['id'];
    $this->message ['first_name'] = $update["message"]['chat']['first_name'];
    $this->message ['last_name'] = $update["message"]['chat']['last_name'];
    $this->message ['language_code'] = $update["message"]['from']['language_code'];
    $this->message ['username'] = $update["message"]['chat']['username'];
    $this->message ['type_chat'] = $update["message"]['chat']['type'];
  }
}
