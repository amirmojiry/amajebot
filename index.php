<?php
require_once('defining.php');
require_once('includes/core.php');
require_once('includes/db.php');
require_once ('includes/persian_date.class.php');

$content = file_get_contents("php://input");
$update = json_decode($content, true);


if (isset($update["message"])) {
    processMessage($update);
}

else if(isset($update["callback_query"])){
    callbackMessage($update);
}
/*else if(isset($update["inline_query"])){
    inlineMessage($update);
}

 function inlineMessage($update){
    $id =$update["inline_query"]['id'];
    $chat_id =$update["inline_query"]['from']['id'];
    $query =$update["inline_query"]['query'];


    $db = Db::getInstance();
    $articles = $db->query("SELECT * FROM article WHERE cat=:query LIMIT 10",array(
    'query' => $query
    ));



    $resault_articles = array();
    foreach($articles as $article){

       $article_title = $article[title];
        $article_cat = $article[cat];
        $article_description = $article[description];
        $article_url = $article[url];
        $article_text = $article[text];
         $article_thumb = $article[thumb];

       $resault_articles[] = array("type"=>"article","id"=>microtime()."a","title"=>$article_title,"input_message_content"=>array("message_text"=>$article_text),description=>$article_description,thumb_url=>$article_thumb,url=>$article_url,
             "reply_markup"=>array(
            "inline_keyboard"=>array(
                array(array("text"=>"مشاده کامل خبر","url"=>$article_url))
            )
        )
    );
    }

     MessageRequestJson("answerInlineQuery",array("inline_query_id"=>$id,"results"=>$resault_articles));
}*/

function callbackMessage($update){
    $update_id = $update["update_id"];
    $callback_id=$update["callback_query"]['id'];
    $chat_id=$update["callback_query"]['message']['chat']['id'];
    $from_id=$update["callback_query"]['from']['id'];
    $data=$update["callback_query"]['data'];
    $message_id=$update["callback_query"]["message"]['message_id'];
    $text=$update["callback_query"]['message']['text'];
    $message_date =$update["callback_query"]["message"]['date'];

    if($data == "fal_hafez") {
        $db = Db::getInstance();
        $fal_hafez_query = $db->query("SELECT * FROM poetry LEFT JOIN poetryInterpretation ON poetry.ID = poetryInterpretation.poetryID
                                        ORDER BY RAND() LIMIT 1");
        $poetry = $fal_hafez_query[0]['poetry'];
        $title = $fal_hafez_query[0]['title'];
        $interpretation = $fal_hafez_query[0]['interpretation'];
        $file_ID = $fal_hafez_query[0]['fileID'];
        $poetry_number = $fal_hafez_query[0]['poetryNumber'];
        $audio_post_number = $poetry_number + 1;
        $fal_hafez_text = "<pre>"
            . "\xF0\x9F\x93\x96 "
            . "شعر: \n" . "</pre>"
            . "<a href='https://t.me/Cxp3adu/$audio_post_number'>&#160;</a>"
            . $poetry
            . "<pre>"
            . "\n\xE2\x9C\xA8"
            . "تفسیر:\n"
            . "</pre>"
            . $interpretation
            . "\n".SIGN;

        MessageRequestJson("sendMessage",
            array('chat_id' => $chat_id, 'text' => $fal_hafez_text, 'parse_mode' => 'HTML')
        );
        /*$log_query = $db->query("INSERT INTO
                                            log (updateID, messageID, fromID, messageDate)
                                            VALUES ('$update_id', '$message_id', '$from_id', '$message_date')");*/
        exit();
    }
}

function processMessage($update){
	
    $update_id = $update["update_id"];
    $chat_id = $update["message"]['chat']['id'];
    $text = $update["message"]['text'];
    $message_id = $update["message"]['message_id'];
    $message_date = $update["message"]['date'];
    $user_id = $update["message"]['from']['id'];
    $first_name = $update["message"]['chat']['first_name'];
    $last_name = $update["message"]['chat']['last_name'];
    $language_code = $update["message"]['from']['language_code'];
    $username = $update["message"]['chat']['username'];
    $type_chat = $update["message"]['chat']['type'];
	
	//previous activity
	$db = Db::getInstance();
	$previous_activity_query = $db->query("SELECT * FROM log WHERE fromID = '$user_id' ORDER BY ID DESC LIMIT 1");
	$previous_activity = $previous_activity_query[0]['activity'];
	
	//date
	$new_persian_date = new persian_date();
	$date = new DateTime();
	$date->setTimezone(new DateTimeZone('Asia/Tehran'));
	
	//home
    if($text == "/start" || $text == "بازگشت به صفحه اول"){
		//today and now time
		//$now = date ("Y-m-d H:i:s");
		$now_time = $date->format('H:i');
		$now_day = $date->format('Y/m/d');
		$today_persian = $new_persian_date->to_date ($now_day, 'compelete');
		
		//if this is a new user insert to db
        $is_new_user_query = $db->query("SELECT * FROM users WHERE userID = $user_id");
        if ( empty ($is_new_user_query)) {
            $add_user_to_db = $db->query("INSERT INTO 
                                                users (userID, firstName, lastName, languageCode, username, typeChat)
                                                VALUES ($user_id, '$first_name', '$last_name', '$language_code', '$username', '$type_chat')");
        } else {
            $update_user_in_db = $db->query("UPDATE users SET 
                                                firstName = '$first_name', lastName = '$last_name', 
                                                languageCode = '$language_code', username = '$username', 
                                                typeChat = '$type_chat', lastUpdate = NOW()
                                                WHERE userID = '$user_id'");
        }
        $this_text =
            "سلام ".
            $first_name." ".$last_name.
            " عزیز
به ربات «آماژه» خوش آمدید.
امروز ".
$today_persian.
" و الان ساعت ".
$now_time.
" به وقت تهران (ایران) است. 
آماژه الان کارهای زیر رو انجام می ده:
- فال حافظ (شعر، تفسیر، صوت)
- استخاره از قرآن
- تحلیل متن
 اما به زودی خیلی کارهای دیگه هم می‌کنه.
\n".SIGN;
		//log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'home', '', '$text')");
        $this_keyboard = array(
            array('فال حافظ', 'استخاره'),
			array('تحلیل متن'),
            array('درباره','تماس')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
    
	//contact
	elseif ($text == "تماس"){
        $this_text = "می‌توانید پیام خود را همین جا بنویسید و بفرستید تا به دست ما برسد. یا از راه‌های زیر با ما در ارتباط باشید:
		\xF0\x9F\x8C\x8D راه های ارتباط با آماژه:
\xF0\x9F\x93\xA7 ایمیل: amojiry@gmail.com
\xF0\x9F\x93\xB2 <a href='tg://user?id=".ADMIN_ID."'>ارتباط مستقیم با سازنده ربات در تلگرام</a>
\xF0\x9F\x92\xBB <a href='http://amaje.ir'>سایت آماژه</a>
\n".SIGN;
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'contact', '', '$text')");
        $this_keyboard = array(
            array('بازگشت به صفحه اول','درباره')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
    
	//about
	elseif ($text == "درباره"){
        $this_text = "آماژه سعی می کنه ایده های خلاقانه رو به اجرا دربیاره.
آماژه یعنی اشاره.
\n".SIGN;
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'about', '', '$text')");
        $this_keyboard = array(
            array('بازگشت به صفحه اول','تماس')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
    
	//fal_hafez_main
	elseif ($text == "فال حافظ" || $text == "بازگشت به صفحه فال") {
        $this_text = "فال حافظ یعنی خواندن اتفاقی یکی از غزل‌های دیوان حافظ. مردم برای انجام کارهایشان با حافظ مشورت می‌کنند.
یکی از رسم‌های شب های یلدا (چله) گرفتن فال حافظ است. ایرانی‌ها سر سفره‌ی هفت سین روز اول فروردین (نوروز) هم فال می‌گیرند.
می‌گویند بهتر است قبل از فال گرفتن حافظ را این گونه قسم بدهید:
✅  ای حافظ شیرازی! تو محرم هر رازی! 
تو را به خدا و به شاخ نباتت قسم می‌دهم که هر چه صلاح و مصلحت می‌بینی برایم آشکار و آرزوی مرا برآورده سازی. ✅
[شاخ نبات، معشوق حافظ بوده است].
حالا به کاری که می‌خواهید بکنید فکر کنید (نیت کنید) و فال بگیرید.
(دکمه ی «فال بگیر» را بزنید).
\n".SIGN;
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'fal_hafez_main', '', '$text')");
        $this_keyboard = array(
            array('آخرین فال های حافظ من','فال بگیر'),
			array('بازگشت به صفحه اول')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
    
	//fal_hafez
	elseif ($text == "فال بگیر") {
        $fal_hafez_query = $db->query("SELECT * FROM poetry LEFT JOIN poetryInterpretation ON poetry.ID = poetryInterpretation.poetryID
                                        ORDER BY RAND() LIMIT 1");
        $poetry = $fal_hafez_query[0]['poetry'];
        $title = $fal_hafez_query[0]['title'];
        $interpretation = $fal_hafez_query[0]['interpretation'];
        $file_ID = $fal_hafez_query[0]['fileID'];
        $poetry_number = $fal_hafez_query[0]['poetryNumber'];
        $audio_post_number = $poetry_number + 1;
        $this_text = "<pre>\xF0\x9F\x93\x96  شعر:</pre>
<a href='https://t.me/Cxp3adu/$audio_post_number'>" . "&#160;</a>
$poetry
<pre>\n\xE2\x9C\xA8 تفسیر: \n</pre>
$interpretation
\n فالتان را گوش کنید.🔈 \n"
            .SIGN;
		//log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'fal_hafez', '$poetry_number', '$text')");
        $this_keyboard = array(
            array('بازگشت به صفحه اول','بازگشت به صفحه فال')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
	
	//last_fal_hafez
	elseif ($text == "آخرین فال های حافظ من") {
		$count_fal = count($db->query("SELECT activity FROM log WHERE fromID = '$user_id' AND activity = 'fal_hafez'"));
		if (count ($count_fal) == 0) {
			$this_text = "شما تاکنون هیچ فالی نگرفته اید.";
		} else {
			$last_fal_hafez_query = $db->query("SELECT log.*, poetryInterpretation.interpretation, poetry.poetryNumber, poetry.title
					FROM log LEFT JOIN poetryInterpretation
					ON log.activityNumber = poetryInterpretation.poetryID
                    LEFT JOIN poetry
                    ON log.activityNumber = poetry.poetryNumber
					WHERE log.fromID = '$user_id' AND log.activity = 'fal_hafez'
					ORDER BY log.ID DESC LIMIT 10");
			$this_text = "آخرین فال های حافظ شما "."(شما تاکنون ".$count_fal." فال حافظ گرفته اید";
			if ($count_fal > 10)
				$this_text .= " اما فقط 10 فال حافظ آخر به شما نشان داده می شود):";
			else
				$this_text .= "):";
			for ($i = 0 ; $i < count($last_fal_hafez_query); $i++ ) {
				$row = $i+1;
				$date = new DateTime($last_fal_hafez_query[$i]['timeof']);
				$date->setTimezone(new DateTimeZone('Asia/Tehran'));
				$time = $date->format('H:i');
				$day = $date->format('Y/m/d');
				$persian_day = $new_persian_date->to_date ($day, 'compelete');
				$this_text .= "\n".$row."- ".$persian_day
					." ساعت ".$time
					." (به وقت تهران)"
					.": پاسخ فال شما، غزل ".$last_fal_hafez_query[$i]['activityNumber']
					." (".$last_fal_hafez_query[$i]['title'].") "
					."دیوان حافظ شده است که تفسیر آن این است: ".$last_fal_hafez_query[$i]['interpretation'];
			}
		}
		//log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'last_fal_hafez', '', '$text')");
		$this_keyboard = array(
            array('بازگشت به صفحه اول','بازگشت به صفحه فال')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
		MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
	}
    
	//estekhare_main
	elseif ($text == "استخاره" || $text == "بازگشت به صفحه استخاره") {
        $this_text = "استخاره یعنی واگذار کردن انجام کار به خدا. وقتی که در انجام یک کار شک دارید، می‌توانید استخاره کنید.
<i>🔸 فراموش نکنید که فقط پس از تحقیق کامل، مشورت و فکر کافی درباره‌ی یک کار، به استخاره رو بیاورید. 🔸</i>
یکی از روش‌های استخاره، استخاره با قرآن است. در این روش، پس از نیت یک صفحه از قرآن را باز می‌کنند و طبق آیات آن صفحه (به ویژه آیه‌ی اول آن) تصمیم می‌گیرند که نتیجه‌ی استخاره چیست.
📖 بهتر است پیش از استخاره دعای زیر را بخوانید و سه بار صلوات بفرستید:

<b>اَللَّهُمَّ اِنّى اَسْتَخيرُكَ بِعِلْمِكَ، فَصَلِّ عَلى‏ مُحَمَّدٍ وَ الِهِ، وَاقْضِ لى‏بِالْخِيَرَةِ، وَاَلْهِمْنا مَعْرِفَةَالْاِخْتِيارِ، وَاجْعَلْ ذلِكَ ذَرِيعَةً اِلَى الرِّضا بِما قَضَيْتَ لَنا، وَالتَّسْليمِ لِما حَكَمْتَ، فَاَزِحْ عَنَّا رَيْبَ الْاِرْتِيابِ، وَ اَيِّدْنا بِيَقينِ الْمُخْلِصينَ، وَ لاتَسُمْنا عَجْزَ الْمَعْرِفَةِ عَمّا تَخَيَّرْتَ فَنَغْمِطَ قَدْرَكَ، وَ نَكْرَهَ مَوْضِعَ رِضاكَ، وَ نَجْنَحَ اِلَى الَّتى هِىَ اَبْعَدُ مِنْ حُسْنِ الْعاقِبَةِ، وَ اَقْرَبُ اِلى‏ ضِدِّ الْعافِيَةِ. حَبِّبْ اِلَيْنا ما نَكْرَهُ مِنْ قَضآئِكَ، وَ سَهِّلْ عَلَيْنا ما نَسْتَصْعِبُ مِنْ حُكْمِكَ، وَ اَلْهِمْنَا الْاِنْقِيادَ لِما اَوْرَدْتَ عَلَيْنا مِنْ مَشِيَّتِكَ، حَتّى‏ لانُحِبَّ تَاْخِيرَ ما عَجَّلْتَ، وَ لاتَعْجيلَ ما اَخَّرْتَ، وَ لا نَكْرَهَ ما اَحْبَبْتَ، وَ لانَتَخَيَّرَ ما كَرِهْتَ. وَ اخْتِمْ لَنا بِالَّتى هِىَ اَحْمَدُ عاقِبَةً، وَ اَكْرَمُ مَصيراً، اِنَّكَ تُفيدُ الْكَريمَةَ، وَ تُعْطِى الْجَسيمَةَ، وَ تَفْعَلُ ما تُريدُ، وَ اَنْتَ عَلى‏ كُلِّ شَىْ‏ءٍ قَديرٌ.</b> 
<b>خداوندا از تو به سبب دانشت (که به همه چیز آگاهى) درخواست خیر دارم، پس بر محمد و آلش درود فرست، و در حقّم به خیر حکم کن، و شناخت آنچه را که برایمان اختیار کردى به ما الهام فرما، و آن را براى ما سبب خشنودى به قضایت، و تسلیم به حُکمت قرار ده، پس دلهره و شک را از ما دور کن، - و ما را به یقین مخلصین تأیید فرما، و ما را دچار فروماندگى از معرفتِ آنچه برایمان اختیار کرده‏اى مکن، که قدر حضرتت را سبک انگاریم، و مورد رضاى تو را ناپسند داریم، و به چیزى که از حسن عاقبت دورتر، و به خلاف عافیت نزدیک‏تر است میل پیدا کنیم. آنچه را از قضاى خود که ما از آن اکراه داریم محبوب قلبمان کن، و آنچه را از حکم تو سخت مى‌پنداریم بر ما آسان ساز، و گردن نهادن به آنچه از مشيّت و اراده‏ات بر ما وارد آورده‏اى به ما الهام فرما، تا تأخیر آنچه را تعجیل فرمودى، و تعجیل آنچه را به تأخیر انداختى دوست نداریم، و هر آنچه محبوب توست ناپسند ندانیم، و آنچه را تو نمى‏پسندى انتخاب نکنیم. و کار ما را به آنچه عاقبتش پسندیده‏تر، و مآلش بهتر است پایان بخش، که تو عطاى نفیس مرحمت مى‏کنى، و نعمتهاى بزرگ مى‏بخشى، و هر چه مى‏خواهى آن کنى، و بر هر چیز قدرت دارى.</b> 
<a href='http://www.erfan.ir/farsi/sahife/nsm_proj/nahgol/display/display.php?Vr_page=184'>دعای 33 صحیفه سجادیه با ترجمه‌ی حسین انصاریان</a>

در ربات آماژه، مانند روش‌های قدیمی استخاره با قرآن، نه حالت برای استخاره‌ی شما وجود دارد که به ترتیب عبارتند از: «خیلی خیلی خوب»، «خیلی خوب»، «خوب»، «میانه خوب»، «میانه»، «میانه بد»، «بد»، «خیلی بد» و «خیلی خیلی بد»
(پس از نیت کردن، دکمه ی «استخاره بگیر» را بزنید).
\n".SIGN;
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'estekhare_main', '', '$text')");
        $this_keyboard = array(
            array('آخرین استخاره های من','استخاره بگیر'),
			array('بازگشت به صفحه اول')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>true,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
    
	//estekhare
	elseif ($text == "استخاره بگیر") {
        $estekhare_query = $db->query("SELECT * FROM quranPages LEFT JOIN quranPagesInterpretation
                                    ON quranPages.quranPage = quranPagesInterpretation.quranPage
                                    WHERE quranPages.translator = 'None' AND quranPages.translateLanguage = '0'
                                    AND  MOD (quranPages.quranPage, 2) = 1
                                        ORDER BY RAND() LIMIT 1");
        $estekhare_file_id = $estekhare_query[0]['fileID'];
        $estekhare_quranPage = $estekhare_query[0]['quranPage'];
        $forwardFromMessageID = $estekhare_query[0]['forwardFromMessageID'];
        $dead_query = $db->query("SELECT * FROM deads ORDER BY RAND() LIMIT 1");
        $dead = $dead_query[0]['deadName'];
        $estekhare_interpretation = $estekhare_query[0]['interpretation'];
        $estekhare_salavat = "\xF0\x9F\x99\x8F لطفا برای شادی روح ".$dead." صلوات بفرستید.";
        $this_text = "\xF0\x9F\x93\x96 نتیجه ی استخاره ی شما : $estekhare_interpretation
<a href='https://t.me/Cxp3adu/$forwardFromMessageID'>&#xA0;</a>
<pre>$estekhare_salavat</pre>
\n".SIGN;
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'estekhare', '$estekhare_quranPage', '$text')");
        $this_keyboard = array(
            array('بازگشت به صفحه اول','بازگشت به صفحه استخاره')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );

       /* MessageRequestJson("sendPhoto", array('chat_id' =>$chat_id,photo=>$estekhare_file_id,
            'caption'=>$this_text, 'reply_markup' =>$reply_markup
        ));*/
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,'parse_mode' => 'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
    
	//last_estekhare
	elseif ($text == "آخرین استخاره های من") {
		$count_estekhare = count($db->query("SELECT activity FROM log WHERE fromID = '$user_id' AND activity = 'estekhare'"));
		if (count ($count_estekhare) == 0) {
			$this_text = "شما تاکنون هیچ استخاره ای نگرفته اید.";
		} else {
			$last_estekhare_query = $db->query("SELECT log.*, quranPagesInterpretation.interpretation 
					FROM log LEFT JOIN quranPagesInterpretation
					ON log.activityNumber = quranPagesInterpretation.quranPage
					WHERE log.fromID = '$user_id' AND log.activity = 'estekhare'
					ORDER BY log.ID DESC LIMIT 10");
			$this_text = "آخرین استخاره های شما "."(شما تاکنون ".$count_estekhare." استخاره گرفته اید";
			if ($count_estekhare > 10)
				$this_text .= " اما فقط 10 استخاره ی آخر به شما نشان داده می شود):";
			else
				$this_text .= "):";
			for ($i = 0 ; $i < count($last_estekhare_query); $i++ ) {
				$row = $i+1;
				$date = new DateTime($last_estekhare_query[$i]['timeof']);
				$date->setTimezone(new DateTimeZone('Asia/Tehran'));
				$time = $date->format('H:i');
				$day = $date->format('Y/m/d');
				$persian_day = $new_persian_date->to_date ($day, 'compelete');
				$this_text .= "\n".$row."- ".$persian_day
					." ساعت ".$time
					." (به وقت تهران)"
					.": پاسخ استخاره‌ی شما، صفحه‌ی ".$last_estekhare_query[$i]['activityNumber']
					." قرآن شده است که یعنی ".$last_estekhare_query[$i]['interpretation'];
			}
		}
		//log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'last_estekhare', '', '$text')");
		$this_keyboard = array(
            array('بازگشت به صفحه استخاره','بازگشت به صفحه اول')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
		MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
	}
	
	//text_analysis_main
	elseif ($text == "تحلیل متن" || $text == "بازگشت به صفحه تحلیل متن"){
        $this_text = "🔧⚒در حال ساخت [آزمایشی] 🔧⚒
متن خود را تحلیل کنید!
کافیه متن خودتون رو همین جا بفرستید (فقط در قالب متن).
\n".SIGN;
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'text_analysis_main', '', '$text')");
        $this_keyboard = array(
            array('بازگشت به صفحه اول')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }
	
	//prosody
	/*elseif ($text = "تحلیل شعر") {
		//log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'prosody', '', '$text')");
		$this_text = prosody();
		$this_keyboard = array(
            array('بازگشت به صفحه اول')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
		MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
	}*/
	
	//text_analysis
	elseif ($previous_activity == "text_analysis_main") {
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
			if ($just_10_characters_counter++ < 10)
				$sorted_all_characters_number .= "کاراکتر «" . $key . "» : ". $value."\n" ;
		}
		//number of words
		//--I'm used this function, no count_word because of persian characters.
		$words_number = count(preg_split('~[\p{Z}\p{P}]+~u', $text, null, PREG_SPLIT_NO_EMPTY));
		//number of each word
		//--I'm used preg_split, no str_split because of persian characters.
		$words = preg_split('~[\p{Z}\p{P}]+~u', $text, null, PREG_SPLIT_NO_EMPTY);
		$words_number_array = array_count_values($words);
		arsort ($words_number_array);
		$just_10_words_counter = 0;
		foreach ($words_number_array as $key => $value) {
			if ($just_10_words_counter++ < 10)
				$sorted_all_words_number .= "واژه ی «" . $key . "» : ". $value."\n" ;
		}
		//number of paragraphs
		$paragraph_number = substr_count( $text, "\n" ) + 1;
		
		$this_text .= "متن شما:
$characters_number کاراکتر، 
$words_number واژه و
$paragraph_number پاراگراف  دارد.\n";
		$this_text .= "\n تعداد تکرار هر واژه (حداکثر 10 واژه‌ی پرتکرار شمرده می‌شود): \n";
		$this_text .= $sorted_all_words_number;
		$this_text .= "\n تعداد تکرار هر کاراکتر (حداکثر 10 کاراکتر پرتکرار شمرده می‌شود): \n";
		$this_text .= $sorted_all_characters_number.
		"\n اگر می خواهید متن دیگری وارد کنید روی «بازگشت  به صفحه تحلیل متن» بزنید.
		\n".SIGN;
		//log
		$db->query("INSERT INTO 
						log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText, botText)
						VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'text_analysis', '$estekhare_quranPage', '$text', '$this_text')");				
		$this_keyboard = array(
            array('بازگشت به صفحه اول','بازگشت به صفحه تحلیل متن')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
	}
	
	//contact_admin
	elseif ($previous_activity == "contact") {
		
		$this_text .= "پیام شما به دست ما رسید و اگر نیاز به پاسخ داشته باشد، در اسرع وقت به آن پاسخ خواهیم داد.
		\n".SIGN;
		//log
		$db->query("INSERT INTO 
						log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText, botText)
						VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'contact_admin', '$estekhare_quranPage', '$text', '$this_text')");		
		$this_keyboard = array(
            array('تماس', 'بازگشت به صفحه اول')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
		//send user text to admin
		$text_contact_admin .= 
		"پیامی از یک کاربر بات آماژه: 
		شناسه کاربر: " .$user_id.
		"\n". "نام و نام خانوادگی کاربر: " .$first_name. " " .$last_name.
		"\n". "نام کاربری: @" .$username.
		"\n" . "پیام کاربر: " ." \n "
		.$text .
		"\n".SIGN;
        MessageRequestJson("sendMessage", array('chat_id' =>ADMIN_ID,'text'=>$text_contact_admin,parse_mode=>'HTML'
        ));
	}
	
	// !!WRONG!!
	else{
        //log
        $db->query("INSERT INTO 
                                log (updateID, messageID, fromID, messageDate, activity, activityNumber, userText)
                                VALUES ('$update_id', '$message_id', '$user_id', '$message_date', 'wrong', '', '$text')");
        $this_text = "اشتباهه!\n".SIGN;
        $this_keyboard = array(
            array('فال حافظ', 'استخاره'),
			array('تحلیل متن'),
            array('درباره','تماس')
        );
        $reply_markup = array(
            'resize_keyboard' =>true,
            'keyboard'=>$this_keyboard
        );
        MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$this_text,disable_web_page_preview=>false,parse_mode=>'HTML',
            'reply_markup' =>$reply_markup
        ));
    }

/*
//Temp
if ($user_id == ADMIN_ID) {
    $file_id = $update["message"]["photo"]["0"]["file_id"];
    $forward_from_message_id = $update["message"]["forward_from_message_id"];
    $message_id = $update["message"]["message_id"];
    $text = "forward id: ".$forward_from_message_id." -- file ID: ".$file_id;

    $db = Db::getInstance();
    $insert = $db->query("INSERT INTO
                                forwardMesaages (forwardFromMessage, fileID)
                                VALUES ('$forward_from_message_id', '$file_id')");
    MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>$text,
        disable_web_page_preview=>false,parse_mode=>'HTML'
    ));
} else {
    MessageRequestJson("sendMessage", array('chat_id' =>$chat_id,'text'=>'ربات آماژه در حال بروزرسانی است. لطفا یک ساعت دیگر مراجعه کنید.',
        disable_web_page_preview=>false,parse_mode=>'HTML'
    ));

}*/

}

?>