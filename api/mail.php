<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    /**
     * Send a message
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     *
     * @return mixed
     */
    public static function send($to, $name, $subject, $html)
    {
      $mail = new PHPMailer(true);
      $mail->CharSet = "UTF-8";

      try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $_ENV["HOST_SMTP"];                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $_ENV["USER_MAIL"];                     //SMTP username
        $mail->Password   = $_ENV["PASS_MAIL"];                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('contacto@ecoapplet.co', 'JUSTICE APP te Contacta');
        $mail->addAddress($to, $name);     //Add a recipient se pueden agregar más correos con más linesas de estas        
        //$mail->addReplyTo('info@ecoapplet.co', $name);
        //$mail->addCC('info@ecoapplet.co', $name);
        $mail->addBCC('developer@ecoapplet.co', $name);
    
        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $html;     //'This is the HTML message body <b>in bold!</b>';
       
    
        $mail->send();
        return true;
      } catch (Exception $e) {
        echo json_encode('El mensaje no pudo ser enviado. Error: '. $mail->ErrorInfo);
      }   
        
    }
}