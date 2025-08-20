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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


final class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        try {
            $selectedChild = $this->getSelectedChild($request);
            $currentDate = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
            
            $children = $this->entityManager->getRepository(Child::class)->findAll();
            $tasksBonus = $this->entityManager->getRepository(Task::class)->findBy(['type' => TypeEnum::BONUS]);
            $tasksPenalties = $this->entityManager->getRepository(Task::class)->findBy(['type' => TypeEnum::PENALTY]);
            $tasksConsumptions = $this->entityManager->getRepository(Task::class)->findBy(['type' => TypeEnum::CONSUMPTION]);

            $taskAlreadyDone = [];
            if ($selectedChild) {
                $tasks = $this->entityManager->getRepository(Historic::class)->findByDay($selectedChild, $currentDate);
                foreach ($tasks as $task) {
                    $taskAlreadyDone[$task['id']] = $task['id'];
                }
            }

            return $this->render('home/index.html.twig', [
                'children' => $children,
                'tasksBonus' => $tasksBonus,
                'tasksPenalties' => $tasksPenalties,
                'tasksConsumptions' => $tasksConsumptions,
                'types' => TypeEnum::get(),
                'selectedchild' => $selectedChild,
                'taskalreadydone' => $taskAlreadyDone,
            ]);
        } catch (\Exception $e) {
            error_log('Erreur lors du chargement de la page d\'accueil: ' . $e->getMessage());
            $this->addFlash('danger', 'Une erreur est survenue lors du chargement de la page.');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/child/{id}', name: 'app_home_child')]
    public function child(int $id): Response
    {
        try {
            $child = $this->entityManager->getRepository(Child::class)->find($id);
            if (!$child) {
                throw new NotFoundHttpException('Enfant non trouvé');
            }

            $cookie = new Cookie('child', $id, time() + (2 * 60 * 60), '/', null, true, true);
            $response = $this->redirectToRoute('app_home');
            $response->headers->setCookie($cookie);
            
            return $response;
        } catch (\Exception $e) {
            error_log('Erreur lors de la sélection de l\'enfant: ' . $e->getMessage());
            $this->addFlash('danger', 'Impossible de sélectionner cet enfant.');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/task', name: 'app_home_task', methods: ['POST'])]
    public function task(Request $request): Response
    {
        try {
            $this->validateTaskRequest($request);
            
            $childId = $request->cookies->get('child');
            $taskId = $request->getPayload()->get('task');
            
            if (!$childId || !$taskId) {
                throw new BadRequestHttpException('Données manquantes');
            }

            $child = $this->entityManager->getRepository(Child::class)->find($childId);
            $task = $this->entityManager->getRepository(Task::class)->find($taskId);
            
            if (!$child || !$task) {
                throw new NotFoundHttpException('Enfant ou tâche non trouvé');
            }

            // Anti double-soumission très rapprochée (2s)
            $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
            $since = $now->sub(new \DateInterval('PT2S'));
            $recent = $this->entityManager->getRepository(Historic::class)
                ->createQueryBuilder('h')
                ->andWhere('h.child = :child')
                ->andWhere('h.task = :task')
                ->andWhere('h.created_at > :since')
                ->setParameter('child', $child)
                ->setParameter('task', $task)
                ->setParameter('since', $since)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($recent) {
                $label = match ($task->getType()) {
                    TypeEnum::BONUS => 'success',
                    TypeEnum::PENALTY => 'danger',
                    TypeEnum::CONSUMPTION => 'warning',
                    default => 'danger',
                };
                $this->addFlash($label, 'Action déjà prise en compte.');
                return $this->redirectToRoute('app_home');
            }

            // Vérifier si la tâche peut être effectuée (fréquence, etc.)
            if (!$this->canPerformTask($child, $task)) {
                throw new BadRequestHttpException('Cette tâche ne peut pas être effectuée maintenant');
            }

            $this->performTask($child, $task);
            
            $label = match ($task->getType()) {
                TypeEnum::BONUS => 'success',
                TypeEnum::PENALTY => 'danger',
                TypeEnum::CONSUMPTION => 'warning',
                default => 'danger',
            };

            $this->addFlash($label, sprintf(
                'Tâche "%s" effectuée pour %s. Points: %+d',
                $task->getName(),
                $child->getFirstname(),
                $task->getPoints()
            ));

            return $this->redirectToRoute('app_home');
            
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'exécution de la tâche: ' . $e->getMessage());
            $this->addFlash('danger', 'Impossible d\'exécuter cette tâche.');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/historic', name: 'app_home_historic')]
    public function historics(Request $request): Response
    {
        try {
            $selectedChild = $this->getSelectedChild($request);
            $children = $this->entityManager->getRepository(Child::class)->findAll();
            $historics = [];

            if ($selectedChild) {
                $allHistorics = $this->entityManager->getRepository(Historic::class)
                    ->findBy(['child' => $selectedChild], ['created_at' => 'DESC']);
                
                foreach ($allHistorics as $historic) {
                    $historics[$historic->getCreatedAt()->format('d/m/Y')][] = $historic;
                }
            }
            
            return $this->render('home/historic.html.twig', [
                'children' => $children,
                'historics' => $historics,
                'selectedchild' => $selectedChild,
            ]);
        } catch (\Exception $e) {
            error_log('Erreur lors du chargement de l\'historique: ' . $e->getMessage());
            $this->addFlash('danger', 'Impossible de charger l\'historique.');
            return $this->redirectToRoute('app_home');
        }
    }

    // Méthodes privées pour améliorer la lisibilité et la réutilisabilité
    private function getSelectedChild(Request $request): ?Child
    {
        $childId = $request->cookies->get('child');
        if (!$childId) {
            return null;
        }
        
        return $this->entityManager->getRepository(Child::class)->find($childId);
    }

    private function validateTaskRequest(Request $request): void
    {
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Méthode HTTP non autorisée');
        }

        $payload = $request->getPayload();
        if (!$payload->has('task') || !$payload->has('_token')) {
            throw new BadRequestHttpException('Données de formulaire incomplètes');
        }

        $taskId = (string) $payload->get('task');
        $token = (string) $payload->get('_token');
        if (!$this->isCsrfTokenValid('task_' . $taskId, $token)) {
            throw new BadRequestHttpException('Token CSRF invalide');
        }
    }

    private function canPerformTask(Child $child, Task $task): bool
    {
        // Vérifier la fréquence de la tâche
        if ($task->getFrequency() === 1) { // Quotidienne
            $today = new \DateTimeImmutable('today', new \DateTimeZone('Europe/Paris'));
            $existingTask = $this->entityManager->getRepository(Historic::class)
                ->findOneBy(['child' => $child, 'task' => $task, 'created_at' => $today]);
            
            if ($existingTask) {
                return false; // Tâche déjà effectuée aujourd'hui
            }
        }

        // Vérifier si l'enfant a assez de points pour les consommations
        if ($task->getType() === TypeEnum::CONSUMPTION && $child->getPoints() < $task->getPoints()) {
            return false;
        }

        return true;
    }

    private function performTask(Child $child, Task $task): void
    {
        $historic = new Historic();
        $historic->setChild($child);
        $historic->setTask($task);
        $historic->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        
        $this->entityManager->persist($historic);

        // Mettre à jour les points de l'enfant
        $newPoints = $child->getPoints() + $task->getPoints();
        $child->setPoints($newPoints);
        
        $this->entityManager->persist($child);
        $this->entityManager->flush();
    }
}
