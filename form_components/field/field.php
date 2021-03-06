<?php
/**
 * Поле формы, которое отображает только Имя пользователя, email, пароль
 *
 * @version 1.0
 * @date 06.08.2015
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace pauline\auth\form_components\field;

use boolive\core\values\Rule;

class field extends \boolive\forms\field\field
{

    function startRule(){
        return parent::startRule()->mix(
                    Rule::arrays([
                        'REQUEST' => Rule::arrays([
                            'object' => Rule::any([
                                            Rule::entity(['is','/vendor/boolive/basic/user/title']),
                                            Rule::entity(['is','/vendor/boolive/basic/user/email']),
                                            Rule::entity(['is','/vendor/boolive/basic/user/password']),
                                    ]),
                        ])
                    ])
                );
    }
} 