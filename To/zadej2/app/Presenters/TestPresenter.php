<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Solvers;
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


    public function renderDefault(array|\ArrayAccess $formData = []) 
    {
        $session = $this->getSession(self::SESSION_NAME);
        if(empty($session->TaskList) || count($session->TaskList)==0 || true) {
            $session->class = $class = (int)explode('?', $formData['Test'])[1];
            $diffucityData = $this->getDifucityData($class);
            $exprs = $diffucityData['exprs'];
            unset($diffucityData['exprs']);
            $session->TaskList = new \App\Tasks\TaskList($exprs, new \App\Tasks\MathTask(),$diffucityData);
            $session->name = $formData['name'];
        }
    }
    
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
        $taskList = $session->TaskList;
        if(!$taskList instanceof TaskList) {
            throw new \Exception('');
        }
        foreach ($taskList as $id =>  $Task) {     
            $elem = $form->addText((string)$id, $Task->getTask());
            if($Task->solved()!==null) {
                $showResult = true;
                $elem->disabled = true;
                $elem->caption .= ' = '.$Task->getResult();
                $elem->addError($Task->solved().' '.'bodů'); 
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
                $elem->addRule(validator: $form::PATTERN, errorMessage: "Výsledek musí odpovídat masce %s", arg: $regexp);
            }
        }
        if($allowSend) {
            $form->addText('started_on','Zadáno')->setDisabled()->setDefaultValue($taskList->getStartedOn()->format('H:i:s,v'))->setHtmlId('started_on');
            $form->addText('actual_time','Aktuální čas')->setDefaultValue(date('H:I:S,v'))->setDisabled()->setHtmlId('actual_time');
            $form->addSubmit("sent", "Vyhodnotit");
        }
        if($showResult) {
            $form->addText('total','Celkem: ')->setDisabled()->addError($session->rank.' '.'bodů');
            $form->addText('time','Čas: ')->setDisabled()->setDefaultValue($taskList->getStartedOn()->format('H:i:s,v').' - '.$taskList->getSolvedOn()->format('H:i:s,v'))->addError($taskList->getSolvingTime().' '.'sekund');
        }
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
    
   public function formSucceeded(Form $form, \Nette\Utils\ArrayHash $formData): void
	{
        $session = $this->getSession(self::SESSION_NAME);
        $taskList = $session->TaskList;
        if(!$taskList instanceof TaskList) {
            throw new \Exception('');
        }
        $session->rank = $rank = $taskList->rank($formData);
        $row = [
            'name' => $session->name,
            'class' => $session->class,
            'time' => $taskList->getSolvingTime(),
            'points' => $rank
        ];
        $this->solvers->insert($row);
        $this->redirect('rank',$formData);
	}
    
    public function renderRank(\ArrayAccess|array $formData)
    {
        $this['form']->setDefaults($formData);
    }

}
