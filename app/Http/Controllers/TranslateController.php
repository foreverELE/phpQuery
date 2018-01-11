<?php

namespace App\Http\Controllers;

use App\Libraries\Translation;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    //
    public function index()
    {

        $res=Translation::translate('苹果', 'zh', 'en');
        dd($res);
    }

}
