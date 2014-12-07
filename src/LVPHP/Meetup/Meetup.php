<?php
namespace LVPHP\Meetup;

class Meetup {
	const BASE = 'https://api.meetup.com';

	protected $_parameters = array(
		'sign' => 'true',
	);

	public function __construct(array $parameters = array()) {
		$this->_parameters = array_merge($this->_parameters, $parameters);
	}

    /**
     * Parameters available rsvp, group_id, event_id, group_urlname, venue_id, member_id, time, group_domain, page, offset, desc, only, omit
     * @param array $parameters
     * @return mixed
     */
    public function getEvents(array $parameters = array()) {
		return $this->get('/2/events', $parameters)->results;
	}
	
	public function getPhotos(array $parameters = array()) {
		return $this->get('/2/photos', $parameters)->results;
	}

    /**
     * Retrieve the boards for a given Meetup Group
     * required parameter urlname
     * optional page, offset, desc, only, omit,
     * @param array $parameters
     * @return mixed
     */
    public function getDiscussionBoards(array $parameters = array()) {
		return $this->get('/:urlname/boards', $parameters);
	}

    /**
     * Retrieve the discussions of a group board.
     * @param array $parameters
     * @return mixed
     */
    public function getDiscussions(array $parameters = array()) {
		return $this->get('/:urlname/boards/:bid/discussions/', $parameters);
	}

    /**
     * Shad Mickelberry: Added this function to retrieve replies to post discussions.
     * required parameters bid, did.
     * optional prameters page, offset, desc, only, omit
     * @param array $parameters
     * @return mixed
     */
    public function getDiscussionsPosts(array $parameters = array()) {
        return $this->get('/:urlname/boards/:bid/discussions/:did', $parameters);
    }

	public function getMembers(array $parameters = array()) {
		return $this->get('/2/members', $parameters);
	}

	public function getNext($response) {
		if (!isset($response) || !isset($response->meta->next))
		{
			throw new Exception("Invalid response object.");
		}
		return $this->get_url($response->meta->next);
	}
	
	public function get($path, array $parameters = array()) {
		$parameters = array_merge($this->_parameters, $parameters);
	
		if (preg_match_all('/:([a-z]+)/', $path, $matches)) {
			
			foreach ($matches[0] as $i => $match) {
			
				if (isset($parameters[$matches[1][$i]])) {
					$path = str_replace($match, $parameters[$matches[1][$i]], $path);
					unset($parameters[$matches[1][$i]]);
				} else {
					throw new Exception("Missing parameter '" . $matches[1][$i] . "' for path '" . $path . "'.");
				}
			}
		}

		$url = self::BASE . $path . '?' . http_build_query($parameters);

		return $this->get_url($url);
	}

	protected function get_url($url) {
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Charset: utf-8"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$content = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$error = curl_error($ch);
			curl_close($ch);
			
			throw new Exception("Failed retrieving  '" . $url . "' because of ' " . $error . "'.");
		}
		
		$response = json_decode($content);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		if ($status != 200) {
						
			if (isset($response->errors[0]->message)) {
				$error = $response->errors[0]->message;
			} else {
				$error = 'Status ' . $status;
			}
			
			throw new Exception("Failed retrieving  '" . $url . "' because of ' " . $error . "'.");
		}

		if (isset($response) == false) {
		
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					$error = 'No errors';
				break;
				case JSON_ERROR_DEPTH:
					$error = 'Maximum stack depth exceeded';
				break;
				case JSON_ERROR_STATE_MISMATCH:
					$error = ' Underflow or the modes mismatch';
				break;
				case JSON_ERROR_CTRL_CHAR:
					$error = 'Unexpected control character found';
				break;
				case JSON_ERROR_SYNTAX:
					$error = 'Syntax error, malformed JSON';
				break;
				case JSON_ERROR_UTF8:
					$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
				default:
					$error = 'Unknown error';
				break;
			}
    
			throw new Exception("Cannot read response by  '" . $url . "' because of: '" . $error . "'.");
		}
		
		return $response;
	}

    /**
     * Shad Mickelberry: This function modifies the date returned from the Meetup.com api response into a readable form.
     * @param $date
     * @return bool|string
     */
    public function modifyDate($date){
        $modified_date = date("l M jS Y",($date)/1000);
        return $modified_date;
    }

    /**
     * Shad Mickelberry: This function parses the Meetup.com event_url variable to return the event id for
     * use in the button API.
     * Example: $event_url = 'http://www.meetup.com/Las-Vegas-PHP-Users-Group/events/207918112/';
     * Output = '207918112';
     * @param $event_url
     * @return mixed
     */
    public function getEventIdFromURL($event_url) {
        $url = substr($event_url, 0, -1);
        $url_components = explode('/', $url);

        $event_id = end($url_components);

        return $event_id;

    }
}

