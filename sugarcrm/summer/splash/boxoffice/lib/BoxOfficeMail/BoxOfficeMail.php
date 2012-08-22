<?php



class BoxOfficeMail
{

    public static function sendTemplate($to, $template, $vars=array()){
        foreach($vars as $k=>$v){
            $$k = $v;
        }
        require(dirname(__FILE__).'templates/email.' . $template . '.php' );
        self::sendMail($to, $subject, $txt, $message);


    }
    public static function sendMail($to, $subject, $plainText, $html)
    {
        $plainText = trim($plainText);
        $html = trim($html);
        $boundary = uniqid("SUMMER");
        $headers = <<<EOQ
From: no-reply@sugarcrm.com
MIME-Version: 1.0
Content-Type: multipart/alternative;boundary="$boundary"\r\n\r\n
EOQ;

        $body = <<<EOQ
\r\n
--$boundary
Content-Type: text/plain

$plainText
--$boundary
Content-Type: text/html

$html
--$boundary--
EOQ;

//send message
        mail($to, $subject, $body, $headers);

    }
}