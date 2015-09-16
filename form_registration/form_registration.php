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
use boolive\core\request\Request;
use boolive\core\values\Rule;
use boolive\core\data\Data;
use boolive\core\session\Session;


class form_registration extends widget
{

    private $_result = 0;//0-Пользователя нет, 1- Успешная регистрация 2- уже есть пользователь с таким email, 3- неизвестная ошибка 4- пользователь есть и подтвержден, 5- Пользователь уже существует, но не активен

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

                   ]),
                    'COOKIE' => Rule::arrays([
                        'token' => Rule::string()->max(32)->default(false)->required()
                    ])
               ]);

       }
        function work(Request $request)
        {
            $user =  Auth::get_user();
            if($user->is_exists()){
                if(!$user->confirm->is_draft()){
                    //новый и еще неактивный
                    $this->_result = 5;
                }else{
                    //Есть такой активный пользователь
                    $this->_result =4;
                }
            //пользователя еще нет
            }else{
                if($request['REQUEST']['form']){
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
                            $session['result'] = $this->_result;
                            Session::set('form', array($this->uri().$this->getToken() => $session));
                            setcookie('token', $this->getToken(), 0, '/');
                            return $session;
                       }else{

                            if (isset($request['COOKIE']['token']) && Session::is_exist('form')){
                                $form = Session::get('form');
                                if (isset($form[$this->uri().$request['COOKIE']['token']])){
                                    $form = $form[$this->uri().$request['COOKIE']['token']];
                                    Session::remove('form');
                                }
                                if (isset($form['result'])){
                                    $this->_result = $form['result'];
                                }
                            }
                            if($this->_result == 1 && isset($user)){
                                $user->password->value($request['REQUEST']['password']);
                                $user->title->value($request['REQUEST']['email']);
                                $user->confirm->proto("/vendor/boolive/basic/string");
                                $user->confirm->value(uniqid('', true));
                                Data::write($user);
                                //Теперь у нас есть такой пользователь
                                $this->_result = 5;
                                $this->mailSender->sendMail($to=$user->email->value(),
                                        $subject= 'Подтвержление регистрации на '.$this->mailSender->domain->value(),
                                        $message= 'Здравствйте, вы зарегистрировались на '.$this->mailSender->domain->value().' , для подтверждения актуальности электронного адреса, перейдите, пожалуйста по <a href="'.$this->mailSender->domain->value().'/profile?confirm='.$user->confirm->value().'">ссылке</a>');
                                $request->redirect('profile?confirm=0');
                            }

                        }

                    }

                }
            return parent::work($request);
        }



    function show($v, Request $request)
    {
        $v['message'] ='';
        if($this->_result==4){
             $v['message'] = 'Вы уже успешно зарегистрированы!';
         }
        if($this->_result==5){
            $v['message'] = 'Вы уже успешно зарегистрированы!  Вам нужно подтвердить свой адресс электронной почты перейдя по ссылке в письме';
        }
         return parent::show($v, $request);
    }

    /**
        * Токен для сохранения в сессию ошибочных данных формы
        * @param bool $remake
        * @return string
        */
       function getToken($remake = false)
       {
           if (!isset($this->_token) || $remake){
               $this->_token = uniqid('', true);
           }
           return (string)$this->_token;
       }

} 