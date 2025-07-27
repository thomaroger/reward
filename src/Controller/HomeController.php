<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Child;
use App\Entity\Task;
use App\Entity\Historic;
use App\Enum\TypeEnum;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $selectedchild = null;
        $children = $entityManager->getRepository(Child::class)->findAll();
        $tasksBonus = $entityManager->getRepository(Task::class)->findBy(array('type'=> TypeEnum::BONUS));
        $tasksPenalties = $entityManager->getRepository(Task::class)->findBy(array('type'=> TypeEnum::PENALTY));
        $tasksConsumptions = $entityManager->getRepository(Task::class)->findBy(array('type'=> TypeEnum::CONSUMPTION));

        if(!empty($request->cookies->get('child'))) {
            $selectedchild = $entityManager->getRepository(Child::class)->find($request->cookies->get('child'));
        }

        return $this->render('home/index.html.twig', [
            'children' => $children,
            'tasksBonus' => $tasksBonus,
            'tasksPenalties' => $tasksPenalties,
            'tasksConsumptions' => $tasksConsumptions,
            'types' => TypeEnum::get(),
            'selectedchild'=> $selectedchild,
        ]);
    }

    #[Route('/child/{id}', name: 'app_home_child')]
    public function child(int $id): Response
    {
        $cookie = new Cookie('child', $id, time() + (2 * 60 * 60));
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->sendHeaders();
        return $this->redirectToRoute('app_home');

    }

    #[Route('/task', name: 'app_home_task')]
    public function task(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $post = $request->getPayload()->all();

        $child = $entityManager->getRepository(Child::class)->find($request->cookies->get('child'));
        $task = $entityManager->getRepository(Task::class)->find($post['task']);
        
        $historic = new Historic();
        $historic->setChild($child);
        $historic->setTask($task);
        $historic->setCreatedAt(new \DateTimeImmutable('now'));
        $entityManager->persist($historic);

        $child->setPoints($child->getPoints() + $task->getPoints());
        $entityManager->persist($child);
        $entityManager->flush();


        return $this->redirectToRoute('app_home');

    }

    #[Route('/historic', name: 'app_home_historic')]
    public function historics(EntityManagerInterface $entityManager, Request $request): Response
    {
        $selectedchild = null;
        $historics = array();
        $children = $entityManager->getRepository(Child::class)->findAll();


        if(!empty($request->cookies->get('child'))) {
            $selectedchild = $entityManager->getRepository(Child::class)->find($request->cookies->get('child'));
            $historics = $entityManager->getRepository(Historic::class)->findby(array('child'=>$selectedchild));
        }
        
        return $this->render('home/historic.html.twig', [
            'children' => $children,
            'historics' => $historics,
            'selectedchild'=> $selectedchild,
        ]);
    }

}
