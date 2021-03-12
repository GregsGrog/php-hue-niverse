# PHPHue
## Overview
I wrote this library to solve an issue I was having with writing a personal app, it provides a quick and easy way to interact with the philips hue api. Currently only supports local connection.
## Setup
Download the api.php file and include it in your application
```php
<?php
require "api.php"; //Path to api.php file

//first we must authorise the application on the bridge this only needs to be done once and then the username can be stored.
print_r(PHPHue::authorise()); //This outputs an array that contains an array of the username and an ip address if auto discovery is selected(See more options below).

//Once we have a username we can then use our class.
$hueApi = new PHPHue("<your-user>");
$hueApi->get_lights(); //this will get all lights and all fields.
$hueApi->toggle_light("1"); //we can then toggle a light via its ID.

//This is the simplest setup of this library more details can be found below.



```

### How to find your hue bridge IP and create a username
[Phillips tutorial on getting an IP and creating a username](http://www.developers.meethue.com/documentation/getting-started)
* Make sure the Bridge is connected to the network and working. Check that your smartphone can connect.
* [Visit this link if an IP address is returned in the InternalAdress field then your bridge is connected correctly](https://discovery.meethue.com).


### Lights API functions

```php
<?php
$hueApi->toggle_light("1"); //Toggle light via ID
$hueApi->get_lights(); //Gets all Lights
$hueApi->get_new_lights(); //Gets any New lights since last check of get_lights
$hueApi->rename_light("1", "Name"); //Renames light using ID and Name
$hueApi->set_light_colour("1", "255", "255", "32000"); //set colour of light using ID, saturation, brightness and hue [Find more info here](https://developers.meethue.com/develop/hue-api/lights-api/)

```

### Groups API Functions
```php
<?php
$hueApi->get_group("1"); //Gets group Via ID
$hueApi->create_group(array("1", "2", "3"), "TestGroup3", "Room", "Hallway"); //Creates group with an array of light ID's, a name for the group a group type and a location if group type is set to room.

```
### This documentation is still being worked on and is due to change
