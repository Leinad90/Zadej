<?php

declare(strict_types=1);

namespace App\Tasks;


/**
 *
 * @author daniel Hejduk
 */
abstract class ITask {
    
    use \Nette\SmartObject;

    protected ?\Nette\Utils\DateTime $solvedOn = null;
    const string TYPE_NUMBER = "number";
    
    protected \Nette\Utils\DateTime $startedOn;

    
    public function __construct()
    {
        $this->startedOn = new \Nette\Utils\DateTime();
    }

    abstract public function getTask() : string;
    
    abstract public function getTaskType() : ?string; 
    
    abstract public function getRegexp() : ?string;

    abstract public function rank(mixed $givenResult) : float;
    
    abstract public function solved() : ?float;
    
    public function getStep() : ?float 
    {
        return NULL;
    }

    abstract public function getResult() : string|float|int;

    public function getStartedOn() : \Nette\Utils\DateTime
    {
        return $this->startedOn;
    }

    public function getSolvedOn() : ?\Nette\Utils\DateTime
    {
        return $this->solvedOn;
    }
}
