<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Component\BestOfTable;
use App\Model\Solvers;
use Nette;
use Nette\Application\UI\Form;


class HomepagePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(protected readonly Solvers $solvers)
    {
        parent::__construct();
    }

    public function renderDefault()
    {
        $this->template->form = $this['form'];
        $this->template->tests= $this->getTests();
        $this->template->solvers = $this->solvers->getAll();
    }
    
    public function createComponentForm(): Form {
        $form = new Form($this,'form');
        $form->addText('name','Jméno');
        //$form->addRadioList('Type','Hra',['solo'=>'Sám','pvp'=>'Vyzvi soupeře']);
        $form->addRadioList('Test', 'Soutěž', $this->getTestsForCheckbox())->setRequired(true);
        $form->addSubmit('sent','Zadat');
        $form->onSuccess[]=function($form, $formData) {$this->processForm($form, $formData);};
        return $form;
    }
    
    private function processForm(Form $form, \Nette\Utils\ArrayHash $formData): never
    {
        $exploded = explode('?', $formData->Test); 
        $this->redirect($exploded[0], $formData);
    }


    protected function getTestsForCheckbox()
    {
        $return = [];
        foreach ($this->getTests() as $class => $tests) {
            if($class==0) {
                continue;
            }
            foreach ($tests as $link => $description) {
                $return[$link]=$description;
            }
        }
        return $return;
    }
    
    protected function getTests() : array
    {
        return [
            0=>['math'=>'Matematika'],
            1=>['Test:default?1'=>'Sčítání od jedné do desíti'],
            2=>['Test:default?2'=>'Sčítání a odčítání od jedné do sta'],
            3=>['Test:default?3'=>'Sčítání, odčítání a násobení od mínus sta do sta'],
            4=>['Test:default?3'=>'Sčítání, odčítání, násobení a dělení od mínus sta do sta']
        ];
    }

    protected function createComponentTable(): BestOfTable
    {
        $table = new BestOfTable($this->solvers, $this);
        return $table;
    }
}
