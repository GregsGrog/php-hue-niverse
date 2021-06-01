<?php /** @noinspection PhpUndefinedVariableInspection */

declare(strict_types=1);



namespace PHPHue;


class PHPHue
{
    use ColorConversion;

    private $hue_ip;
    private string $hue_user;
    private object $httpclient;
    private array $available_room_types = array(
        "Living room",
        "Kitchen",
        "Dining",
        "Bedroom",
        "Kids bedroom",
        "Bathroom",
        "Nursery",
        "Recreation",
        "Office",
        "Gym",
        "Hallway",
        "Toilet",
        "Front door",
        "Garage",
        "Terrace",
        "Garden",
        "Driveway",
        "Carport",
        "Other",
        "Home",
        "Downstairs",
        "Upstairs",
        "Top floor",
        "Attic",
        "Guest room",
        "Staircase",
        "Lounge",
        "Man cave",
        "Computer",
        "Studio",
        "Music",
        "TV",
        "Reading",
        "Closet",
        "Storage",
        "Laundry room",
        "Balcony",
        "Porch",
        "Barbecue",
        "Pool"
    );
    private array $available_light_types = array("Room", "LightGroup");


    /**
     * Hue constructor.
     * @param $httpclient
     * @param $hue_user
     * @param int $auto_discovery
     * @param null $hue_ip
     * @throws \Exception
     */
    function __construct($httpclient, $hue_user, $auto_discovery = 1, $hue_ip = null)
    {

        $this->httpclient = $httpclient;

        if ($auto_discovery === 1 && $hue_ip === null) {
            $this->hue_ip = $this->autoIPDiscovery();

        } elseif ($auto_discovery === 0 && !$hue_ip === null) {
            $this->hue_ip = $hue_ip;
        } else {
            throw new \Exception('Please use auto discovery or supply your own IP!');
        }


        if (!preg_match('/^[A-Za-z0-9_-]*$/', $hue_user)) {
            throw new \Exception('Bridge username is invalid!');
        } else {
            $this->hue_user = $hue_user;
        }


    }

    /**
     * @param $method
     * @param $url
     * @param null $data
     * @return mixed
     * @throws \Exception
     */
    function callAPI($method, $url, $data = NULL)
    {

        switch ($method) {
            case "PUT":
                $response = $this->httpclient->request('PUT', $url, [
                    'body' => $data
                ]);
                break;
            case "GET":
                $response = $this->httpclient->request('GET', $url);
                break;
            case "POST":
                $response = $this->httpclient->request('POST', $url, [
                    'body' => $data
                ]);
                break;
            case "DELETE":
                $response = $this->httpclient->request('DELETE', $url);
                break;
        }

        $response = json_decode($response->getContent());


        if(is_array($response)){
            if (isset($response[0]->error)) {
                throw new \Exception($response[0]->error->description);
            } else {
                return $response;
            }
        }

        return $response;

    }

    /**
     * @return mixed
     */
    private function autoIPDiscovery()
    {
        $response = $this->httpclient->request('GET', 'https://discovery.meethue.com');
        return $response->toArray()[0]['internalipaddress'];
    }

    /**
     * @param $httpclient
     * @param string $app_name
     * @param string $device_name
     * @param int $auto_discover_hue_IP
     * @param null $hue_ip
     * @return array
     * @throws \Exception
     */
    public static function authorise($httpclient, $app_name = 'PHPHue', $device_name = 'myServer', $auto_discover_hue_IP = 1, $hue_ip = null): array
    {

        $auth_output_array = array();

        if ($auto_discover_hue_IP === 1 && $hue_ip === null) {
            $response = $httpclient->request('GET', 'https://discovery.meethue.com');
            $auth_output_array['Bridge_Ip'] = $response->toArray()[0]['internalipaddress'];
            $hue_ip = $response->toArray()[0]['internalipaddress'];
        } elseif ($auto_discover_hue_IP === 0 && !$hue_ip === null) {
            if (!filter_var($hue_ip, FILTER_VALIDATE_IP)) {
                throw new \Exception('IP address is invalid!');
            }
        } else {
            throw new \Exception('Please use auto discovery or supply your own IP!');
        }
        $data_array = array(
            "devicetype" => $app_name . "#" . $device_name
        );


        $response = $httpclient->request('POST', 'https://' . $hue_ip . '/api', [
            'body' => json_encode($data_array)
        ]);

        $response = json_decode($response->getContent());

        if (isset($response[0]->error)) {
            throw new \Exception($response[0]->error->description);
        }
        $auth_output_array['Username'] = $response;
        return $auth_output_array;


    }

    /**
     * @param $light_id
     * @return mixed
     * @throws \Exception
     */
    function toggle_light($light_id)
    {
        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is not a number!');
        }

        $light = $this->get_light($light_id);

        if ($light->state->on === true) {
            $light_state = false;
        } elseif ($light->state->on === false) {
            $light_state = true;
        } else {
            throw new \Exception('Could Not get status of light!');
        }

        $data_array = array(
            "on" => $light_state
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . "/" . $light_id . "/state", json_encode($data_array));

    }

    /**
     * @param $light_id
     * @return mixed
     * @throws \Exception
     */
    function get_light($light_id)
    {

        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is Invalid.');
        }

        return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . '/' . $light_id);
    }

    /**
     * @param $light_id
     * @param string $light_sat
     * @param string $light_bri
     * @param string $light_hue
     * @return mixed
     * @throws \Exception
     */
    function set_light_colour($light_id, $light_sat = "254", $light_bri = "254", $light_hue = "1000")
    {


        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is Invalid.');
        }
        if (!is_numeric($light_sat) && $light_sat > 0 && $light_sat < 255) {
            throw new \Exception('Light Saturation is Invalid.');
        }
        if (!is_numeric($light_bri) && $light_bri > 0 && $light_bri < 255) {
            throw new \Exception('Light Brightness is invalid.');
        }
        if (!is_numeric($light_hue) && $light_hue > 0 && $light_hue < 65535) {
            throw new \Exception('Light Hue is invalid.');
        }

        $data_array = array(
            "on" => true,
            "sat" => intval($light_sat),
            "bri" => intval($light_bri),
            "hue" => intval($light_hue),
            "transitiontime" => 2
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . "/" . $light_id . "/state", json_encode($data_array));


    }

    /**
     * @param $light_id
     * @param string $light_sat
     * @param string $light_bri
     * @param string $light_hue
     * @return mixed
     * @throws \Exception
     */
    function set_light_colour_hex($light_id, $hex_colour)
    {


        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is Invalid.');
        }
        if (!isset($hex_colour)) {
            throw new \Exception('No Hex color supplied.');
        }

        $xy_cords = $this->xy_from_hex($hex_colour);

        $data_array = array(
            "on" => true,
            "xy" => array($xy_cords[0], $xy_cords[1]),
            "transitiontime" => 2
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . "/" . $light_id . "/state", json_encode($data_array));


    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function get_new_lights()
    {
        if ($this->get_all_lights()) {

            return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights/new');

        } else {
            throw new \Exception('Failed To get Lights!');
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function get_all_lights()
    {
        return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights');
    }

    /**
     * @param $light_id
     * @param $light_name
     * @return mixed
     * @throws \Exception
     */
    function rename_light($light_id, $light_name)
    {
        if (!strlen($light_name) > 0 && strlen($light_name) < 32) {
            throw new \Exception('Light Name is too Long!');
        }

        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is not numeric!');
        }

        $data_array = array(
            "name" => $light_name,
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . "/" . $light_id, json_encode($data_array));

    }

    /**
     * @param $light_id
     * @return array|mixed
     * @throws \Exception
     */
    function light_alert($light_id){
        //TODO toggle function to remember previous state and revert once alert is complete
        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is Invalid.');
        }

        $data_array = array(
            "on" => true,
            "alert" => "select",
        );


        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . "/" . $light_id . "/state", json_encode($data_array));

    }

    /**
     * @param $light_id
     * @return array|mixed
     * @throws \Exception
     */
    function delete_light($light_id)
    {
        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is not numeric!');
        }

        return $this->callAPI('DELETE', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights/' . $light_id);
    }

    /**
     * @return mixed
     * @throws
     */
    function get_all_groups()
    {
        return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/groups');
    }

    /**
     * @param $light_array
     * @param $group_name
     * @param string $light_type
     * @param string $room_type
     * @return mixed
     * @throws \Exception
     */
    function create_group($light_array, $group_name, $light_type = "LightGroup", $room_type = "Other")
    {

        if (!in_array($room_type, $this->available_room_types)) {
            throw new \Exception('Room Type is not available!');
        }

        if (!in_array($light_type, $this->available_light_types)) {
            throw new \Exception('Type must be "LightGroup or Room"');
        }


        if ($light_type === "LightGroup") {
            $data_array = array(
                "lights" => $light_array,
                "name" => $group_name,
            );
        } elseif ($light_type === "Room") {
            $data_array = array(
                "lights" => $light_array,
                "name" => $group_name,
                "type" => $light_type,
                "class" => $room_type,
            );
        } else {
            throw new \Exception('Type must be "LightGroup or Room"');
        }
        return $this->callAPI('POST', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/groups', json_encode($data_array));
    }

    /**
     * @param $group_id
     * @return mixed
     * @throws \Exception
     */
    function get_group($group_id)
    {
        if (!is_numeric($group_id)) {
            throw new \Exception('Group ID is not numeric!');
        }

        return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/groups' . "/" . $group_id);
    }

    /**
     * @param $group_id
     * @return mixed
     */
    function delete_group($group_id){
        if (!is_numeric($group_id)) {
            throw new \Exception('Group ID is not numeric!');
        }

        return $this->callAPI('DELETE', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/groups' . "/" . $group_id);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function get_all_schedules()
    {
        return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/schedules');
    }

    /**
     * @param $schedule_id
     * @return mixed
     * @throws \Exception
     */
    function get_schedule($schedule_id)
    {
        if (!is_numeric($schedule_id)) {
            throw new \Exception('Schedule ID is not numeric!');
        }

        return $this->callAPI('GET', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/schedules/' . $schedule_id);
    }

    /**
     * @param $schedule_id
     * @return array|mixed
     * @throws \Exception
     */
    function delete_schedule($schedule_id)
    {
        if (!is_numeric($schedule_id)) {
            throw new \Exception('Schedule ID is not numeric!');
        }

        return $this->callAPI('DELETE', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/schedules/' . $schedule_id);
    }

    /**
     * @param $group_id
     * @return array|mixed
     * @throws \Exception
     */
    function toggle_group($group_id){

        if (!is_numeric($group_id)) {
            throw new \Exception('Group ID is not a number!');
        }

        $group = $this->get_group($group_id);



        if ($group->action->on === true) {
            $group_state = false;
        } elseif ($group->action->on === false) {
            $group_state = true;
        } else {
            throw new \Exception('Could Not get status of Group!');
        }

        $data_array = array(
            "on" => $group_state
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/groups' . "/" . $group_id . "/action", json_encode($data_array));
    }

    /**
     * @param $group_id
     * @param $group_name
     * @return array|mixed
     * @throws \Exception
     */
    function rename_group($group_id, $group_name){

        if (!strlen($group_name) > 0 && strlen($group_name) < 32) {
            throw new \Exception('Light Name is too Long!');
        }

        if (!is_numeric($group_id)) {
            throw new \Exception('Light ID is not numeric!');
        }

        $data_array = array(
            "name" => $group_name,
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/groups' . "/" . $group_id, json_encode($data_array));
    }


    function set_light_brightness($light_id, $light_bri){

        if (!is_numeric($light_id)) {
            throw new \Exception('Light ID is Invalid.');
        }

        if (!is_numeric($light_bri) && $light_bri > 0 && $light_bri < 255) {
            throw new \Exception('Light Brightness is invalid.');
        }

        $data_array = array(
            "bri" => intval($light_bri),
        );
        return $this->callAPI('PUT', 'https://' . $this->hue_ip . '/api/' . $this->hue_user . '/lights' . "/" . $light_id . "/state", json_encode($data_array));
    }


}


