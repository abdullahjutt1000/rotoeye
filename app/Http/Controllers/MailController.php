<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller {

	public function send($view, $subject, $data){
		Mail::send('emails.'.$view, $data, function ($message) use ($data, $subject) {
			$message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
			$message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
				->cc('nauman.abid@packages.com.pk', 'M Nauman Abid')
				->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
				->subject($subject);
		});
	}

}
