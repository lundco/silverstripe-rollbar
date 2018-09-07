<?php

namespace silverstripe\rollbar\Adaptor;

use Rollbar\TransformerInterface;
use Rollbar\Payload\Level;
use Rollbar\Payload\Payload;
use Rollbar\Payload\Body;
use Rollbar\Payload\Trace;
use Rollbar\Payload\ExceptionInfo;

class RollbarTransformer implements TransformerInterface{


	/**
	 * Add tracestack to rollbar payload
	 *
	 * @param Payload $payload
	 * @param Level $level
	 * @param \Exception | \Throwable $toLog
	 * @param $context
	 * @return Payload
	 */
	public function transform(Payload $payload, $level, $toLog, $context)
	{
		//Get custom array passed from the RollbarLogWriter
		$custom = $payload->getData()->getCustom();


		if(isset($custom['event'])){
			//Get event and traces data
			$traces = $custom['tracestack'];
			$event = $custom['event'];
			//$extra = $custom['extra'];

			//Build new body for rollbar payload
			$trace = new Trace($this->getFrames($traces), $this->getExceptionInfo($event));
			$body = new Body($trace);

			//Set body with updated trace stack
			$payload->getData()->setBody($body);
		}

		//Set custom payload holder from extra array
		//$payload->getData()->setCustom($extra);

		//Return the payload object
		return $payload;

	}

	protected function getExceptionInfo($event){

		$eventMessage = $event['message']['errstr'];
		$messageSplit = explode(': ',$eventMessage);

		if(count($messageSplit)>1){
			return new ExceptionInfo($messageSplit[0],$messageSplit[1]);
		}else{
			return new ExceptionInfo($eventMessage,null);
		}


	}

	protected function getFrames($traces){
		//Initialise frames
		$frames = [];

		//Generate traceback
		foreach ($traces as $trace){

			$method = '';

			if(isset($trace['class'])){
				$method .= $trace['class'] . '::';
			}

			if(isset($trace['function'])){
				$method .= $trace['function'];
			}

			$frames[] = array(
				'filename' => $trace['file'],
				'lineno' => $trace['line'],
				'method' => $method
			);
		}

		return $frames;
	}

}
