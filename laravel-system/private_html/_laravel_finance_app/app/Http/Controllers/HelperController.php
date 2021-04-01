<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

class HelperController extends Controller
{
    public static function sendEmail($dataEmail)
    {
        
        \Mail::send([], [], function ($message) use ($dataEmail) {
            $message->from($dataEmail['fromEmail'], $dataEmail['fromName']);
            $message->to($dataEmail['toEmail']);
            $message->subject($dataEmail['subject']);
            $message->setBody($dataEmail['content'], 'text/html');
        });
        
    }
}