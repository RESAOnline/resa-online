<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Exception extends \Exception
{
	private $options;

    public function __construct($message, $options) 
    {
        parent::__construct($message, 0, null);

        $this->options = $options; 
    }

    public function getOptions() { return $this->options; }
	public function toJSON(){
		return '{
			"isError":true,
			"message":"'.$this->getMessage().'",
			"options":'.json_encode($this->options).'			
		}';
	}
}