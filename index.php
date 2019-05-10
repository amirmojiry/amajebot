<?php
require_once ('includes/defining.php');
require_once ('includes/core.php');
require_once ('includes/db.php');
require_once ('includes/persian_date.class.php');
require_once ('class.Message.php');

$content = file_get_contents ("php://input");
$update = json_decode ($content, true);

if (isset ($update["message"])) {
    process_message ( $update);
}
else if (isset ($update["callback_query"])){
    callback_message ( $update);
}

/**
*
* @param array $update
*/
function callback_message($update) {
  $update_id = $update["update_id"];
  $callback_id=$update["callback_query"]['id'];
  $chat_id=$update["callback_query"]['message']['chat']['id'];
  $from_id=$update["callback_query"]['from']['id'];
  $data=$update["callback_query"]['data'];
  $message_id=$update["callback_query"]["message"]['message_id'];
  $text=$update["callback_query"]['message']['text'];
  $message_date =$update["callback_query"]["message"]['date'];
  if($data == "fal_hafez") {
    $db = Database::getInstance();
    $fal_hafez_query = $db-> query("SELECT *
      FROM poetry
      LEFT JOIN poetryInterpretation
      ON poetry.ID = poetryInterpretation.poetryID
      ORDER BY RAND() LIMIT 1");
    $poetry = $fal_hafez_query[0]['poetry'];
    $title = $fal_hafez_query[0]['title'];
    $interpretation = $fal_hafez_query[0]['interpretation'];
    $file_ID = $fal_hafez_query[0]['fileID'];
    $poetry_number = $fal_hafez_query[0]['poetryNumber'];
    $audio_post_number = $poetry_number + 1;
    $fal_hafez_text = "<pre>\xF0\x9F\x93\x96 شعر: \n</pre>"
      . "<a href='https://t.me/Cxp3adu/$audio_post_number'>&#160;</a>"
      . $poetry
      . "<pre>\n\xE2\x9C\xA8 تفسیر:\n</pre>"
      . $interpretation
      . "\n".SIGN;
    message_request_json("sendMessage",
        array(
          'chat_id' => $chat_id,
          'text' => $fal_hafez_text,
          'parse_mode' => 'HTML'
        ));
    /*$log_query = $db->query("INSERT INTO
                                        log (updateID, messageID, fromID, messageDate)
                                        VALUES ('$update_id', '$message_id', '$from_id', '$message_date')");*/
    exit();
  }
}

function home ($first_name, $last_name) {
	$new_persian_date = new persian_date();
	$date = new DateTime();
	$date->setTimezone(new DateTimeZone('Asia/Tehran'));
  $current_time = $date->format('H:i');
  $current_day = $date->format('Y/m/d');
  $today_persian = $new_persian_date->to_date ($current_day, 'compelete');
  $return_array = array();
  $return_array['this_text'] = " سلام "
    .$first_name
    .$last_name
    ." عزیز به ربات «آماژه» خوش آمدید. امروز "
    . $today_persian
    ." و الان ساعت "
    . $current_time
    ." به وقت تهران (ایران) است."
    ." آماژه الان کارهای زیر رو انجام می ده:"
    ."\n- فال حافظ (شعر، تفسیر، صوت)"
    ."\n- استخاره از قرآن"
    ."\n- تحلیل متن"
    ."\nاما به زودی خیلی کارهای دیگه هم می‌کنه.";
  $return_array['this_keyboard'] = array (
    array (
      'فال حافظ',
      'استخاره'
    ),
    array (
      'تحلیل متن'
    ),
    array (
      'درباره',
      'تماس'
    )
  );
  return $return_array;
}

function contact() {
  $return_array = array ();
  $return_array ['this_text'] =
    "می‌توانید پیام خود را همین جا بنویسید "
    ."و بفرستید تا به دست ما برسد. "
    ."یا از راه‌های زیر با ما در ارتباط باشید:"
    ."\n \xF0\x9F\x8C\x8D راه های ارتباط با آماژه:"
    ."\n \xF0\x9F\x93\xA7 ایمیل: amojiry@gmail.com"
    ."\n \xF0\x9F\x93\xB2 <a href='tg://user?id="
    .ADMIN_ID
    ."\n '>ارتباط مستقیم با سازنده ربات در تلگرام</a>"
    ."\n \xF0\x9F\x92\xBB <a href='http://amaje.ir'>سایت آماژه</a>";
  $return_array ['this_keyboard'] = array (
    array (
      'بازگشت به صفحه اول',
      'درباره',
    )
  );
  return $return_array;
}

function about() {
  $return_array = array ();
  $return_array ['this_text'] = "آماژه سعی می کنه ایده های خلاقانه رو به اجرا دربیاره."
    ."\nآماژه یعنی اشاره.";
  $return_array ['this_keyboard'] = array (
      array (
        'بازگشت به صفحه اول',
        'تماس',
      ),
  );
  return $return_array;
}

function fal_hafez_main () {
  $return_array = array ();
  $return_array ['this_text'] = "فال حافظ یعنی خواندن اتفاقی یکی از غزل‌های دیوان حافظ. مردم برای انجام کارهایشان با حافظ مشورت می‌کنند.
    یکی از رسم‌های شب های یلدا (چله) گرفتن فال حافظ است. ایرانی‌ها سر سفره‌ی هفت سین روز اول فروردین (نوروز) هم فال می‌گیرند.
    می‌گویند بهتر است قبل از فال گرفتن حافظ را این گونه قسم بدهید:
    ✅  ای حافظ شیرازی! تو محرم هر رازی!
    تو را به خدا و به شاخ نباتت قسم می‌دهم که هر چه صلاح و مصلحت می‌بینی برایم آشکار و آرزوی مرا برآورده سازی. ✅
    [شاخ نبات، معشوق حافظ بوده است].
    حالا به کاری که می‌خواهید بکنید فکر کنید (نیت کنید) و فال بگیرید.
    (دکمه ی «فال بگیر» را بزنید).";
  $return_array ['this_keyboard'] = array(
      array('آخرین فال های حافظ من','فال بگیر'),
      array('بازگشت به صفحه اول')
  );
  return $return_array;
}

function fal_hafez () {
  $return_array = array ();
  $db = Database::getInstance ();
  $fal_hafez_query = $db -> query("SELECT *
    FROM poetry
    LEFT JOIN poetryInterpretation
    ON poetry.ID = poetryInterpretation.poetryID
    ORDER BY RAND()
    LIMIT 1");
  $poetry = $fal_hafez_query[0]['poetry'];
  $title = $fal_hafez_query[0]['title'];
  $interpretation = $fal_hafez_query[0]['interpretation'];
  $file_ID = $fal_hafez_query[0]['fileID'];
  $return_array ['poetry_number'] = $fal_hafez_query[0]['poetryNumber'];
  $audio_post_number = $poetry_number + 1;
  $return_array ['this_text'] = "<pre>\xF0\x9F\x93\x96  شعر:</pre>
    <a href='https://t.me/Cxp3adu/$audio_post_number'>" . "&#160;</a>
    $poetry
    <pre>\n\xE2\x9C\xA8 تفسیر: \n</pre>
    $interpretation
    \n فالتان را گوش کنید.🔈";
  $return_array ['this_keyboard'] = array (
      array ('بازگشت به صفحه اول','بازگشت به صفحه فال')
  );
  return $return_array;
}

function last_fal_hafez ($user_id) {
  $return_array = array ();
  $db = Database::getInstance();
  $count_fal = count ($db -> query ("SELECT activity
    FROM log
    WHERE fromID = '$user_id'
    AND activity = 'fal_hafez'"));
  if (count ($count_fal) == 0) {
    $return_array ['this_text'] = "شما تاکنون هیچ فالی نگرفته اید.";
  }
  else {
    $last_fal_hafez_query = $db -> query( "SELECT log.*,
      poetryInterpretation.interpretation, poetry.poetryNumber, poetry.title
      FROM log
      LEFT JOIN poetryInterpretation
      ON log.activityNumber = poetryInterpretation.poetryID
      LEFT JOIN poetry
      ON log.activityNumber = poetry.poetryNumber
      WHERE log.fromID = '$user_id'
      AND log.activity = 'fal_hafez'
      ORDER BY log.ID DESC
      LIMIT 10");
    $return_array ['this_text'] = "آخرین فال های حافظ شما "
      ."(شما تاکنون "
      .$count_fal
      ." فال حافظ گرفته اید";
    if ($count_fal > 10) {
      $return_array ['this_text'] .= " اما فقط 10 فال حافظ آخر به شما نشان داده می شود):";
    }
    else {
      $return_array ['this_text'] .= "):";
    }
    for ($i = 0 ; $i < count($last_fal_hafez_query); $i++ ) {
      $row = $i+1;
      $date = new DateTime($last_fal_hafez_query[$i]['timeof']);
      $date->setTimezone(new DateTimeZone('Asia/Tehran'));
      $time = $date->format('H:i');
      $day = $date->format('Y/m/d');
      $new_persian_date = new persian_date();
      $persian_day = $new_persian_date->to_date ($day, 'compelete');
      $return_array ['this_text'] .= "\n"
        .$row
        ."- "
        .$persian_day
        ." ساعت "
        .$time
        ." (به وقت تهران)"
        .": پاسخ فال شما، غزل "
        .$last_fal_hafez_query[$i]['activityNumber']
        ." ("
        .$last_fal_hafez_query[$i]['title']
        .") "
        ."دیوان حافظ شده است که تفسیر آن این است: "
        .$last_fal_hafez_query[$i]['interpretation'];
    }
  }
  $return_array ['this_keyboard'] = array (
    array (
      'بازگشت به صفحه اول',
      'بازگشت به صفحه فال',
    )
  );
  return $return_array;
}

function estekhare_main () {
  $return_array = array ();
  $return_array ['this_text'] = "استخاره یعنی واگذار کردن انجام کار به خدا. وقتی که در انجام یک کار شک دارید، می‌توانید استخاره کنید.
  <i>🔸 فراموش نکنید که فقط پس از تحقیق کامل، مشورت و فکر کافی درباره‌ی یک کار، به استخاره رو بیاورید. 🔸</i>
  یکی از روش‌های استخاره، استخاره با قرآن است. در این روش، پس از نیت یک صفحه از قرآن را باز می‌کنند و طبق آیات آن صفحه (به ویژه آیه‌ی اول آن) تصمیم می‌گیرند که نتیجه‌ی استخاره چیست.
  📖 بهتر است پیش از استخاره دعای زیر را بخوانید و سه بار صلوات بفرستید:
  <b>اَللَّهُمَّ اِنّى اَسْتَخيرُكَ بِعِلْمِكَ، فَصَلِّ عَلى‏ مُحَمَّدٍ وَ الِهِ، وَاقْضِ لى‏بِالْخِيَرَةِ، وَاَلْهِمْنا مَعْرِفَةَالْاِخْتِيارِ، وَاجْعَلْ ذلِكَ ذَرِيعَةً اِلَى الرِّضا بِما قَضَيْتَ لَنا، وَالتَّسْليمِ لِما حَكَمْتَ، فَاَزِحْ عَنَّا رَيْبَ الْاِرْتِيابِ، وَ اَيِّدْنا بِيَقينِ الْمُخْلِصينَ، وَ لاتَسُمْنا عَجْزَ الْمَعْرِفَةِ عَمّا تَخَيَّرْتَ فَنَغْمِطَ قَدْرَكَ، وَ نَكْرَهَ مَوْضِعَ رِضاكَ، وَ نَجْنَحَ اِلَى الَّتى هِىَ اَبْعَدُ مِنْ حُسْنِ الْعاقِبَةِ، وَ اَقْرَبُ اِلى‏ ضِدِّ الْعافِيَةِ. حَبِّبْ اِلَيْنا ما نَكْرَهُ مِنْ قَضآئِكَ، وَ سَهِّلْ عَلَيْنا ما نَسْتَصْعِبُ مِنْ حُكْمِكَ، وَ اَلْهِمْنَا الْاِنْقِيادَ لِما اَوْرَدْتَ عَلَيْنا مِنْ مَشِيَّتِكَ، حَتّى‏ لانُحِبَّ تَاْخِيرَ ما عَجَّلْتَ، وَ لاتَعْجيلَ ما اَخَّرْتَ، وَ لا نَكْرَهَ ما اَحْبَبْتَ، وَ لانَتَخَيَّرَ ما كَرِهْتَ. وَ اخْتِمْ لَنا بِالَّتى هِىَ اَحْمَدُ عاقِبَةً، وَ اَكْرَمُ مَصيراً، اِنَّكَ تُفيدُ الْكَريمَةَ، وَ تُعْطِى الْجَسيمَةَ، وَ تَفْعَلُ ما تُريدُ، وَ اَنْتَ عَلى‏ كُلِّ شَىْ‏ءٍ قَديرٌ.</b>
  <b>خداوندا از تو به سبب دانشت (که به همه چیز آگاهى) درخواست خیر دارم، پس بر محمد و آلش درود فرست، و در حقّم به خیر حکم کن، و شناخت آنچه را که برایمان اختیار کردى به ما الهام فرما، و آن را براى ما سبب خشنودى به قضایت، و تسلیم به حُکمت قرار ده، پس دلهره و شک را از ما دور کن، - و ما را به یقین مخلصین تأیید فرما، و ما را دچار فروماندگى از معرفتِ آنچه برایمان اختیار کرده‏اى مکن، که قدر حضرتت را سبک انگاریم، و مورد رضاى تو را ناپسند داریم، و به چیزى که از حسن عاقبت دورتر، و به خلاف عافیت نزدیک‏تر است میل پیدا کنیم. آنچه را از قضاى خود که ما از آن اکراه داریم محبوب قلبمان کن، و آنچه را از حکم تو سخت مى‌پنداریم بر ما آسان ساز، و گردن نهادن به آنچه از مشيّت و اراده‏ات بر ما وارد آورده‏اى به ما الهام فرما، تا تأخیر آنچه را تعجیل فرمودى، و تعجیل آنچه را به تأخیر انداختى دوست نداریم، و هر آنچه محبوب توست ناپسند ندانیم، و آنچه را تو نمى‏پسندى انتخاب نکنیم. و کار ما را به آنچه عاقبتش پسندیده‏تر، و مآلش بهتر است پایان بخش، که تو عطاى نفیس مرحمت مى‏کنى، و نعمتهاى بزرگ مى‏بخشى، و هر چه مى‏خواهى آن کنى، و بر هر چیز قدرت دارى.</b>
  <a href='http://www.erfan.ir/farsi/sahife/nsm_proj/nahgol/display/display.php?Vr_page=184'>دعای 33 صحیفه سجادیه با ترجمه‌ی حسین انصاریان</a>
  در ربات آماژه، مانند روش‌های قدیمی استخاره با قرآن، نه حالت برای استخاره‌ی شما وجود دارد که به ترتیب عبارتند از: «خیلی خیلی خوب»، «خیلی خوب»، «خوب»، «میانه خوب»، «میانه»، «میانه بد»، «بد»، «خیلی بد» و «خیلی خیلی بد»
  (پس از نیت کردن، دکمه ی «استخاره بگیر» را بزنید).";
  $return_array ['this_keyboard'] = array(
      array('آخرین استخاره های من','استخاره بگیر'),
      array('بازگشت به صفحه اول')
  );
  return $return_array;
}

function estekhare () {
  $return_array = array ();
  $db = Database::getInstance();
  $estekhare_query = $db->query("SELECT *
    FROM quranPages LEFT JOIN quranPagesInterpretation
    ON quranPages.quranPage = quranPagesInterpretation.quranPage
    WHERE quranPages.translator = 'None'
    AND quranPages.translateLanguage = '0'
    AND  MOD (quranPages.quranPage, 2) = 1
    ORDER BY RAND()
    LIMIT 1");
  $estekhare_file_id = $estekhare_query[0]['fileID'];
  $return_array ['estekhare_quranPage'] = $estekhare_query[0]['quranPage'];
  $forwardFromMessageID = $estekhare_query[0]['forwardFromMessageID'];
  $dead_query = $db->query("SELECT *
    FROM deads
    ORDER BY RAND()
    LIMIT 1");
  $dead = $dead_query[0]['deadName'];
  $estekhare_interpretation = $estekhare_query[0]['interpretation'];
  $estekhare_salavat = "\xF0\x9F\x99\x8F لطفا برای شادی روح "
    .$dead
    ." صلوات بفرستید.";
  $return_array ['this_text'] = "\xF0\x9F\x93\x96 نتیجه ی استخاره ی شما :
    $estekhare_interpretation
    <a href='https://t.me/Cxp3adu/$forwardFromMessageID'>&#xA0;</a>
    <pre>$estekhare_salavat</pre>
    \n".SIGN;
  $return_array ['this_keyboard'] = array (
    array (
      'بازگشت به صفحه اول',
      'بازگشت به صفحه استخاره',
    )
  );
  return $return_array;
}

function last_estekhare ($user_id) {
  $return_array = array ();
  $db = Database::getInstance();
  $count_estekhare = count($db->query("SELECT activity
    FROM log
    WHERE fromID = '$user_id'
    AND activity = 'estekhare'"));
  if (count ($count_estekhare) == 0) {
    $return_array ['this_text'] = "شما تاکنون هیچ استخاره ای نگرفته اید.";
  }
  else {
    $last_estekhare_query = $db->query("SELECT log.*,
      quranPagesInterpretation.interpretation
      FROM log LEFT JOIN quranPagesInterpretation
      ON log.activityNumber = quranPagesInterpretation.quranPage
      WHERE log.fromID = '$user_id'
      AND log.activity = 'estekhare'
      ORDER BY log.ID DESC
      LIMIT 10");
    $return_array ['this_text'] = "آخرین استخاره های شما "."(شما تاکنون ".$count_estekhare." استخاره گرفته اید";
    if ($count_estekhare > 10) {
      $return_array ['this_text'] .= " اما فقط 10 استخاره ی آخر به شما نشان داده می شود):";
    }
    else {
      $return_array ['this_text'] .= "):";
    }
    for ($i = 0 ; $i < count($last_estekhare_query); $i++ ) {
      $row = $i+1;
      $date = new DateTime($last_estekhare_query[$i]['timeof']);
      $date->setTimezone(new DateTimeZone('Asia/Tehran'));
      $time = $date->format('H:i');
      $day = $date->format('Y/m/d');
      $new_persian_date = new persian_date();
      $persian_day = $new_persian_date->to_date ($day, 'compelete');
      $return_array ['this_text'] .= "\n".$row."- ".$persian_day
        ." ساعت ".$time
        ." (به وقت تهران)"
        .": پاسخ استخاره‌ی شما، صفحه‌ی ".$last_estekhare_query[$i]['activityNumber']
        ." قرآن شده است که یعنی ".$last_estekhare_query[$i]['interpretation'];
    }
  }
  $return_array ['this_keyboard'] = array (
    array (
      'بازگشت به صفحه استخاره',
      'بازگشت به صفحه اول'
    )
  );
  return $return_array;
}

function text_analysis_main () {
  $return_array = array ();
  $return_array ['this_text'] = "🔧⚒در حال ساخت [آزمایشی] 🔧⚒
    متن خود را تحلیل کنید!
    کافیه متن خودتون رو همین جا بفرستید (فقط در قالب متن).";
  $return_array ['this_keyboard'] = array (
    array(
      'بازگشت به صفحه اول',
    )
  );
  return $return_array;
}

function text_analysis () {
  $return_array = array ();
  //number of characters
  //--I'm used mb_strlen, no strlen because of persian characters.
  $characters_number = mb_strlen($text,'utf-8');
  //number of each character
  //--I'm used preg_split, no str_split because of persian characters.
  $characters = preg_split('//u', $text, null, PREG_SPLIT_NO_EMPTY);
  $characters_number_array = array_count_values($characters);
  arsort ($characters_number_array);
  $just_10_characters_counter = 0;
  foreach ($characters_number_array as $key => $value) {
    if ($just_10_characters_counter++ < 10) {
      $sorted_all_characters_number .= "کاراکتر «"
        . $key
        . "» : "
        . $value
        ."\n" ;
    }
  }
  //number of words
  //--I'm used this function, no count_word because of persian characters.
  $words_number = count ( preg_split ( '~[\p{Z}\p{P}]+~u',
    $text,
    null,
    PREG_SPLIT_NO_EMPTY));
  //number of each word
  //--I'm used preg_split, no str_split because of persian characters.
  $words = preg_split ( '~[\p{Z}\p{P}]+~u',
  $text,
  null,
  PREG_SPLIT_NO_EMPTY);
  $words_number_array = array_count_values($words);
  arsort ($words_number_array);
  $just_10_words_counter = 0;
  foreach ($words_number_array as $key => $value) {
    if ($just_10_words_counter++ < 10)
      $sorted_all_words_number .= "واژه ی «"
        . $key
        . "» : "
        . $value
        ."\n" ;
  }
  //number of paragraphs
  $paragraph_number = substr_count( $text, "\n" ) + 1;
  $return_array ['this_text'] = "متن شما:
    $characters_number کاراکتر،
    $words_number واژه و
    $paragraph_number پاراگراف  دارد."
    ."\n تعداد تکرار هر واژه (حداکثر 10 واژه‌ی پرتکرار شمرده می‌شود): \n"
    .$sorted_all_words_number
    ."\n تعداد تکرار هر کاراکتر (حداکثر 10 کاراکتر پرتکرار شمرده می‌شود): \n"
    .$sorted_all_characters_number
    ."\n اگر می خواهید متن دیگری وارد کنید روی «بازگشت  به صفحه تحلیل متن» بزنید.";
  $return_array ['this_keyboard'] = array (
    array (
      'بازگشت به صفحه اول',
      'بازگشت به صفحه تحلیل متن',
    )
  );
  return $return_array;
}

function contact_admin ($user_id, $first_name, $last_name, $username, $text) {
  $return_array = array ();
  $return_array ['this_text'] .= "پیام شما به دست ما رسید "
    ."و اگر نیاز به پاسخ داشته باشد، "
    ."در اسرع وقت به آن پاسخ خواهیم داد.";
  $return_array ['this_keyboard'] = array (
    array (
      'تماس',
      'بازگشت به صفحه اول',
    )
  );
  //send user text to admin
  $text_contact_admin =
    "پیامی از یک کاربر بات آماژه:"
    ."شناسه کاربر: "
    .$user_id
    ."\n"
    ."نام و نام خانوادگی کاربر: "
    .$first_name
    . " "
    .$last_name
    ."\n"
    ."نام کاربری: @"
    .$username
    ."\n"
    ."پیام کاربر: "
    ."\n "
    .$text;
  $message = new Message;
  $message->send_message ($text_contact_admin, $return_array ['this_keyboard'], ADMIN_ID);
  return $return_array;
}

function wrong () {
  $return_array = array ();
  $return_array ['this_text'] = "اشتباهه!";
  $return_array ['this_keyboard'] = array (
    array (
      'فال حافظ',
      'استخاره',
    ),
    array (
      'تحلیل متن',
    ),
    array (
      'درباره',
      'تماس',
    )
  );
  return $return_array;
}

/**
*
* @param array $update
*/
function process_message($update) {

  $message = new Message($update);

  $update_id = $message -> update_id;
  $chat_id = $message -> chat_id;
  $text = $message -> text;
  $message_id = $message -> message_id;
  $message_date = $message -> message_date;
  $user_id = $message -> user_id;
  $first_name = $message -> first_name;
  $last_name = $message -> last_name;
  $language_code = $message -> language_code;
  $username = $message -> username;
  $type_chat = $message -> type_chat;
  $previous_activity = $message -> get_previous_activity ();
	//home
  if($text == "/start" || $text == "بازگشت به صفحه اول") {
    $message -> insert_user();
    $return_array = home ($first_name, $last_name);
    $message -> send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message -> insert_log_message ("home");
  }
  //contact
  elseif ( $text == "تماس" ) {
    $return_array = contact ();
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("contact");
  }
  //about
  elseif ($text == "درباره") {
    $return_array = about ();
    $message -> send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message -> insert_log_message ("about");
  }
  //fal_hafez_main
  elseif ($text == "فال حافظ" || $text == "بازگشت به صفحه فال") {
    $return_array = fal_hafez_main ();
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("fal_hafez_main");
  }
	//fal_hafez
	elseif ($text == "فال بگیر") {
    $return_array = fal_hafez();
    $message -> send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message -> insert_log_message ("fal_hafez", $return_array ['poetry_number']);
    }
	//last_fal_hafez
	elseif ($text == "آخرین فال های حافظ من") {
		$return_array = last_fal_hafez($user_id);
    $message -> send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message -> insert_log_message ("last_fal_hafez");
	}
  //estekhare_main
  elseif ($text == "استخاره" || $text == "بازگشت به صفحه استخاره") {
    $return_array = estekhare_main();
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("estekhare_main");
  }
	//estekhare
	elseif ($text == "استخاره بگیر") {
    $return_array = estekhare ();
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("estekhare", $return_array ['estekhare_quranPage']);
  }
	//last_estekhare
	elseif ($text == "آخرین استخاره های من") {
    $return_array = last_estekhare ($user_id);
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("last_estekhare");
	}
  //text_analysis_main
  elseif ($text == "تحلیل متن" || $text == "بازگشت به صفحه تحلیل متن") {
    $return_array = text_analysis_main ();
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("text_analysis_main");
  }
	//text_analysis
	elseif ($previous_activity == "text_analysis_main") {
		$return_array = text_analysis ();
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("text_analysis", "", $this_text);
	}
	//contact_admin
	elseif ($previous_activity == "contact") {
    $return_array = contact_admin ($user_id, $first_name, $last_name, $username, $text);
    $message->send_message ($return_array['this_text'],
      $return_array['this_keyboard']);
    $message->insert_log_message ("contact_admin");
	}
	// !!WRONG!!
	else {
    $return_array = wrong ();
    $message->send_message ($this_text, $this_keyboard);
    $message->insert_log_message ("wrong");
  }
}
?>
