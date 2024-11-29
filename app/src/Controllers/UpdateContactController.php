<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use DateTime;

class UpdateContactController extends AbstractController {

    public function process(Request $request): Response {
        // check if the request content type is application/json
        $headers = $request->getHeaders();
        if ($headers['Content-Type'] !== 'application/json') {
            return new Response(json_encode(['error' => 'Invalid Content-Type']), 400);
        }

        $uri = $request->getUri();
        
        if (!preg_match('/^\/contact\/(.+)$/', $uri, $matches)) {
            return new Response(json_encode(['error' => 'Invalid URI']), 400);
        }

        $email = $matches[1];

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

        // set the data to check in the request body
        $data = json_decode(file_get_contents('php://input'), true);

        // validate the request body
        $allowedKeys = ['email', 'subject', 'message'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedKeys)) {
                return new Response(json_encode(['error' => 'Invalid request body']), 400);
            }
        }

        // read the contact file
        $contact = json_decode(file_get_contents($contactFile), true);

        // update the contact data
        foreach ($data as $key => $value) {
            $contact[$key] = $value;
        }
        $contact['dateOfLastUpdate'] = (new DateTime())->getTimestamp();

        // save the updated contact to the file
        file_put_contents($contactFile, json_encode($contact));

        // send the response
        return new Response(json_encode($contact), 200);
    }
}