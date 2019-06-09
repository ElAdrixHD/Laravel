<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactEmail;
use App\Http\Requests\ContactFormRequest;
use Exception;

class ContactController extends Controller
{
    public function create()
    {
        return view('contact.create');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ContactFormRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ContactFormRequest $request)
    {
        $contact = [];
        $contact['name'] = $request->get('name');
        $contact['email'] = $request->get('email');
        $contact['message'] = $request->get('message');

        try {
            Mail::to(config('mail.from.address'))->send(new ContactEmail($contact));
            $status = 'success';
            $message = 'Thanks for contacting us!';
        } catch (Exception $exception) {
            $status = 'error';
            $message = 'Error: ' . $exception->getMessage();
        }
      return back()->with($status, $message);      
    }
}
