<?php
// @todo Introduce routing to avoid having a php file for each separate page.

require_once '../bootstrap.php';

// Init our request/response objects
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Michelf\Markdown;

$request = Request::createFromGlobals();

$response = new Response(
    'Content',
    Response::HTTP_OK,
    array( 'content-type' => 'text/html' )
);



// include header
include '../resources/view/header.php';

// Get the list of meetings which are markdown files in the meetings directory
$meetings = array_diff(scandir('../resources/meetings'), array('.', '..'));

// Sort newest to oldest. Files should be named in format yyyymmdd.md as in 20150402.md for the april 2nd 2015 meetup
rsort($meetings);

/**
 * If the link is to a specific meeting that will be in the url as a $_GET['date'] variable
 *
 */
    if (empty($_GET['date'])) {

        // If the $_GET['date'] variable is not in the URL then we want to display a list of events

        $display_contents = '<h1>Las Vegas PHP User Group Meetings</h1>';

        /**
         * Loop through each meeting and display a link to that file/page
         */
        foreach ($meetings as $meeting) {

                $file = explode('.', $meeting);
                $date =  $file[0];
                $display_contents .= '<a href="/meetings.php?date=' . $date . '"><button class="btn btn-danger meetup">LVPHP Meeting: ' . date('m/d/y', strtotime($date)) . '</button></a>';
                $display_contents .= '<hr/>';

            }

        echo $display_contents;

    } else {

        // Get the contents of the file related to meeting date
        $text = file_get_contents('../resources/meetings/' . $_GET['date'] . '.md');

        /**
         * If we didn't find the file or the file is empty display meeting list again
         */

        if (empty($text)) {

            $display_contents = '<h1>Las Vegas PHP User Group Meetings</h1>';
            $display_contents .= '<p class="alert alert-danger">Invalid meeting date</p>';

            /**
             * Loop through each meeting and display a link to that file/page
             */
            foreach ($meetings as $meeting) {

                $file = explode('.', $meeting);
                $date =  $file[0];
                $display_contents .= '<a href="' . WEB_ROOT . '/meetings.php?date=' . $date . '"><button class="btn btn-danger meetup">LVPHP Meeting: ' . date('m/d/y', strtotime($date)) . '</button></a>';
                $display_contents .= '<hr/>';

            }

            echo $display_contents;

        } else {

            $html = Markdown::defaultTransform($text);

            echo $html;

        }

    }

//include footer
include '../resources/view/footer.php';



