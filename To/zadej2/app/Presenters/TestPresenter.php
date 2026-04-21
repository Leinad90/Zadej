<?php

declare(strict_types=1);

namespace App\Presenters;

use App\DTO\GameSelectData;
use App\Model\Solvers;
use App\Tasks\MathTask;
use App\Tasks\TaskList;
use Nette;
use Nette\Application\UI\Form;

class TestPresenter extends Nette\Application\UI\Presenter
{
    
    const SESSION_NAME = 'TaskList';


    public function __construct(
        protected readonly Solvers $solvers
    ) {
        parent::__construct();
    }


    public function renderDefault(GameSelectData $formData) : void
    {
        /*if($formData->type==='pvp') {

        }*/
        $this->getTaskList((int)explode('?', $formData->Test)[1],$formData->name);
    }

    private function getTaskList(?int $class=null, ?string $name=null): TaskList
    {
        $session = $this->getSession(self::SESSION_NAME);
        if(empty($session->TaskList) || !$session->TaskList instanceof TaskList) {
            if($class !== null) {
                $session->set('class',$class);
            }
            $classValue = $session['class'];
            if (!is_int($classValue)) {
                throw new \InvalidArgumentException('Class must be an integer.');
            }
            $diffucityData = $this->getDifucityData($classValue);
            $exprs = $diffucityData['exprs'];
            unset($diffucityData['exprs']);
            $session->TaskList = new TaskList($exprs, new MathTask(),$diffucityData);
            if($name) {
                $session->set('name',$name);
            }
        }
        return $session->TaskList; /** @phpstan-ignore return.type (Tasklist is always set) */
    }

    /**
     * @param int $class
     * @return array<string, int>
     */
    protected function getDifucityData(int $class) : array
    {
        $return = ['exprs'=>10,
                    'min'=> 1,
                    'max'=>10,
                    'step'=>1,
                    'operands'=>2,
                    'operators'=>0
            ];
        if($class>=2) {
            $return = array_merge($return,['exprs'=>20,
                    'min'=>0,
                    'max'=>100,
                    'operands'=>3,
                    'operators'=>1
                    ]);
        }
        if($class>=3) {
            $return = array_merge($return,[
                    'min'=>-100,
                    'operands'=>4,
                    'operators' => 2
                    ]);
        }
        if($class>=4) {
            $return = array_merge($return,[
                    'operands'=>5,
                    'operators' => 3
            ]);
        }
        return $return;
    }


    protected function createComponentForm(): Form
	{
        $session = $this->getSession(self::SESSION_NAME);
        $form = new Form();
        $form->addProtection();
        $allowSend = $showResult = false;
        $taskList = $this->getTaskList();
        foreach ($taskList as $id =>  $Task) {     
            $elem = $form->addText((string)$id, $Task->getTask());
            if($Task->getSolvedOn()!==null) {
                $showResult = true;
                $elem->disabled = true;
                $elem->caption .= ' = '.$Task->getResult();
                $elem->addError((string)$Task->solved().' '.'bodů');
            } else {
                $allowSend = true;
            }
            if( ($taskType = $Task->getTaskType()) ) {
                $elem->setHtmlType($taskType);
            }
            if( ($step = $Task->getStep()) ) {
                $elem->setHtmlAttribute('step', $step);
            }
            if( ($regexp = $Task->getRegexp()) ) {
                $elem->addRule(validator: Form::Pattern, errorMessage: "Výsledek musí odpovídat masce %s", arg: $regexp);
            }
        }
        if($allowSend) {
            $form->addText('started_on','Zadáno')->setDisabled()->setDefaultValue($taskList->getStartedOn()->format('H:i:s,v'))->setHtmlId('started_on');
            $form->addText('actual_time','Aktuální čas')->setDefaultValue(date('H:i:s,v'))->setDisabled()->setHtmlId('actual_time');
            $form->addSubmit("sent", "Vyhodnotit");
        }
        if($showResult) {
            $form->addText('total','Celkem: ')->setDisabled()->addError($session->rank.' '.'bodů');
            $form->addText('time','Čas: ')->setDisabled()->setDefaultValue($taskList->getStartedOn()->format('H:i:s,v').' - '.$taskList?->getSolvedOn()->format('H:i:s,v'))->addError($taskList->getSolvingTime().' '.'sekund');
        }
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
    
   public function formSucceeded(Form $form, array $formData): void
	{
        $session = $this->getSession(self::SESSION_NAME);
        $taskList = $session->TaskList;
        if(!$taskList instanceof TaskList) {
            throw new \Exception('');
        }
        $session->rank = $rank = $taskList->rank($formData);
        $nameValue = $session->name;
        $classValue = $session->class;
        if (!is_string($nameValue)) {
            trigger_error('Name is not string');
            $nameValue = '';
        }
        if (!is_int($classValue)) {
            trigger_error('Class is not int');
            $classValue = 0;
        }
        $row = [
            'name' => $nameValue,
            'class' => $classValue,
            'time' => $taskList->getSolvingTime(),
            'points' => $rank
        ];
        $this->solvers->insert($row);
        $this->redirect('rank',$formData);
	}
    
    public function renderRank(array $formData): void
    {
        $this['form']->setDefaults($formData);
    }

}
