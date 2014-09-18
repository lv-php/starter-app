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
use LVPHP\Meetup\Meetup;

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



$responseContent = '
<!DOCTYPE html>
<html>
<head>
<title>Las Vegas PHP Users Group - LVPHP.org</title>
<meta charset="UTF-8">
<meta name="description" content="Las Vegas PHP Users Group is a community of PHP developers looking to share and learn. All events are free to attend and can be found here.">
<meta name="keywords" content="Las Vegas PHP Users Group">
<meta name="author" content="The LVPHPUG Community">
<!-- JQUERY UI CSS -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css">
<!-- Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/bootstrap-theme.min.css">
<link rel="stylesheet" href="css/lvphp_custom.css">
<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico"/>
</head>
<body>


<div class="container" id="page_container">
<!--NavBar Start     -->
<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>

      </button>
      <a class="navbar-brand" href="#index.php">LVPHP.org</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="index.php#about">About Us</a></li>
        <li><a href="index.php#meetup">Meetups</a></li>
         <li><a href="index.php#topic_picker">Upcoming Topics</a></li>
          <li><a href="#sponsors">Sponsors</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">More<span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="discussions.php#discussion">Discussions</a></li>
            <!--<li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li class="divider"></li>
            <li><a href="#">One more separated link</a></li>-->
          </ul>
        </li>
      </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<!--NavBar End -->

<!-- Header Begin -->
<div id="top_header" class="document-header">
<div>
    <img src="http://upload.wikimedia.org/wikipedia/commons/thumb/archive/2/27/20100303222348%21PHP-logo.svg/120px-PHP-logo.svg.png" alt="PHP Logo">
    <h1>Las Vegas PHP User Group - LVPHP.org</h1>
</div>
</div>
<!-- Header End -->


<!-- Begin Meetups -->
<div class="section-even">
<a name="discussions" id="discussions" href="discussions.php#discussions"></a>
<h1 class="section-header">Discussion Topics From Our Meetup Group</h1><div class="meetup-border"></div><p></p>
';
/**
 * Use Meetup.com API to retrieve discussion topics for the Las-Vegas-PHP-Users-Group Meetup.com group
 */
try {

    //@TODO move this key to a private repo
    $meetup = new Meetup(array(
        'key' => '415a4025535743759555174434b7a46'
    ));

    /**
     * Retrieve discussions from group message board.
     * The optional page parameter to limit responses.
     * Currently @ 7. May decrease for better page load speeds.
     */

    $discussions = $meetup->getDiscussions(array(
        'urlname' => 'Las-Vegas-PHP-Users-Group',
        'bid'           => '8781472', //group board ID
        'page'         => '7'
    ));

    if($discussions){

        /**
         * Loop through each of the returned discussions and list the date created and subject.
         * We will use the post api to display the content of the discussion. THis is done so we
         * can also include any replies to the original post
         */

        foreach ($discussions as $discussion){
            /**
             * Use the Meetup API to retrieve the posts for each discussion
             * passing the board id and discussion id
             */
            $posts = $meetup->getDiscussionsPosts(array(
                    'urlname' => 'Las-Vegas-PHP-Users-Group',
                    'bid'           => $discussion->board->id,
                    'did'           => $discussion->id
                ));


                    $responseContent.=
            '<ul class="media-list">
                <li class="media">
                    <h2 class="media-heading ">
                      <p>'.$discussion->subject.'
                           <a class="meetup pull-right" href="http://www.meetup.com/Las-Vegas-PHP-Users-Group/messages/boards/thread/'.$discussion->id.'" target="_blank">
                                <button class="btn btn-danger meetup" type="button">
                                    <span class="meetup-date">Click to reply/view</span>
                                </button>
                            </a>
                        </p>
                    </h2>
                </li>
            ';
            /**
             * Loop through the posts and display the user name and body of post
             */
            foreach($posts as $post){
                $responseContent .= '
                <li>
                <div class="media">
                    <a class="pull-left" href="'.$post->member->photo->thumb .'">
                      <img class="media-object" alt="'.$post->member->name .'" src=" '.$post->member->photo->thumb.' " style="width: 64px; height: 64px;">
                    </a>

                    <div class="media-body">
                        <h3 class="media-heading"> '.$post->member->name.' On '.$meetup->modifyDate($post->created).'</h3>
                        <span class="text-muted">'.$post->body.'</span>

                     </div>
                </div>

                     ';
                    }
                /*
                 * Add a twitter search link for Vegas Tech to earch post put a red border to separate discussions.
                 */
                     $responseContent .= '
                            <a href="https://twitter.com/search?q=vegastech">#VegasTech</a>
                            <div class="meetup-border"></div>
                      </li>
                </ul>
                     ';

                }

    }

    /**
     * There should always be discussions but if not display them. Probably means someth
     */

    else{
        $responseContent .= '<h4>No Discussions</h4>';
    }
}
catch (Exception $e) {
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
$responseContent .='
</div>


<!-- End Meetups-->




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
      <img src="img/logo_innovation.png" alt="InNEVation Center">
      <div class="caption">
        <h3 class="thumbnail-title">InNEVation Center</h3>
        <p>InNEVation Center provides the co-working space for our meetups.</p>
         <p><a href="http://www.innevation.com/" target="_blank" class="btn btn-primary" role="button">Learn More</a> </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="img/logo_coupla.jpg" alt="Coupla">
      <div class="caption">
        <h3 class="thumbnail-title">Coupla.co</h3>
        <p>Coupla pays for our Meetup.com expenses and has great couples events.</p>
        <p><a href="http://www.coupla.co/" target="_blank" class="btn btn-primary" role="button">Learn More</a> </p>
      </div>
    </div>
  </div>
</div>
</div>

</body>
<footer>
<!-- Jquery  -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
<!--Bootstrap JS Files-->

<script type="text/javascript" src="js/bootstrap.min.js"></script>
</footer>
</html>';

// Set and send response
$response->setContent($responseContent);
$response->send();