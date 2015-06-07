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
    array( 'content-type' => 'text/html' )
);

// Initialize our variables used in the template
$errors          = array();
$header          = null;
$body            = null;
$topics          = null;
$upvote          = null;
$responseContent = null;

// Add a generic try catch around the entire PHP code section to ensure we catch any errors
try {

    if ($request->isMethod( 'POST' )) {

        // Check for upvote on topic
        $topic_id = $request->get( 'topic_id' );
        if ($topic_id) {
            $topic = $entityManager->getRepository( 'LVPHP\Models\Topic' )->findOneById( $topic_id );
            $vote  = new \LVPHP\Models\Vote( $topic, $request->getClientIp() );
            $entityManager->persist( $vote );
            $entityManager->flush();

        } else {

            //Check captcha first
            $clientIp = $request->getClientIp();
            $gRecaptchaResponse = $request->get( 'g-recaptcha-response' );

            $guzzleClient = new \Guzzle\Service\Client();

            $recaptchaRequest = $guzzleClient->post('https://www.google.com/recaptcha/api/siteverify', null, array(
                    'secret' => $recaptchaApiKey,
                    'response' => $gRecaptchaResponse,
                    'remoteip' => $clientIp
            ));
            $recaptchaResponse = $recaptchaRequest->send()->json();

            $header = $request->get( 'header' );
            $body   = $request->get( 'body' );

            if ($recaptchaResponse['success']) {

                if (empty( $header )) {
                    $errors[] = 'Please enter a header for the topic.';
                }

                if (empty( $body )) {
                    $errors[] = 'A description of the topic is required.';
                }

                // If the form post had no errors then we will store the data in the database
                if ( ! $errors) {

                    // Use try/catch to catch any errors in saving the registration
                    try {
                        $topic = new \LVPHP\Models\Topic( $header, $body, $clientIp );
                        $vote  = new \LVPHP\Models\Vote( $topic, $clientIp );
                        $entityManager->persist( $vote );
                        $entityManager->flush();
                        // Since the topic was created successfully, clear the form values to allow for a new topic idea
                        $header = null;
                        $body   = null;
                    } catch ( \Doctrine\ORM\ORMInvalidArgumentException $e ) {
                        // Set status code to 500 due to server error
                        $response->setStatusCode( Response::HTTP_INTERNAL_SERVER_ERROR );
                        $message = sprintf(
                            'Unable to save topic: %s: Topic info: [header: %s, description: %s]',
                            $e->getMessage(),
                            $header,
                            $body
                        );

                        // Throw new Exception with descriptive error message so the default handler will properly log an display the error
                        throw new Exception( $message, 0, $e ); // Set the caught exception as the previous
                    }
                }
            } else {
                $errors[] = 'Failed to verify captcha input, try again.';
            }
        }
    }

    // Get all the topics AFTER we made one or w/e
    $topics = $entityManager->getRepository( 'LVPHP\Models\Topic' )->findBy( array( 'status' => Topic::ACTIVE ) );

} catch ( Exception $e ) {
    // The default/master exception handler will log the error and display to the user

    // Generate a Unique ID to identify this error
    $errorId = uniqid( 'ERROR-' );

    // Add a nondescript error to the errors to show the user and include the error ID for reference
    $errors[] = sprintf( 'An application error occurred [%s]', $errorId );
    if ($isDevMode) {
        $errors[] = sprintf( 'Error message : %s $s Trace : %s', $e->getMessage(), PHP_EOL, $e->getTraceAsString() );
    }
    error_log( sprintf( '%s: %s', $errorId, $e->getMessage() ) );
}
// include header
include '../php_includes/header.php';
$responseContent = '
<!-- About Us Begin -->
<div class="section-odd">
<a name="about" href="#about"></a>
<h1 class="section-header">About Us</h1>
<p class="about-text">The Las Vegas PHP Users Group is a group dedicated to PHP developers learning from and teaching each others. Many PHP developers are experts in one segment or another. This group is an opportunity for all of us to teach what we know well and learn something new. All skill levels are sought after. If you are looking to teach, learn, network, or just mingle, join the group and participate on the adventure.</p>
</div>

<!-- About US End -->

<!-- Begin Meetups -->
<div class="section-even">
<a name="meetup" id="meetup" href="#meetup"></a>
<h1 class="section-header">Upcoming Meetups</h1>
';
/**
 * Use Meetup.com API to retrieve upcoming meetups for the Las-Vegas-PHP-Users-Group.
 */
try {

    /* @var \DMS\Service\Meetup\Response\MultiResultResponse $events */
    $events = $meetupClient->getEvents( array(
        'group_urlname' => 'Las-Vegas-PHP-Users-Group'
    ) );
    $events = $events->getData();
    if (!empty($events)) {
        for ($i = 0; $i < 2; $i ++) {
            $event = $events[$i];
//            var_dump($event);
            $responseContent .= '<div class="media">

              <a class="pull-left meetup" href="' . $event['event_url'] . '" target="_blank">
                <button class="btn btn-danger meetup" type="button">
                <span class="meetup-date">' . date("l M jS",($event['time'])/1000) ."<br/>" .date("g:i A",($event['time'])/1000) . '</span>
                </button>

              </a>

              <div class="media-body">
                <h3 class="media-heading">' . $event['name'] . '</h3>

                <span class="text-muted">' . $event['description'] . '</span>
                <p><a href="' . $event['event_url'] . '" target="_blank">
                <button class="btn btn-danger" type="button">
                <span class="meetup-date">RSVP</span> to join <span class="badge">' . $event['yes_rsvp_count'] . ' others</span>
                </button>
';

            if (isset($event['venue'])) {
                $responseContent .= '</a></p>
                              Location: <br/>
                                <a href="https://www.google.com/maps/place/' . $event['venue']['address_1'] . ',' . $event['venue']['city'] . ',' . $event['venue']['state'] . '" target="_blank">' . $event['venue']['name'] . '<br/>' . $event['venue']['address_1'] . '<br/>' . $event['venue']['city'] . ', ' . $event['venue']['state'] . '
                                </a>';
            }

            $responseContent .= '</div>
                </div><div class="meetup-border"></div>';

        }
    } else {
        $responseContent .= '<h4>No Events Currently Scheduled</h4>';
    }
} catch ( Exception $e ) {
    // The default/master exception handler will log the error and display to the user

    // Generate a Unique ID to identify this error
    $errorId = uniqid( 'ERROR-' );

    // Add a nondescript error to the errors to show the user and include the error ID for reference
    $errors[] = sprintf( 'An application error occurred [%s]', $errorId );
    if ($isDevMode) {
        $errors[] = sprintf( 'Error message : %s $s Trace : %s', $e->getMessage(), PHP_EOL, $e->getTraceAsString() );
    }
    error_log( sprintf( '%s: %s', $errorId, $e->getMessage() ) );
}
$responseContent .= '
</div>


<!-- End Meetups-->
<!-- Begin Topic Picker -->
<div class="section-even">
<a name="topic_picker" id="topic_picker" href="topic_picker"></a>
<div>
    <h1 class="section-header">Topics Picker</h1>
    <p>To suggest a topic, simply enter a title and description of what you want to hear about.</p>';

// If there are errors, display them to the user
if ( ! empty( $errors )) {

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
        <div id="topic-form">
            <div class="g-recaptcha" data-sitekey="6LdUyAQTAAAAAObr4yIrSBqCf98CE0fcdOAR63j3"></div>

            <label for="header">Title: </label>
            <input id="header" name="header" value="' . htmlentities( $header ) . '">

            <label for="body">Description: </label>
            <input id="body" name="body" value="' . htmlentities( $body ) . '">


            <input type="submit" value="Create Topic">
        </div>
    </form>
</div>
<div id="topics-list">';

if ($topics) {
    /**
     * @var $voteRepository LVPHP\Repositories\VoteRepository
     */
    $voteRepository = $entityManager->getRepository( 'LVPHP\Models\Vote' );
    /**
     * @var $topic Topic
     */
    foreach ($topics as $topic) {
        $responseContent .= '<hr />';
        $responseContent .= '<h4> Title : ' . htmlentities( $topic->getHeader() ) . '</h4>';
        $responseContent .= '<p> Description : ' . htmlentities( $topic->getBody() ) . '</p>';
        $responseContent .= '<h4> Votes : ' . count( $voteRepository->findAllVotesForTopic( $topic ) ) . '</h4>';
        $vote = $voteRepository->findVoteFromTopicBasedOnIP( $topic, $request->getClientIp() );
        if (empty( $vote )) {
            $responseContent .= '<form method="POST">
                                        <input type="hidden" name="topic_id" value="' . $topic->getId() . '">
                                        <input type="submit" value="Up Vote">
                                     </form>';
        }
    }
} else {
    $responseContent .= '<h4>There are currently no topics.</h4>';
}

$responseContent .= '</div></div>
<div class="section-odd">
<a name="sponsors" id="sponsors" href="#sponsors"></a>
<h1 class="section-header">Sponsors</h1>
<div class="row">
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="img/logo_jetbrains.png" alt="Jetbrains">
      <div class="caption">
        <h3 class="thumbnail-title">JetBrains</h3>
        <p>JetBrains provides licenses for PHPStorm that are raffled away at main events.</p>
        <p><a href="http://www.jetbrains.com/" target="_blank" class="btn btn-primary" role="button">Learn More</a> </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="img/logo_estrella.png" alt="Estrella Insurance">
      <div class="caption">
        <h3 class="thumbnail-title">Estrella Insurance</h3>
        <p>Local Insurance company Estrella Insurance pays for Meetup.com expenses.</p>
        <p><a href="http://www.directoestrella.com/" target="_blank" class="btn btn-primary" role="button">Learn More</a> </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="img/logo_innovation.png" alt="InNEVation Center">
      <div class="caption">
        <h3 class="thumbnail-title">InNEVation Center</h3>
        <p>InNEVation Center provides the co-working space for our meetups.</p>
         <p><a href="http://www.innevation.com/" target="_blank" class="btn btn-primary" role="button">Learn More</a> </p>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</body>
';

//include footer
include '../php_includes/footer.php';
// Set and send response
$response->setContent( $responseContent );
$response->send();