<?php
/**
 * Название
 *
 * @version 1.0
 * @date 30.08.2015
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace pauline\auth\profile\confirm;

use boolive\basic\user\user;
use boolive\basic\widget\widget;
use boolive\core\config\Config;
use boolive\core\data\Data;
use boolive\core\request\Request;
use boolive\core\values\Rule;
use pauline\auth\mailSender;

class confirm extends widget
{
    private static $config;


    function  startRule(){
        return Rule::arrays([
                'REQUEST'=>Rule::arrays([
                            'confirm'=>Rule::string()->default(false)->required(),
                            'sendAgain'=>Rule::email()->default(false)->required(),
                            'object'=>Rule::entity()->default(false)
                        ])
        ]);
    }

    private  function searchUser($confirm){
        self::$config = Config::read('auth');
        $search_result = Data::find(array(
                'from' => self::$config['users-list'],
                'select' => 'children',
                'depth' => 'max',
                'where' => array(
                ['child', 'confirm',
                   array('value',  '=', $confirm),
                 ],
                 ),
                'key' => false,
                'limit' => array(0, 1),
                'comment' => 'search  user by confirm property',
                     ), false);
           if(!empty($search_result)){
              $user =  $search_result[0];
           }else{
               $user = false;
           }

        return $user;
    }
    /**
    * Функция, подтверждения корректрости электронного адреса пользователя
    * Находит его по уникальной последовательности в поле confirm и удаляет поле тем самым активируя пользователя
    * @param $confirm
     * @return integer результат операции, обработка результата в функции work
    */
    private  function confirmUser($confirm){
        /** @var user $user */
        $user =  $this->searchUser($confirm);
        if($user && !$user->confirm->is_draft()){
            $user->confirm->is_draft(true);
            Data::write($user->confirm);
            //Успешное подтверждение
            $result = 1;
        }else{
            //Пользователь уже существует
            if($user && $user->confirm->is_draft()){
               $result = 4;
            }else{
                //Нет пользователя с таким значением - неизвестно почему
                $result = 3;
            }
        }
        return $result;
    }

    function work(Request $request){
        $v = array();
        if($request['REQUEST']['confirm']){
            if($request['REQUEST']['sendAgain']){
                $user = $this->searchUser($request['REQUEST']['confirm']);
                if($user){
                   $mail =  $this->sendAgain($to=$user->email->value(),
                            $subject = 'Подтвержление регистрации на '.$this->mailSender->domain->value(),
                            $message='Здравствйте, вы зарегистрировались на '.$this->mailSender->domain->value().', для подтверждения актуальности электронного адреса, перейдите, пожалуйста по <a href="'.$this->mailSender->domain->value().'/profile?confirm='.$user->confirm->value().'">ссылке</a>');
                    if($mail){
                        $v['message'] = "Письмо успешно отправлено";

                    }
                }
            }
            $result = $this->confirmUser($request['REQUEST']['confirm']);
            switch($result){
                case 1:
                $v['message'] = "Успешное подтверждение адреса";
                    break;
                case 3:
                    $v['message'] = "Нет пользователя с таким уникальным ключом ";
            }
            return $this->show($v, $request);
        }else{
            //Если профиль передал этому виджету уже пользователя, а не пользователь пришел по ссылке
          if($request['REQUEST']['object']){
              $user = $request['REQUEST']['object'];
              if(!$user->confirm->is_draft()){
                  $v['message'] = "Вам выслано письмо на адрес".$user->email->value()." перейдите по указанной в письме ссылке, чтобы подтвердить email, если письмо не пришло  нажмите <a href='profile?confirm=".$user->confirm->value()."&sendAgain=".$user->email->value()."'>сюда</a>";
                  return $this->show($v, $request);
              }else{
                  return false;
              }
          }
        }
        return parent::work($request);
    }

    function show($v, $request){
        return parent::show($v, $request);
    }


    private function sendAgain($to, $subject, $message){

        return $this->mailSender->sendMail($to, $subject, $message);

    }

} 