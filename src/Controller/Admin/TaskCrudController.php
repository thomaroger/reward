<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use App\Enum\FrequencyEnum;
use App\Enum\TypeEnum;

class TaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

     public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            IntegerField::new('points'),
            ChoiceField::new('frequency')->setChoices(FrequencyEnum::get()),
            ChoiceField::new('type')->setChoices(TypeEnum::get()),
            TextField::new('logo'),
            IntegerField::new('order'), 
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('frequency')->setChoices(FrequencyEnum::get()))
            ->add(ChoiceFilter::new('type')->setChoices(TypeEnum::get()));
    }
}
