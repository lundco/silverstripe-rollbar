<?php

namespace silverstripe\rollbar;

use \Rollbar\Rollbar;
use \Rollbar\Payload\Level;

require_once THIRDPARTY_PATH . '/Zend/Log/Writer/Abstract.php';

/**
 * The RollbarLogWriter class simply acts as a bridge between the configured Rollbar
 * adaptor and SilverStripe's {@link SS_Log}.
 *
 * Usage in your project's _config.php for example (See README for examples).
 *
 *    SS_Log::add_writer(\silverstripe\rollbar\RollbarLogWriter::factory(), '<=');
 */

class RollbarLogWriter extends \Zend_Log_Writer_Abstract
{

	protected $accessToken;

    /**
     * A static constructor.
     *
     * @param  array $config    An array of optional additional configuration for
     *                          passing custom information to Rollbar. See the README for more detail.
     * @return RollbarLogWriter
     */
    public static function factory($config = [])
    {
		//Inject Rollbar logwriter
        $writer = \Injector::inst()->get('RollbarLogWriter');

		$RollbarConfig = array(
			'access_token' => 'f6cdd31fdcce4b0fb83002ece38c4ace',
			'transformer' => '\silverstripe\rollbar\Adaptor\RollbarTransformer',
			'environment' => self::getEnv()
		);

		//Initialize rollbar with build config settings
		Rollbar::init($RollbarConfig);

        return $writer;
    }


    /**
     * _write() forms the entry point into the physical sending of the error.
     *
     * @param  array $event An array of data that is created in, and arrives here
     *                      via {@link SS_Log::log()} and {@link Zend_Log::log}.
     * @return void
     */
    protected function _write($event)
    {
    	//Set error message
		$title = 'Err no. ' . $event['message']['errno'] . ': ' . $event['message']['errstr'];                             // From SS_Log::log()

		//Add line number and filename to error message, to make errors unique to each file
		$title .= ' - On line ' . $event['message']['errline'] . ' in ' . $event['message']['errfile'];

        $custom = $this->getCustom($event);

		//Log error to rollbar
		Rollbar::log($this->getLevel($event['priorityName']),$title,$custom);

    }

	public function getLevel($SS_Level){

    	//Logger will return ERROR as default when logging

    	switch ($SS_Level){
			case 'ERR':
				return Level::ERROR;
				break;
			case 'WARN':
				return Level::WARNING;
				break;
			case 'NOTICE':
				return Level::NOTICE;
				break;
			case 'INFO':
				return Level::INFO;
			case 'DEBUG':
				return Level::DEBUG;
			default:
				return Level::ERROR;
		}
	}

    public function getCustom($event){
    	return array(
			'tracestack' => $this->getTraces($event),
			'event' => $event,
			'environment' => $this->getEnv(),
			'gitBranch' => $this->getGitBranch(),
			'extra' => (isset($event['extra']) ? $event['extra'] : null)
		);
	}


    public function getTraces($event){
		//Get the debug backtrace
		$bt = debug_backtrace();

		// Use given context if available
		if (!empty($event['message']['errcontext'])) {
			$bt = $event['message']['errcontext'];
		}

		// Push current line into context
		array_unshift($bt, [
			'file' => $event['message']['errfile'],
			'line' => $event['message']['errline'],
			'function' => '',
			'class' => '',
			'type' => '',
			'args' => [],
		]);

		$traces = \SS_Backtrace::filter_backtrace($bt, [
			'RollbarLogWriter->_write',
			'silverstripe\rollbar\RollbarLogWriter->_write'
		]);

		return $traces;
	}


	/**
	 * Returns either development or production depending on SS enviroment
	 *
	 * @return string
	 */
	public static function getEnv()
	{
		$env = \Director::get_environment_type();

		switch ($env){
			case 'dev':
				return 'development';
			case 'live':
				return 'production';
		}

	}

	/**
	 * Get current Git branch that's checked out on the server
	 *
	 * @return string
	 */
	private function getGitBranch()
	{
		try {
			if (function_exists('shell_exec')) {
				$output = rtrim(shell_exec('git rev-parse --abbrev-ref HEAD'));
				if ($output) {
					return $output;
				}
			}
			return null;
		} catch (\Exception $e) {
			return null;
		}
	}


    /**
     * What sort of request is this? (A harder question to answer than you might
     * think: http://stackoverflow.com/questions/6275363/what-is-the-correct-terminology-for-a-non-ajax-request)
     *
     * @return string
     */
    public function getRequestType()
    {
        $isCLI = $this->getSAPI() !== 'cli';
        $isAjax = \Director::is_ajax();

        return $isCLI && $isAjax ? 'AJAX' : 'Non-Ajax';
    }

    /**
     * @return string
     */
    public function getSAPI()
    {
        return php_sapi_name();
    }

}
