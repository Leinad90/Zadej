<?php

declare(strict_types=1);


namespace App\Tasks;

use DivisionByZeroError;
use Random\RandomException;

/**
 * Description of MathTask
 *
 * @author Daniel Hejduk <daniel.hejduk at gmail.com>
 */
class MathTask extends ITask {
    
    private ?float $solved = null;
    
    private string $task;

    public function __construct(float $min=0, float $max=1, int $operators=2, int $operands=2, private float $step=1) {
        parent::__construct();
        do {
            try {
                $this->task = '';
                $operands = random_int(2, $operands);
                for ($i = 1; $i < $operands; $i++) {
                    $randomNumber = $this->randomOperand($min, $max, $step);
                    while ($operands - $i - $this->countBrackets() > 1 && !rand(0, 10)) {
                        $this->task .= ' (';
                    }

                    $this->task .= $randomNumber;

                    while ($this->countBrackets() && !rand(0, 10)) {
                        $this->task .= ') ';
                    }
                    $this->task .= ' ' . $this->createOperator($operators) . ' ';
                }
                $randomNumber = $this->randomOperand($min, $max, $step);
                $this->task .= $randomNumber;
                while ($this->countBrackets()) {
                    $this->task .= ') ';
                }
                $this->getResult();
                $valid = true;
            } catch (DivisionByZeroError) {
                $valid = false;
            }
        } while (!$valid);
    }
    
    protected function countBrackets() : int
    {
        return substr_count($this->task, '(') - substr_count($this->task, ')');
    }

    protected function randomOperand(float $min, float $max, float $step=1): string
    {
        $return = $this->randomNumber($min, $max, $step);
        if($return<0) {
            $return = '('.$return.')';
        }
        return (string)$return;
    }

    protected function randomNumber(float $min, float $max, float $step=1): float
    {
        try {
            $random = random_int(0, PHP_INT_MAX) / PHP_INT_MAX;
        } catch (RandomException $e) {
            $random = mt_rand() / mt_getrandmax();
        }
        $diff = $max - $min;
        return $this->round($random*$diff + $min, $step);
    }
    
    protected function round(float $value, float $step=1, int $mode=PHP_ROUND_HALF_UP): float
    {
        $diff=fmod($value,$step);
        $return = $value-$diff;
        if($diff>$step/2 || ($diff===$step/2 && $mode===PHP_ROUND_HALF_UP)) {
             $return += $step;
        }
        return $return;
    }


    protected function createOperator(int $difficulty):string
    {
        $operators = '+-*/^';
        $index = random_int(0, $difficulty);
        return $operators[$index];
    }

    public function getTask() : string 
    {
        return $this->task;
    }
    
    public function getTaskType() : string 
    {
        return $this::TYPE_NUMBER;
    }
    
    public function getRegexp() : string
    {
        return "(\+|\-){0,1}[0-9| ]+((\.|,)[0-9| ]+){0,1}";
    }
    
    public function rank(mixed $givenResult) : float
    {
        $this->solvedOn = new \Nette\Utils\DateTime();
        if(!is_numeric($givenResult)) {
            return $this->solved = 0;
        }
        $correct= $this->getResult();
        $givenResult = (float)$givenResult;
        if($this->isEqual($givenResult, $correct))  {
            return $this->solved = 1;
        }

        return $this->solved = min(fdiv($givenResult,$correct), fdiv($correct,$givenResult));
    }
    
    private function isEqual(float $a, float $b) : bool
    {
        return abs($a-$b)<$this->getStep();
    }
    
    public function solved() : ?float
    {
        return $this->solved;
    }
    
    public function getResult() : float
    {
        $stringCalc = new \ChrisKonnertz\StringCalc\StringCalc();
        return $stringCalc->calculate($this->getTask());
    }
    
    public function getStep(): float
    {
        return $this->step;
    }
}
