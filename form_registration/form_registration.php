<?php
/**
 * Форма регистрации
 *
 * @version 1.0
 * @date 10.08.2015
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace pauline\auth\form_registration;

use boolive\basic\widget\widget;
use boolive\core\auth\Auth;
use boolive\core\data\Entity;
use boolive\core\request\Request;
use boolive\core\values\Rule;
use boolive\core\data\Data;

class form_registration extends widget
{
    private static $config;
    private $_result = 0;//0-Пользователь уже существует, но не активен 1- Успешная регистрация 2- уже есть пользователь с таким email, 3- неизвестная ошибка

    function startRule()
       {
           return
               Rule::arrays([
                   'REQUEST' => Rule::arrays([
                       'form' => Rule::eq($this->uri())->default(false)->required(),
                       'email' => Rule::email()->default(false)->required(),
                       'password' => Rule::string()->default(false)->required(),
                       'passwordAgain' => Rule::string()->default(false)->required(),
                       'call' => Rule::string()->default('')->required(),
                       'path' => Rule::regexp('/^'.preg_quote($this->path->value(),'/').'($|\/)/ui')->required()
                   ]),
               ]);

       }
        function work(Request $request){

            if($request['REQUEST']['form']){
                $user =  Auth::get_user();
                if($user->is_exists()){
                    if(!$user->active){
                        //новый и еще неактивный
                        $this->_result = 0;
                    }else{
                        //Есть такой активный пользователь
                        $this->_result =4;
                    }
                //пользователя еще нет
                }else{
                    //Присвоим email новому пользователю и проверим уникальность
                    $user->email->value($request['REQUEST']['email']);
                    if($request['REQUEST']['call']=='check'){
                        if(!$user->check()){
                            if($user->errors()->email->value->duplicate){
                                $this->_result = 2;
                            }else{
                                //Другая ошибка
                                $this->_result = 3;
                            }
                       }else{
                            //все корректно
                            $this->_result = 1;
                        }

                       return array('result'=>$this->_result);
                   }
                }

                if($this->_result == 1 && isset($user)){
                    $user->password->value($request['REQUEST']['password']);
                    $user->title->value($request['REQUEST']['email']);
                    $user->active->proto("/vendor/boolive/basic/boolean");
                    $user->active->value(false);
                    Data::write($user);
                    //Теперь у нас есть такой пользователь
                    $this->_result = 0;
                    $this->SendMail($user);
                }

            }
            return parent::work($request);
        }



    function show($v, Request $request){
        $v['message'] ='';
        if($this->_result==4){
             $v['message'] = 'Вы уже успешно зарегистрированы!';
         }
        if($this->_result==0){
            $v['message'] = 'Вы уже успешно зарегистрированы!  Вам нужно подтвердить свой адресс электронной почты перейдя по ссылке в письме';
        }
         return parent::show($v, $request);
    }

    /**
     * Функция отправки письма новому пользователю для проверки валидности адреса электронной почты
     * Использует сторонний пакет для простой отправки почты. Планируется замена этого куска  на кусок, использующий
     * внутренний класс адаптер для сторонних библиотек
     * @param $user
     *
     */
    private function SendMail($user)
    {
        $send = false;
        $mail = new \SimpleMail();
        if($this->_result==0){
            $mail->setTo($user->email->value,'')
                ->setSubject('Успешная регистрация на healthcabinet.ru')
                ->setFrom('no-reply@healthcabinet.ru', 'Команда healthcabinet')
                ->addMailHeader('Reply-To', 'no-reply@healthcabinet.ru', 'healthcabinet.ru')
                ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
                ->setMessage('Здравствуйте, вы успешно зарегистрировались на сайте healthcabinet.ru. Вам нужно подтвердить
                    свой email перейдя по ссылке, Если вы не регистрировались, то проигнорируйте это письмо.')
                ->setWrap(1000);
                $send = $mail->send();

        }
        return $send;


    }

} 