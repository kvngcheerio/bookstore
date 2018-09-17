<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use App\Http\Controllers\Controller;
use App\Api\V1\Models\MessageTemplate;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageTemplateController extends Controller
{
    /**
    * get all message templates
    *
    * @param App\Api\V1\Models\MessageTemplate $messageTemplate
    *
    * @return  Dingo\Api\Http\Response
     */
    public function index(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate = $messageTemplate->all()) {
            return $messageTemplate;
        }
        throw new NotFoundHttpException( 'No message templates found' );
    }
    /**
    * store new created message template
    *
    * @param Illuminate\Http\Request $request
    * @param App\Api\V1\Models\MessageTemplate $messageTemplate
    *
    * @return  Dingo\Api\Http\Response
     */
    public function store(Request $request, MessageTemplate $messageTemplate)
    {
        $this->validate($request, [
            'slug' => 'required|string|unique:message_templates,slug',
            'description' => 'required|string',
            'subject' => 'required|string',
            'template' => 'required|string'
        ]);

        if ($messageTemplate->create($request->only('slug', 'description', 'subject', 'template'))) {
            return new Response(['status'=>  'message template created'], 201);
        }
        throw new StoreResourceFailedException('message template couldn\'t be stored');
    }
    /**
     * Display the specified message template.
     *
     * @param App\Api\V1\Models\MessageTemplate $messageTemplate
     * @param  int $id
     *
     * @return  Dingo\Api\Http\Response
     */
    public function show(MessageTemplate $messageTemplate, $id)
    {
        if ($messageTemplate = $messageTemplate->find($id)) {
            return $messageTemplate;
        }
        throw new NotFoundHttpException('message template not found');
    }

    /**
    * update a message template
    *
    * @param Illuminate\Http\Request $request
    * @param App\Api\V1\Models\MessageTemplate $messageTemplate
    * @param int $id
    *
    * @return  Dingo\Api\Http\Response
     */
    public function update(Request $request, MessageTemplate $messageTemplate, $id)
    {
        $this->validate($request, [
            'description' => 'required|string',
            'subject' => 'required|string',
            'template' => 'required'
        ]);

        $messageTemplate = $messageTemplate->findOrFail($id);

        $messageTemplate->description = $request->description;
        $messageTemplate->subject = $request->subject;
        $messageTemplate->template = $request->template;

        if ($messageTemplate->save()) {
            return new Response(['status'=>  'message template update successfully'], 201);
        }
        throw new StoreResourceFailedException('message template update failed');
    }
    /**
    * delete a message template
    *
    * @param App\Api\V1\Models\MessageTemplate $messageTemplate
    * @param int $id
    *
    * @return  Dingo\Api\Http\Response
     */
    public function destroy(MessageTemplate $messageTemplate, $id)
    {
        if (! $messageTemplate = $messageTemplate->find($id)) {
            throw new DeleteResourceFailedException('message template not found');
        }
        //delete message template
        if ($messageTemplate->delete()) {
            return new Response(['status'=>  'message template deleted' ], 201);
        }
        
        //message template not deleted
        throw new DeleteResourceFailedException('message template delete request failed');
    }
}
