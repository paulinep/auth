<?php
/**
 * Класс для отправки електронных писем для регистрации и восстановления пользователей
 *
 * @version 1.0
 * @date 01.09.2015
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace pauline\auth\mailSender;

use boolive\basic\controller\controller;
use boolive\core\request\Request;
use boolive\core\values\Rule;

class mailSender extends controller
{
    function startRule(){
        return Rule::arrays([
              'REQUEST' => Rule::arrays([
                  'to'=>Rule::email()->required(),
                  'subject'=>Rule::string()->required(),
                  'message'=>Rule::string()->required()
              ])
        ]);
    }

    function work(Request $request){
        $result = $this->sendMail($request['REQUEST']['to'],$request['REQUEST']['subject'], $request['REQUEST']['message'] );
        if($result){
            $message =  "На указанный адрес электронной почты выслано письмо, пожалуйста прочтите его";
        }else{
            $message = false;
        }
        return $message;
    }

    /**
     * Функция отправки письма новому пользователю для проверки валидности адреса электронной почты
     * Использует сторонний пакет для простой отправки почты. Планируется замена этого куска  на кусок, использующий
     * внутренний класс адаптер для сторонних библиотек
     * @param $to string кому отправить
     * @param $subject string тема письма
     * @param $message string текст письма
     * @return bool
     */
    private function SendMail($to, $subject, $message)
    {
        $send = false;
        $mail = new \SimpleMail();
        if($this->_result==0){
            $mail->setTo($to,'')
                ->setSubject($subject)
                ->setFrom('no-reply@healthcabinet.ru', 'Команда healthcabinet')
                ->addMailHeader('Reply-To', 'no-reply@healthcabinet.ru', 'healthcabinet.ru')
                ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
                ->setMessage($message)
                ->setWrap(1000);
                $send = $mail->send();

        }
        return $send;

    }


} 