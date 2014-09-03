<?php
/**
 * Copyright (c) 2014 Adam L. Englander
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software
 * is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

// Init our db connection and other stuff
require_once '../bootstrap.php';

// Init our request/response objects
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use LVPHP\Models\Topic;

$request = Request::createFromGlobals();

$response = new Response(
    'Content',
    Response::HTTP_OK,
    array('content-type' => 'text/html')
);

// Initialize our variables used in the template
$errors = array();
$header = null;
$body = null;
$topics = null;
$upvote = null;
$responseContent = null;

// Add a generic try catch around the entire PHP code section to ensure we catch any errors
try {

    if ($request->isMethod('POST')) {

        // Check for upvote on topic
        $topic_id = $request->get('topic_id');
        if ($topic_id) {
            $topic = $entityManager->getRepository('LVPHP\Models\Topic')->findOneById($topic_id);
            $vote = new \LVPHP\Models\Vote($topic, $request->getClientIp());
            $entityManager->persist($vote);
            $entityManager->flush();

        } else {

            $header = $request->get('header');
            $body = $request->get('body');

            if (empty($header)) {
                $errors[] = 'Please enter a header for the topic.';
            }

            if (empty($body)) {
                $errors[] = 'A description of the topic is required.';
            }

            // If the form post had no errors then we will store the data in the database
            if (!$errors) {

                // Use try/catch to catch any errors in saving the registration
                try {
                    $topic = new \LVPHP\Models\Topic($header, $body, $request->getClientIp());
                    $vote = new \LVPHP\Models\Vote($topic, $request->getClientIp());
                    $entityManager->persist($vote);
                    $entityManager->flush();
                    // Since the topic was created successfully, clear the form values to allow for a new topic idea
                    $header = null;
                    $body = null;
                } catch (\Doctrine\ORM\ORMInvalidArgumentException $e) {
                    // Set status code to 500 due to server error
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                    $message = sprintf(
                        'Unable to save topic: %s: Topic info: [header: %s, description: %s]',
                        $e->getMessage(),
                        $header,
                        $body
                    );

                    // Throw new Exception with descriptive error message so the default handler will properly log an display the error
                    throw new Exception($message, 0, $e); // Set the caught exception as the previous
                }
            }
        }
    }

    // Get all the topics AFTER we made one or w/e
    $topics = $entityManager->getRepository('LVPHP\Models\Topic')->findBy(array('status' => Topic::ACTIVE));

} catch (Exception $e) {
    // The default/master exception handler will log the error and display to the user

    // Generate a Unique ID to identify this error
    $errorId = uniqid('ERROR-');

    // Add a nondescript error to the errors to show the user and include the error ID for reference
    $errors[] = sprintf('An application error occurred [%s]', $errorId);
    if ($isDevMode) {
        $errors[] = sprintf('Error message : %s $s Trace : %s', $e->getMessage(), PHP_EOL, $e->getTraceAsString());
    }
    error_log(sprintf('%s: %s', $errorId, $e->getMessage()));
}

$responseContent = '
<!DOCTYPE html>
<html>
<body>
<div>
    <h1>Las Vegas PHP User Group - LVPHP.org</h1>
</div>
<div>
    <h2>Topics Picker</h2>
    <p>To suggest a topic, simply enter a title and description of what you want to hear about.</p>';

// If there are errors, display them to the user
if (!empty($errors)) {

    $responseContent .= '<div class="errors">
                            <h3 class="error-heading">Errors were encountered wth your topic</h3>
                            <ul>';

// Loop through the errors array
    foreach ($errors as $error) {
        $responseContent .= "<li>{$error}</li>";
    }
    $responseContent .= '</ul></div>';
}

$responseContent .= '<form method="POST">
        <div>
            <label for="header">Title: </label>
            <input id="header" name="header" value="'. htmlentities($header) .'">

            <label for="body">Description: </label>
            <input id="body" name="body" value="' . htmlentities($body) .'">

            <input type="submit" value="Create Topic">
        </div>
    </form>
</div>
<div class="topics-list">';

    if ($topics) {
        /**
         * @var $topic Topic
         */
        foreach ($topics as $topic) {
            $responseContent .= '<hr />';
            $responseContent .= '<h4> Title : ' . htmlentities($topic->getHeader()) . '</h4>';
            $responseContent .= '<p> Description : ' . htmlentities($topic->getBody()) . '</p>';
            $responseContent .= '<h4> Votes : ' . $entityManager->getRepository('LVPHP\Models\Vote')->getTotalVotesForTopic($topic) . '</h4>';
            $vote = $entityManager->getRepository('LVPHP\Models\Vote')->userHasVoted($topic, ip2long($request->getClientIp()));
            if (empty($vote)) {
                $responseContent .= '<form method="POST">
                                        <input type="hidden" name="topic_id" value="' . $topic->getId() .'">
                                        <input type="submit" value="Up Vote">
                                     </form>';
            }
        }
    } else {
        $responseContent .= '<h4>There are currently no topics.</h4>';
    }

$responseContent .= '</div>
</body>
</html>';

// Set and send response
$response->setContent($responseContent);
$response->send();