<?php
/**
 * Класс для отправки електронных писем для регистрации и восстановления пользователей
 *
 * @version 1.0
 * @date 01.09.2015
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace pauline\auth\mailSender;

use boolive\core\data\Entity;

class mailSender extends Entity
{


    /**
     * Функция отправки письма новому пользователю для проверки валидности адреса электронной почты
     * Использует сторонний пакет для простой отправки почты. Планируется замена этого куска  на кусок, использующий
     * внутренний класс адаптер для сторонних библиотек
     * @param $to string кому отправить
     * @param $subject string тема письма
     * @param $message string текст письма
     * @return bool
     */
    public  function sendMail($to, $subject, $message)
    {
        $mail = new \SimpleMail();
            $mail->setTo($to,'')
                ->setSubject($subject)
                ->setFrom('no-reply@healthcabinet.ru', 'Команда healthcabinet')
                ->addMailHeader('Reply-To', 'no-reply@healthcabinet.ru', 'healthcabinet.ru')
                ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
                ->setMessage($message)
                ->setWrap(1000);
                $send = $mail->send();

        return $send;

    }


} 