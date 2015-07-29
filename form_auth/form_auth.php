<?php
 namespace pauline\auth\form_auth;

 use boolive\basic\widget\widget;
 use boolive\core\auth\Auth;
 use boolive\core\request\Request;
 use boolive\core\values\Rule;
 use boolive\core\config\Config;
 use boolive\core\data\Data;

 class form_auth extends widget{

     private static $config;

     function startRule(){
         return Rule::arrays([
             'REQUEST' => Rule::arrays([
                'form' => Rule::eq($this->uri())->default(false)->required(),
                 'email' => Rule::string()->default(false)->required(),
                 'password' => Rule::string()->default(false)->required(),
                 'remember-me'=>Rule::bool()->default(false)->required(),
                 'path' => Rule::regexp('/^'.preg_quote($this->path->value(),'/').'($|\/)/ui')->required()
             ]),
         ]);
     }


     function  work(Request $request){
         if ($request['REQUEST']['form']){
             self::$config = Config::read('auth');
             $result = Data::find(array(
                     'from' => self::$config['users-list'],
                     'select' => 'properties',
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
             }
             if($user){
                 Auth::set_user($user);
                if($request['REQUEST']['remember-me']){
                    Auth::set_user($user, 1234565);
                 }
                $request->redirect('profile');
            }else{
                 $v['message'] = 'Такого пользователя не существует';
                 return parent::work($request);
             }
         }else{
            return parent::work($request);
         }
     }
 }