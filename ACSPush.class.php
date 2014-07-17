<?php

/**
 * PHP-ACS-PushNotifications - A PHP Class to easily send push notifications using Appcelerator ACS REST API
 * @author Ugo Stephant (http://www.ugostephant.io)
 * @version 1.0
 * @license This file is subject to the terms and conditions defined in file 'LICENSE', which is part of this source code package.
 */
class ACSPush{


	/**
	 * The channel on which you want to send your notifications (empty string means "all channels")
	 * @public
	 * @property {String} $channel
	 * @default ""
	 */
	public $channel = "";


	/**
	 * The title of your notification (only works with Android service, iOS shows app title)
	 * @public
	 * @property {String} $title
	 * @default ""
	 */
	public $title = "";


	/**
	 * Your notification message
	 * @public
	 * @property {String} $message
	 * @default ""
	 */
	public $message = "";


	/**
	 * The number you want to show on your app icon badge
	 * @public
	 * @property {String} $badge
	 * @default "+1"
	 */
	public $badge = "+1";


	/**
	 * Your recipients (array of device tokens)
	 * @property {Array} $to
	 */
	public $to;

	/**
	 * Wether the phone should vibrate when a push notifications comes (android only)
	 * @public
	 * @property {Boolean} $vibrate
	 * @default true
	 */
	public $vibrate = true;


	/**
	 * Sound to be played when a push notification comes out
	 * @public
	 * @property {String} $sound
	 * @default "default"
	 */
	public $sound = "default";


	/**
	 * Your Appcelerator Cloud APP Key
	 * @rivate
	 * @property {String} $key
	 */
	private $key;


	/**
	 * Creditentials used to login on ACS API
	 * @private
	 * @property {Array} $creditentials
	 */
	private $creditentials;


	/**
	 * CURL object
	 * @private
	 * @property {Ressource} $curl_obj
	 */
	private $curl_obj;


	/**
	 * CURL settings
	 * @private
	 * @property {Array} $curl_opt
	 */
	private $curl_opt;


	/**
	 * CURL call result
	 * @private
	 * @property {String} $curl_result
	 */
	private $curl_result;


	/**
	 * Appcelerator Cloud API url
	 * @private
	 * @property {String} $acs_api_url
	 */
	private $acs_api_url = "https://api.cloud.appcelerator.com/v1";


	/**
	 * JSON query
	 * @private
	 * @property {String} $query
	 */
	private $query;


	/**
	 * Wether the push call if closed or not
	 * @private
	 * @property {Boolean} $closed
	 */
	private $closed = false;


	/**
	 * @constructor
	 * @param {String} $username - Your Appcelerator username
	 * @param {String} $password - Indeed, your Moulinex password........wait, what ?
	 * @param {String} $key - Your Appcelerator App Key (typically something like 8DSB1HZ0pXXXxXX19ZrwoxXXXxxM31YK)
	 */
	public function __construct($username, $password, $key){

		//Set creditentials
		$this->creditentials = array("login" => $username, "password" => $password);
		$this->key = $key;

		//Init curl
		$this->curl_obj = curl_init();
		$this->curl_opt = array(
			CURLOPT_URL => $this->acs_api_url."/users/login.json?key=".$this->key,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => "login=".$this->creditentials["login"]."&password=".$this->creditentials["password"],
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_TIMEOUT => 60
		);

		//Login
		curl_setopt_array($this->curl_obj, $this->curl_opt);
		$this->curl_result = curl_exec($this->curl_obj);

	}


	/**
	 * Used to construct API query
	 * @private
	 * @method construct_query
	 * @param {String} $api
	 * @return {String}
	 */
	private function construct_query($api){

		//Init url
		$this->curl_opt[CURLOPT_URL] = $this->acs_api_url."/push_notification/".$api.".json?key=".$this->key;

		//Init json payload
		$json = array(
			"alert" => $this->message,
			"title" => $this->title,
			"badge" => $this->badge,
			"vibrate" => $this->vibrate,
			"sound" => $this->sound
		);

		//Init fields
		$fields = array("channel" => $this->channel, "payload" => json_encode($json));
		if(isset($this->to) && !empty($this->to) && count($this->to) > 0) $fields["to_tokens"] = $this->to;

		//Init result
		$this->curl_opt[CURLOPT_POSTFIELDS] = http_build_query($fields, "", "&");
	}


	/**
	 * When you have set all your infos, thou can send thy notification to everybody in the world
	 * @public
	 * @method send
	 */
	public function notify(){
		if($this->closed === false){

			$this->construct_query("notify");
			curl_setopt_array($this->curl_obj, $this->curl_opt);
			$this->curl_result = curl_exec($this->curl_obj);

		}
	}


	public function notify_tokens(){
		if($this->closed === false){

			$this->construct_query("notify_tokens");
			curl_setopt_array($this->curl_obj, $this->curl_opt);
			$this->curl_result = curl_exec($this->curl_obj);

		}
	}


	/**
	 * Like you do with MySQL, don't forget to close the query
	 * @public
	 * @method close
	 */
	public function close(){

		//Close curl
		curl_close($this->curl_obj);

		//Avoid reuse of our object
		$this->closed = true;
	}

}
