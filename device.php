<?php
#https://demo.shome52.ru/provider/v1.0/user/devices
# tcpdump -n -s 0 -A -i enp0s3 port 8088 > update_list.log
# https://github.com/dmitry-k/yandex_smart_home/blob/master/capability.py


class Capabilities{
	public $type;
	public $retrievable;
	public $parameters;
	public $state;
	
	public function __construct(){
		$this->type="devices.capabilities.on_off";
		$this->retrievable=true;		
		$this->state['instance']='off';
		$this->state['value']=false;
	}
	
	public function set_type($type,$name="temperature",$min=0,$max=100){
		debug ($type,1,"type:");
		switch ($type) {
			case "on_off":
				$this->type="devices.capabilities.".$type;
				break;
			case "range":
#				$this->state['instance']='temperature';
#				$this->state['value']='20';
#				$this->parameters["range"]="hsv";
				$this->type="devices.capabilities.".$type;
				unset($this->state);
				$this->parameters["instance"]=$name;
				if ($name=="channel")
					break;

				if ($name=="temperature")
					$this->parameters["unit"]="unit.temperature.celsius";
#				else
#					$this->parameters["unit"]="unit.percent";

				$this->parameters["range"]["min"]=$min;
				$this->parameters["range"]["max"]=$max;
				$this->parameters["range"]["precision"]=1;
				break;
			case "":
				break;

			case "mode":
				unset($this->state);
				$this->type="devices.capabilities.".$type;
				$this->parameters["instance"]=$name;
				$this->parameters["modes"]=array("heat","cool");
				$this->parameters["ordered"]=false;

				break;
			case "toggle":
				$this->type="devices.capabilities.".$type;
				$this->parameters["instance"]=$name;
				break;
			case "color_setting":
				$this->type="devices.capabilities.".$type;
				unset($this->state);
#					$this->state['instance']="hsv";
#						$this->state['value']=""."5000";
				unset($this->retrievable);
				$this->parameters["color_model"]="hsv";
#				$this->parameters["temperature_k"]["min"]=2700;
#				$this->parameters["temperature_k"]["max"]=9000;
#				$this->parameters["temperature_k"]["precision"]=1;
				break;
			default:
		}
	}
}

class Device{
	public $id;
	public $name;
	public $description;
	public $room;
	public $type;
	public $state=array();
	public $capabilities=array();
	public $device_info=array();
	public $action=array();		//Массив для определения действий.
	/*
	 $this->action['on'][type]='http';
	 $this->action['on'][action]='http://example.com/device/on';
	 $this->action['off'][type]='http';
	 $this->action['off'][action]='http://example.com/device/off';
	 $this->action['togle'][type]='http';
	 $this->action['togle'][action]='http://example.com/device/togle';
	 
	 
	*/
		public function __construct($name="",$room="",$type="devices.types.socket",$description="") {
		$this->name=$name;
		$this->room=$room;
		$this->type=$type;
		$this->description=$description;
		$this->state["instance"]="";
		$this->state["value"]="";
		
	}
	
	
	function print_json(){
		$result='new device({
		"id": "'.$this->id.'",
		"name": "'.$this->name.'",
		"room": "'.$this->room.'",
		"type": "'.$this->type.'",
		"capabilities": "'.$this->capabilities.'",
		})';
		
		return $result;
	}
	function get_array(){
		$device["id"]="".$this->id;
		$device["name"]=$this->name;
		$device["description"]=$this->description;
		$device["room"]=$this->room;

		$device["type"]=$this->type;
		$device["capabilities"]=$this->capabilities;
		return $device;
	}
	function get_status(){
#		$device=new Device();
debug("NEW_DEVICE");
		$device->id="".$this->id;
		$device->capabilities=$this->capabilities;
		for ($i=0;$i<count($this->capabilities);$i++){
			switch ($this->capabilities[$i]->type)
			{
				case "on_off":
#                                	$device["capabilities"][$i]->type="devices.capabilities.".$type;
					$device["capabilities"][$i]->state['instance']["instance"]="on";
					$device["capabilities"][$i]->state['instance']["value"]=true;
					
                               		break;
				default:
					unset($device["capabilities"][$i]);
			}
		}
		return $device;
	}
}

class DeviceList{
	public $list;
	
	public function add($name,$room="",$type="devices.types.socket",$description=""){
		$this->list[]=new Device($name,$room,$type,$description);
		$this->list[count($devices->list)-1]->capabilities[]=new Capabilities;

	}
	public function getList(){
		$result='{"request_id":"1","payload":{"user_id":"1","devices":';
#		$list=array();
		for($i=0;$i<count($this->list); $i++) {
#			$this->list[$i]->id=$i;
			$list[]=$this->list[$i]->get_array();
		}
		$result.=json_encode($list);

		$result.='}}';
		
		return ($result);
	}

	public function getQuery($reqID){
		$result='{"request_id":"'.$reqID.'","payload":{"devices": ';
		for($i=0;$i<count($this->list); $i++) {
			$this->list[$i]->id=0;
			$list[]=$this->list[$i]->get_status();
		}
		$result.=json_encode($list);
		$result.='}}';
		return ($result);
	}
	
	public function initIdAll(){
		for ($i=0;$i<count($this->list);$i++)
			$this->list[$i]->id=$i;
	}
	
	public function load(){
		#$devices= new DeviceList;

		$this->list[]=new Device("Светильник","Виртуальная","devices.types.light");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[1]->set_type("color_setting");
		debug($this->list[count($this->list)-1],1,"Светильник: ");

		$this->list[]=new Device("Розетка","Виртуальная","devices.types.socket");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		
		
		$this->list[]=new Device("Переключатель","Виртуальная","devices.types.switch");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		
		$this->list[]=new Device("Водонагреватель","Виртуальная","devices.types.thermostat");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
#		$this->list[count($this->list)-1]->capabilities[1]->set_type("range","temperature");
		$this->list[count($this->list)-1]->capabilities[1]->set_type("range");
		
		$this->list[]=new Device("Кондиционер","Виртуальная","devices.types.thermostat.ac");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[1]->set_type("range");

#		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
#		$this->list[count($this->list)-1]->capabilities[2]->set_type("mode","thermostat");
		

		
		$this->list[]=new Device("Медиаприставка","Виртуальная","devices.types.media_device");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[1]->set_type("toggle","mute");

#		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
#		$this->list[count($this->list)-1]->capabilities[2]->set_type("toggle","1");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[2]->set_type("range","volume",1,100);
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[3]->set_type("range","channel",0,30);
		


		$this->list[]=new Device("Телевизор","Виртуальная","devices.types.media_device.tv");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[1]->set_type("toggle","mute");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[2]->set_type("range","channel",0,30);

		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[3]->set_type("range","volume",1,100);

		
		$this->list[]=new Device("Кофемашина","Виртуальная","devices.types.cooking");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		
		$this->list[]=new Device("Чайник","Виртуальная","devices.types.cooking.kettle");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		$this->list[count($this->list)-1]->capabilities[1]->set_type("range");
		
		$this->list[]=new Device("Игрушка","Виртуальная","devices.types.other");
		$this->list[count($this->list)-1]->capabilities[]=new Capabilities;
		
		$this->initIdAll();
		#return $devices;
	}
}

$devices=new DeviceList();
$devices->load();


#if (isset($_SERVER['REQUEST_URI']))
#	$orig=$_SERVER['REQUEST_URI'];

switch ($orig){
	#'/v1.0/user/devices/action'
	#'/v1.0/user/devices/query'
#	case (strpos($orig,'/v1.0/user/devices') ? true : false): 
#		break;
	case '':
		$message="";
		break;
	case (strpos($orig,'/v1.0/user/devices/action') ? true : false): 

		$cur_dev=$data->payload->devices[0];
		$dev_id=$cur_dev->id;
		$dev_cap=$cur_dev->capabilities;
		$dev_action=$dev_cap[0];
		$message='{"request_id":"1","payload":{"devices":[{"id":"'.$dev_id.'","capabilities":[{"state":{"instance":"'.$dev_action->state->instance.'","action_result":{"status":"DONE"}}}]}]}}';
		
		break;
	case (strpos($orig,'/v1.0/user/devices/query') ? true : false): 
	

		$dataRow = $_SERVER['HTTP_X_REQUEST_ID'];
		debug($dataRow,1,"query_dataRow: ");
		//$data = json_decode($dataRow);


		$dev_id=$data->devices[0]->id[0];
/*
#		debug($data,1,"dev_id_data: ");
#		debug($dev_id,1,"dev_id_qq: ");

		$cur_dev= new DeviceList();
		$message='{"request_id": "' . $_SERVER['HTTP_X_REQUEST_ID'].'","payload": {        "devices":"';
		debug($message,0,"message_query1:");

		$cur_dev->list[]= new Device();
		debug($message,0,"message_query3:");
		
		$cur_dev->list[count($cur_dev->list)-1]=$device[$dev_id]->get_status();
		debug($message,0,"message_query2:");
		#$devices->list[$dev_id];
		$message.=json_decode($cur_dev);

		$message.='}}';
		debug($cur_dev,1,"cur_dev:");
*/
#		$message=$cur_dev->getQuery($_SERVER['HTTP_X_REQUEST_ID']);
#		debug($data,1,"request_id");
#	$dev_id=4;

		$message='{"request_id": "' . $_SERVER['HTTP_X_REQUEST_ID'].'","payload": {        "devices": [{            "id": "'.$dev_id.'",
            "capabilities": [';
function capabilities_color_setting(){
	return ('{
                "type": "devices.capabilities.color_setting",
                "state": {
                    "instance": "hsv",
                    "value": {
                        "h": 255,
                        "s": 50,
                        "v": 100
                    }
                }
            }');
}

function capabilities_on_off(){
	return ('{
                "type": "devices.capabilities.on_off",
                "state": {
                    "instance": "on",
                    "value": true
                }
            }');
}

function capabilities_toggle(){
        return ('{"type": "devices.capabilities.toggle",
    "state": {
        "instance": "mute",
        "value": true
}}');
}

function capabilities_range($range_name="temperature",$value=20){
	if ($range_name=="channel")
		return ('{"type": "devices.capabilities.range",
    "state": {
        "instance": "'.$range_name.'"
}}');


        return ('{"type": "devices.capabilities.range",
    "state": {
        "instance": "'.$range_name.'",
        "value": '.$value.'
}
        }');
}


	for ($i=0;$i<=count($devices->list[$dev_id]->capabilities[$i]);$i++)
	{
#	$message.=$devices->list[$dev_id]->capabilities[$i]->parameters["instance"];	
#		$message.=','.$i.',.'.$devices->list[$dev_id]->capabilities[$i]->type;
		if ($i>0) $message.=','; 
		switch ($devices->list[$dev_id]->capabilities[$i]->type){
			case "devices.capabilities.on_off":
				$message.=capabilities_on_off();
			break;
			case "devices.capabilities.color_setting":
				$message.=capabilities_color_setting();
			break;
			case "devices.capabilities.toggle":
				$message.=capabilities_toggle();
				break;
			case "devices.capabilities.range":
                                $message.=capabilities_range($devices->list[$dev_id]->capabilities[$i]->parameters["instance"]);
                                break;
#			default:
#				$message.=$devices->list[$dev_id]->capabilities[$i]->type;
		
		}
	}
		$type_dev=$devices->list[$dev_id]->type;
#	if ($type_dev=="devices.types.media_device.tv")
#		$message.=capabilities_range("channel",1);
/*
'{
                "type": "devices.capabilities.color_setting",
                "state": {
                    "instance": "hsv",
                    "value": {
                        "h": 255,
                        "s": 50,
                        "v": 100
                    }
                }
            },{
                "type": "devices.capabilities.on_off",
                "state": {
                    "instance": "on",
                    "value": true
                }
            },{
"type": "devices.capabilities.toggle",
    "state": {
        "instance": "mute",
        "value": true
    },{
"type": "devices.capabilities.range",
    "state": {
        "instance": "temperature",
        "value": 10
    }
}
]
*/
$message.='        
            ]}]
    }
}';

#		$message="qweqweqwe";
		debug($message,0,"message_query:");
		break;
	case (strpos($orig,'/v1.0/user/unlink') ? true : false): 
		$message="Unlink.";
		break;
	default:
		$message=$devices->getList();
}
#$message='{"request_id":"1","payload":{"user_id":"1","devices":[
#{"id":"0","name":"все","description":"","type":"devices.types.socket","room":"Детская",
#"capabilities":[{"type":"devices.capabilities.on_off","retrievable":true,"state":{"instance":"off","value":false}}]
#}
#]}}';

header('Content-Type: application/json; charset=utf-8');
header("Content-Length: ".strlen($message));
echo $message;
exit;

/*


22:25:25.158901 IP 10.0.2.15.1503 > 10.0.2.2.53752: Flags [.], ack 337, win 30016, length 0
E..(N.@.@..-
...
.......r.!....RP.u@.+..
22:25:25.160612 IP 10.0.2.15.1503 > 10.0.2.2.53752: Flags [P.], seq 1:495, ack 337, win 30016, length 494
E...N.@.@..>
...
.......r.!....RP.u@....HTTP/1.1 200 OK
Server: nginx/1.14.0 (Ubuntu)
Date: Tue, 04 Jun 2019 19:25:25 GMT
Content-Type: application/json
Transfer-Encoding: chunked
Connection: keep-alive

137
{"request_id":"1","payload":{"user_id":"1","devices":[{"id":0,"name":"\u0421\u0432\u0435\u0442","room":"\u0414\u0435\u0442\u0441\u043a\u0430\u044f","type":"devices.types.light"},{"id":1,"name":"\u0412\u044b\u0442\u044f\u0436\u043a\u0430","room":"\u041a\u0443\u0445\u043d\u044f","type":"devices.types.switch"}]}}
0


*/


/*
.POST /provider/v1.0/user/devices/query HTTP/1.1
Connection: upgrade
Host: smarthome.ged.korshunov.ru
X-Real-IP: 37.9.68.170
X-Forward-For: 37.9.68.170
X-Forward-Proto: http
X-Nginx-Proxy: true
Content-Length: 250
authorization: Bearer acceess123456789
x-request-id: 85fbc473-6c07-43fe-ba59-d81710c46216
content-type: application/json
accept-encoding: gzip
user-agent: Go-http-client/2.0

{"devices":[{"id":"4","custom_data":{"http":"http://192.168.7.189:7780/objects/?object=KinderRoomLamp1\u0026op=m\u0026m=switch2","http_status":"","httpoff":"","httpon":"","mqtt":{"set":"cmnd/kitchen/light/power","stat":"stat/kitchen/light/POWER"}}}]}
19:26:51.993643 IP 10.0.2.15.8088 > 10.0.2.2.26270: Flags [.], ack 653, win 29992, length 0
E..(;8@.@...
...
.....f..@.W....P.u(.+..
19:26:51.995695 IP 10.0.2.15.8088 > 10.0.2.2.26270: Flags [P.], seq 1:704, ack 653, win 29992, length 703
E...;9@.@...
...
.....f..@.W....P.u(....HTTP/1.1 200 OK
X-Powered-By: Express
Content-Type: application/json; charset=utf-8
Content-Length: 489
ETag: W/"1e9-PHLzU8j9QV9jWN1BvMo3U93JZgc"
Date: Wed, 05 Jun 2019 16:26:51 GMT
Connection: keep-alive

{"request_id":"1","payload":{"devices":[{"id":"4","name":"................ ........","description":"","type":"devices.types.other","room":"..............","custom_data":{"mqtt":{"set":"cmnd/kitchen/light/power","stat":"stat/kitchen/light/POWER"},"http":"http://192.168.7.189:7780/objects/?object=KinderRoomLamp1&op=m&m=switch2","httpon":"","httpoff":"","http_status":""},"capabilities":[{"type":"devices.capabilities.on_off","retrievable":true,"state":{"instance":"on","value":false}}]}]}}
19:26:51.996004 IP 10.0.2.2.26270 > 10.0.2.15.8088: Flags [.], ack 704, win 65535, length 0

 */ 
 
/*
	case (strpos($orig,'/v1.0/user/devices/query') ? true : false): 
		$dev_id=$data->devices[0]->id;
			$message='{"request_id":"1","payload":{
				"devices":[{
				"id":"'.$dev_id.'",
				"name":"................ ........",
				"description":"",
				"type":"devices.types.other",
				"room":"..............",
				"custom_data":{
					"mqtt":{
						"set":"cmnd/kitchen/light/power",
						"stat":"stat/kitchen/light/POWER"
					},
					"http":"http://192.168.1.1:70/objects/?object=KinderRoomLamp1&op=m&m=switch2",
					"httpon":"",
					"httpoff":"",
					"http_status":""
				},
				"capabilities":[{
					"type":"devices.capabilities.on_off",
					"retrievable":true,
					"state":{
						"instance":"on",
						"value":false
					}
				}]
			}]}}';
		break;

*/
