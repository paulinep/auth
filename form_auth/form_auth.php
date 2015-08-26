<?php
 namespace pauline\auth\form_auth;

 use boolive\basic\widget\widget;
 use boolive\core\auth\Auth;
 use boolive\core\request\Request;
 use boolive\core\values\Rule;
 use boolive\core\config\Config;
 use boolive\core\data\Data;
 use boolive\core\session\Session;

 class form_auth extends widget{

     private static $config;
     private $_result = 1;

     function startRule(){
         return Rule::arrays([
             'REQUEST' => Rule::arrays([
                'form' => Rule::eq($this->uri())->default(false)->required(),
                 'email' => Rule::email()->default(false)->required(),
                 'password' => Rule::string()->default(false)->required(),
                 'remember-me'=>Rule::bool()->default(false)->required(),
                 'path' => Rule::regexp('/^'.preg_quote($this->path->value(),'/').'($|\/)/ui')->required()
             ]),
             'COOKIE' => Rule::arrays([
                 'token' => Rule::string()->max(32)->default(false)->required()
             ])
         ]);
     }


     function  work(Request $request){
         if ($request['REQUEST']['form']){
             $session = array();
            try{
             self::$config = Config::read('auth');
             $result = Data::find(array(
                     'from' => self::$config['users-list'],
                     'select' => 'children',
                     'depth' => 'max',
                     'where' => array(
                     ['child', 'email',
                        array('value',  '=', $request['REQUEST']['email']),
                      ],
                     [ 'child', 'password',
                         array('value', '=', $request['REQUEST']['password']),
                     ]
                     ),
                     'key' => false,
                     'limit' => array(0, 1),
                     'comment' => 'auth user by email and password',
                          ), false);
             if(!empty($result)){
                 $user = $result[0];
                 Auth::set_user($user);
                 if($request['REQUEST']['remember-me']){
                    Auth::set_user($user, 1234565);
                 }
                 $request->redirect('profile');
                }else{
                     $this->_result = 0;
                 }
            }catch(\Exception $error){
                $this->_result = 0;
            }
            $session['result'] = $this->_result;
            Session::set('form', array($this->uri().$this->getToken() => $session));
            setcookie('token', $this->getToken(), 0, '/');
            return $session;
         }else{
            // Отображение формы
            $v = array();
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
            $this->res->start($request);
            return $this->show($v, $request);
         }
     }

     function show($v, Request $request){
         if($this->_result==0){
             $v['message'] = 'Такого пользователя не существует';
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