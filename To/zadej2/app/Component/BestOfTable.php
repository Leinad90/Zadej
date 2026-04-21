<?php

namespace App\Component;

use App\Model\Solvers;
use Nette\Application\UI\Control;

class BestOfTable extends Control
{

    public function __construct(
        protected readonly Solvers $solvers
    ) {
        //parent::__construct($parent, $name);
    }

    public function render(): void
    {
        $this->getTemplate()->solvers=$this->solvers->getAll();
        $this->getTemplate()->setFile(__DIR__.'/templates/bestOfTable.latte')->render();
    }

}