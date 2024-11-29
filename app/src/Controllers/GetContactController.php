<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class GetContactController extends AbstractController {

    public function process(Request $request): Response {
        // check if the request content type is application/json
        $headers = $request->getHeaders();
        if ($headers['Content-Type'] !== 'application/json') {
            return new Response(json_encode(['error' => 'Invalid Content-Type']), 400);
        }

        // get the contact email from the uri
        $uri = $request->getUri();
        
        if (!preg_match('/^\/contact\/(.+)$/', $uri, $matches)) {
            return new Response(json_encode(['error' => 'Invalid URI']), 400);
        }
        $email = $matches[1];

        // set the directory to read the contact files
        $directory = __DIR__ . "/../../var/contacts";

        // find the contact file by email
        $contactFile = null;
        foreach (glob("{$directory}/*_{$email}.json") as $filename) {
            $contactFile = $filename;
            break;
        }

        // check if the contact file exists
        if (!$contactFile) {
            return new Response(json_encode(['error' => 'Contact not found']), 404);
        }

        // read the contact file
        $contact = json_decode(file_get_contents($contactFile), true);

        // send the response
        return new Response(json_encode($contact), 200);
    }
}