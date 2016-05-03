<?php
class VariableVO
{
	public $autoid;
	public $code;
	public $question;
    public $answers;
	
	public function __construct()
	{
		$this->autoid = -1;
		$this->answers = array();
	}
}
?>