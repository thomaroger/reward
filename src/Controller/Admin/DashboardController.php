<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Child;
use App\Entity\Task;
use App\Entity\Historic;


#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Reward');

    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToUrl('Frontend', 'fa fa-globe', '/'),
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section('Configuration'),
            MenuItem::linkToCrud('Child', 'fa fa-user', Child::class), 
            MenuItem::linkToCrud('Task', 'fa fa-list-check', Task::class), 

            MenuItem::section('Configuration'),
            MenuItem::linkToCrud('Historic', 'fa fa-clock', Historic::class), 

            MenuItem::section('Security'),
            MenuItem::linkToCrud('Administrators', 'fa fa-lock', User::class), 
            MenuItem::linkToUrl('Logout', 'fa-solid fa-arrow-right-to-bracket', '/logout'), 

         ];
    }
}
