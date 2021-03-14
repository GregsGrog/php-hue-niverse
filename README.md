# PHP-hue-niverse
## Overview
I wrote this library to solve an issue I was having with writing a personal app, it provides a quick and easy way to interact with the philips hue api. Currently only supports local connection.
## Setup
### Using Composer
Download the library by using the composer require command:
```
composer require gregs-grog/php-hue-niverse
```
All dependencies will be installed alongside the library.

### Getting started
A simple example of setup that uses the [symfony http client](https://zetcode.com/symfony/httpclient/) as the client that is passed to the client constructor.
The class then uses this client to call the api. The class is compatible with PSR 7 clients.
``` PHP
require './vendor/autoload.php';
use Symfony\Component\HttpClient\HttpClient;


$httpClient = HttpClient::create(); //creates http client the hui api
try{
    $auth = \PHPHue\PHPHue::authorise($httpClient); //Sends an authorisation request to the API and returns the bridge IP and a username, keep this safe.
    echo $auth;

    $hue = new PHPHue\PHPHue($httpClient, "Your ID from auth function"); //pass the username collected from the above request and send it to the class contructor.

    print_r($hue->toggle_light("1")); //toggle light with ID 1
}catch(Exception $e){
echo $e;
}
```



### How to find your hue bridge IP and create a username
* [Phillips tutorial on getting an IP and creating a username](http://www.developers.meethue.com/documentation/getting-started)
* Make sure the Bridge is connected to the network and working. Check that your smartphone can connect.
* [Visit this link if an IP address is returned in the InternalAddress field then your bridge is connected correctly](https://discovery.meethue.com).


### Other examples of API calls
This library is still in progress, so some calls are missing or still being implemented.
```PHP
$hue->toggle_light($light_id);
$hue->get_light($light_id);
$hue->set_light_colour($light_id, $light_sat, $light_bri, $light_hue);
$hue->get_new_lights();
$hue->get_all_lights();
$hue->rename_light($light_id, $light_name);
$hue->delete_light($light_id);

$hue->get_all_groups();
$hue->create_group($light_id_array, $group_name, $light_type, $room_type);
$hue->get_group($group_id);
$hue->delete_group($group_id);
$hue->toggle_group($group_id);
$hue->rename_group($group_id, $group_name);


$hue->get_all_schedules();
$hue->get_schedule($schedule_id);
$hue->delete_schedule($schedule_id);
```