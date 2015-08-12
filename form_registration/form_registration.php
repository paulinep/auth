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
use boolive\core\request\Request;
use boolive\core\values\Rule;

class form_registration extends widget
{
    function startRule()
       {
           return
               Rule::arrays([
                   'REQUEST' => Rule::arrays([
                       'form' => Rule::eq($this->uri())->default(false)->required(),
                       'path' => Rule::regexp('/^'.preg_quote($this->path->value(),'/').'($|\/)/ui')->required()
                   ]),
               ]);

       }
    function work(Request $request){

    }

} 