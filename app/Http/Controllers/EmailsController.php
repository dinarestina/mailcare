<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Email;
use PhpMimeMailParser\Parser;
use App\Transformers\EmailTransformer;

class EmailsController extends ApiController
{
    protected $emailTransformer;

    public function __construct(EmailTransformer $emailTransformer)
    {
        $this->emailTransformer = $emailTransformer;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $limit = request()->input('limit') ?: 25;
        $to = request()->input('to');
        $search = request()->input('search');


        $emails = Email::when($to, function ($query) use ($to) {
            return $query->where('to', $to);
        })
        ->when($search, function ($query) use ($search) {
            return $query->where('to', 'like', $search.'%')
            ->orWhere('from', 'like', $search.'%')
            ->orWhere('subject', 'like', $search.'%');
        })
        ->latest()
        ->paginate($limit);

        return $this->respondWithPagination($emails, [
            'data' => $this->emailTransformer->transformCollection($emails->all()),
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $email = Email::find($id);

        if ( ! $email)
        {
            return $this->respondNotFound('Email does not exist.');
        }

        $acceptedHeaders = ['application/json', 'text/html', 'text/plain', 'message/rfc822'];

        if ('text/html' == request()->prefers($acceptedHeaders))
        {
            $parser = new Parser;
            $parser->setPath($email->fullPath());
            return response($parser->getMessageBody('html'))->header('Content-Type', 'text/html; charset=UTF-8');
        }
        elseif ('text/plain' == request()->prefers($acceptedHeaders))
        {
            $parser = new Parser;
            $parser->setPath($email->fullPath());
            return response($parser->getMessageBody('text'))->header('Content-Type', 'text/plain; charset=UTF-8');
        }
        elseif ('message/rfc822' == request()->prefers($acceptedHeaders))
        {;
            return response(file_get_contents($email->fullPath()))->header('Content-Type', 'message/rfc822; charset=UTF-8');
        }
        else
        {
            return $this->respond([
            'data' => $this->emailTransformer->transform($email)
            ]);

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
