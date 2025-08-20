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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


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
            MenuItem::linkToRoute('Ajustement de points', 'fa fa-plus-minus', 'admin_adjust_points'),


            MenuItem::section('Configuration'),
            MenuItem::linkToCrud('Historic', 'fa fa-clock', Historic::class), 

            MenuItem::section('Security'),
            MenuItem::linkToCrud('Administrators', 'fa fa-lock', User::class), 
            MenuItem::linkToUrl('Logout', 'fa-solid fa-arrow-right-to-bracket', '/logout'), 

         ];
    }

    #[Route('/admin/adjust', name: 'admin_adjust_points')]
    public function adjust(Request $request, EntityManagerInterface $entityManager): Response
    {
        $children = $entityManager->getRepository(Child::class)->findAll();

        if ($request->isMethod('POST')) {
            $childId = (int) $request->request->get('child');
            $points = (int) $request->request->get('points');
            $description = (string) $request->request->get('description');
            $token = (string) $request->request->get('_token');

            if (!$this->isCsrfTokenValid('adjust_points', $token)) {
                $this->addFlash('danger', 'Token CSRF invalide');
                return $this->redirectToRoute('admin_adjust_points');
            }

            $child = $entityManager->getRepository(Child::class)->find($childId);
            if (!$child) {
                $this->addFlash('danger', 'Enfant introuvable');
                return $this->redirectToRoute('admin_adjust_points');
            }

            $historic = new Historic();
            $historic->setChild($child);
            $historic->setTask(null);
            $historic->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $historic->setDescription($description ?: 'Ajustement manuel');
            $historic->setPoints($points);

            $entityManager->persist($historic);

            $child->setPoints((int)$child->getPoints() + $points);
            $entityManager->persist($child);
            $entityManager->flush();

            $this->addFlash($points >= 0 ? 'success' : 'danger', 'Ajustement appliqué');
            return $this->redirectToRoute('admin_adjust_points');
        }

        return $this->render('admin/adjust_points.html.twig', [
            'children' => $children,
        ]);
    }
}
