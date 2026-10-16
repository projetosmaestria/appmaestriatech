<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Googlecalendar extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
        $this->load->library('session');
        $this->load->library('googleplus');
        $this->calendar = new Google_Service_Calendar($this->googleplus->client());

    }


    public function isLogin($client_id, $client_secret, $token)
    {
        //$token = $this->session->userdata('google_calendar_access_token');

        $json = json_decode($token, true);

        if ($json["access_token"]) {
            $this->googleplus
                ->client
                ->setAccessToken($token);
        }

        if ($this->googleplus->isAccessTokenExpired()) {

            $this->googleplus->client->setClientId($client_id);
            $this->googleplus->client->setClientSecret($client_secret);
            $this->googleplus->client->refreshToken($json["refresh_token"]);

            $token = $this->googleplus->client->getAccessToken();

            $this->session
                ->set_userdata('google_calendar_access_token', $token);

            return false;
            
        }

        return true;
    }


    public function loginUrl($client_id = null, $client_secret = null)
    {
        if($client_id == null || $client_secret == null)
             return false;

         $this->googleplus->client->setClientId($client_id);
         $this->googleplus->client->setClientSecret($client_secret);

        return $this->googleplus
            ->loginUrl();
    }


    public function login($client_id = null, $client_secret = null, $code)
    {
        if($client_id == null || $client_secret == null)
             return false;

        $this->googleplus->client->setClientId($client_id);
        $this->googleplus->client->setClientSecret($client_secret);

        $login = $this->googleplus
            ->client
            ->authenticate($code);

        if ($login) {

            $token = $this->googleplus
                ->client
                ->getAccessToken();

            $this->session
                ->set_userdata('google_calendar_access_token', $token);

            return true;

        }

    }

    public function revoke($client_id = null, $client_secret = null)
    {
        if($client_id == null || $client_secret == null)
             return false;

        $this->googleplus->client->setClientId($client_id);
        $this->googleplus->client->setClientSecret($client_secret);

        return $this->googleplus->client->revokeToken();
    }

    public function getUserInfo()
    {

        return $this->googleplus->getUser();

    }


    public function getEvents($calendarId = 'primary', $timeMin = false, $timeMax = false, $maxResults = 10)
    {


        if ( ! $timeMin) {

            $timeMin = date("c", strtotime(date('Y-m-d ').' 00:00:00'));

        } else {

            $timeMin = date("c", strtotime($timeMin));

        }


        if ( ! $timeMax) {

            $timeMax = date("c", strtotime(date('Y-m-d ').' 23:59:59'));

        } else {

            $timeMax = date("c", strtotime($timeMax));

        }


        $optParams = array(
            'maxResults'   => $maxResults,
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin,
            'timeMax'      => $timeMax,
            'timeZone'     => 'Europe/Istanbul',

        );

        $results = $this->googlecalendar->calendar->events->listEvents($calendarId, $optParams);


        $data = array();

        foreach ($results->getItems() as $item) {

            $start = date('d-m-Y H:i', strtotime($item->getStart()->dateTime));

            array_push(

                $data,
                array(

                    'id'          => $item->getId(),
                    'summary'     => $item->getSummary(),
                    'description' => $item->getDescription(),
                    'creator'     => $item->getCreator(),
                    'start'       => $item->getStart()->dateTime,
                    'end'         => $item->getEnd()->dateTime,


                )

            );

        }

        return $data;

    }


    public function addEvent($calendarId = 'primary', $data)
    {
        //date format is => 2016-06-18T17:00:00+03:00

        $event = new Google_Service_Calendar_Event(
            array(
                'summary'     => $data['summary'],
                'description' => $data['description'],
                'start'       => array(
                    'dateTime' => str_replace(" ", "T", $data['start']),
                    'timeZone' => 'America/Sao_Paulo',
                ),
                'end'         => array(
                    'dateTime' => str_replace(" ", "T", $data['end']),
                    'timeZone' => 'America/Sao_Paulo',
                ),
            )
        );


        return $this->calendar->events->insert($calendarId, $event);
    }

    public function updateEvent($calendarId = 'primary', $eventId, $data)
    {
        $event = new Google_Service_Calendar_Event(
            array(
                'summary'     => $data['summary'],
                'description' => $data['description'],
                'start'       => array(
                    'dateTime' => str_replace(" ", "T", $data['start']),
                    'timeZone' => 'America/Sao_Paulo',
                ),
                'end'         => array(
                    'dateTime' => str_replace(" ", "T", $data['end']),
                    'timeZone' => 'America/Sao_Paulo',
                ),
            )
        );

        return $this->calendar->events->update($calendarId, $eventId, $event);
    }

    public function delEvent($calendarId = 'primary', $eventId)
    {
        return $this->calendar->events->delete($calendarId, $eventId);
    }

    public function addCalendar($description)
    {
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($description);
        $calendar->setTimeZone('America/Sao_Paulo');

        return $this->calendar->calendars->insert($calendar);
    }

    public function delCalendar($id)
    {
        return $this->calendar->calendars->delete($id);
    }

    public function shareCalendar($calendarId, $userEmail, $role)
    {
        $rule = new Google_Service_Calendar_AclRule();
        $scope = new Google_Service_Calendar_AclRuleScope();
        /*
        The type of the scope. Possible values are:

            "default" - The public scope. This is the default value.
            "user" - Limits the scope to a single user.
            "group" - Limits the scope to a group.
            "domain" - Limits the scope to a domain.
        */
        
        $scope->setType("user");
        $scope->setValue($userEmail);
        $rule->setScope($scope);
        /*
        The role assigned to the scope. Possible values are:
            "none" - Provides no access.
            "freeBusyReader" - Provides read access to free/busy information.
            "reader" - Provides read access to the calendar. Private events will appear to users with reader access, but event details will be hidden.
            "writer" - Provides read and write access to the calendar. Private events will appear to users with writer access, and event details will be visible.
            "owner" - Provides ownership of the calendar. This role has all of the permissions of the writer role with the additional ability to see and manipulate ACLs.
        */
        $rule->setRole($role);
        
        return $this->calendar->acl->insert($calendarId, $rule);
    }

    public function unshareCalendar($calendarId, $ruleId)
    {        
        return $this->calendar->acl->delete($calendarId, $ruleId);
    }

}