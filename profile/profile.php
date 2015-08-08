<?php
namespace pauline\auth\profile;

use boolive\basic\widget\widget;
use boolive\core\auth\Auth;
use boolive\core\request\Request;
use boolive\core\values\Rule;


class profile extends widget{

    function startRule()
    {
        return Rule::arrays([
            'REQUEST' => Rule::arrays([
                'path' => Rule::regexp('/^'.preg_quote($this->path,'/').'($|\/)/ui')->required()
            ])
        ]);
    }

    function work(Request $request)
    {
        $user = Auth::get_user();
        $request->mix(['REQUEST' => ['object' => $user]]);
        return parent::work($request);
    }

    function show($v, Request $request)
    {
        $v['user_form'] = $this->user_form->start($request);
        return parent::show($v, $request);
    }
}
 