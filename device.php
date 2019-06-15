<?php
#https://demo.shome52.ru/provider/v1.0/user/devices
#https://demo.shome52.ru/provider/v1.0/user/devices/query
##https://demo.shome52.ru/auth
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
#	$dev_id=6;

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
        "instance": "'.$range_name.'",
	 "value": '.$value.'
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
$message.='        
            ]}]
    }
}';

		debug($message,0,"message_query:");
		break;
	case (strpos($orig,'/v1.0/user/unlink') ? true : false): 
		$message="Unlink.";
		break;
	default:
		$message=$devices->getList();
}

header('Content-Type: application/json; charset=utf-8');
header("Content-Length: ".strlen($message));
echo $message;

exit;
