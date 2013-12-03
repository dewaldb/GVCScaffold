<?php

require_once('swift_lib/swift_required.php');

class Mailer {
    static function send($to,$cc,$bcc,$from,$subject,$message) {
        if(isset($_GET["dev"])) {
            return Mailer::swiftSend($to,$cc,$bcc,$from,$subject,$message);
        }
        $xheaders = "";
        $xheaders .= "From: <$from>\n";
        $xheaders .= "X-Sender: <$from>\n";
        $xheaders .= "X-Mailer: PHP\n"; // mailer
        $xheaders .= "X-Priority: 3\n"; //1 Urgent Message, 3 Normal
        $xheaders .= "Content-Type:text/html; charset=\"iso-8859-1\"\n";
        $xheaders .= "Bcc:{$bcc}\n";
        $xheaders .= "Cc:{$cc}\n";
        return mail($to, $subject, $message, $xheaders);
    }
    
    // cc and bcc not implemented yet!
    static function swiftSend($to,$cc,$bcc,$from,$subject,$message) {
        // Create the Transport
        $transport = Swift_SmtpTransport::newInstance('smtp.hindsfeet.co.za', 25)
                        ->setUsername('webmaster@hindsfeet.co.za')
                        ->setPassword('HFMasterCaps#2!');

        /*
        You could alternatively use a different transport such as Sendmail or Mail:

        // Sendmail
        $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

        // Mail
        $transport = Swift_MailTransport::newInstance();
        */

        // Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);

        // Create a message
        $message = Swift_Message::newInstance($subject)
                        ->setFrom(array($from))
                        ->setTo(array($to))
                        ->setBody($message,'text/html');

        // Send the message
        return $mailer->send($message);
    }
}

?>