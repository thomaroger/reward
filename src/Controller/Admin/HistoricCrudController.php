<?php

namespace App\Controller\Admin;

use App\Entity\Historic;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;



use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class HistoricCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Historic::class;
    }

    public function configureFields(string $pageName): iterable
{
    return [
        AssociationField::new('child'),
        AssociationField::new('task'),
        DateTimeField::new('createdat'),
    ];
}

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(100)
            ->setPaginatorRangeSize(4)
            ->setPaginatorUseOutputWalkers(true)
            ->setPaginatorFetchJoinCollection(true)
        ;
    }

     public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('child'));
    }



}
