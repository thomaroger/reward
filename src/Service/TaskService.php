<?php

namespace App\Service;

use App\Entity\Child;
use App\Entity\Task;
use App\Entity\Historic;
use App\Enum\TypeEnum;
use App\Repository\HistoricRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HistoricRepository $historicRepository
    ) {}

    /**
     * Vérifie si une tâche peut être effectuée par un enfant
     */
    public function canPerformTask(Child $child, Task $task): bool
    {
        // Vérifier la fréquence de la tâche
        if ($task->getFrequency() === 1) { // Quotidienne
            $today = new \DateTimeImmutable('today', new \DateTimeZone('Europe/Paris'));
            $existingTask = $this->historicRepository->findOneBy([
                'child' => $child, 
                'task' => $task, 
                'created_at' => $today
            ]);
            
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

    /**
     * Exécute une tâche pour un enfant
     */
    public function performTask(Child $child, Task $task): void
    {
        if (!$this->canPerformTask($child, $task)) {
            throw new BadRequestHttpException('Cette tâche ne peut pas être effectuée maintenant');
        }

        try {
            $this->entityManager->beginTransaction();

            // Créer l'historique
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
            $this->entityManager->commit();

            error_log(sprintf(
                'Tâche "%s" effectuée pour %s. Points: %+d, Nouveau total: %d',
                $task->getName(),
                $child->getFirstname(),
                $task->getPoints(),
                $newPoints
            ));

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            error_log('Erreur lors de l\'exécution de la tâche: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère le résumé des points d'un enfant
     */
    public function getChildPointsSummary(Child $child): array
    {
        $historics = $this->historicRepository->findBy(['child' => $child], ['created_at' => 'DESC']);
        
        $summary = [
            'total' => $child->getPoints(),
            'bonus' => 0,
            'penalties' => 0,
            'consumptions' => 0,
            'recent_activities' => []
        ];

        foreach ($historics as $historic) {
            $task = $historic->getTask();
            $points = $task->getPoints();
            
            switch ($task->getType()) {
                case TypeEnum::BONUS:
                    $summary['bonus'] += $points;
                    break;
                case TypeEnum::PENALTY:
                    $summary['penalties'] += abs($points);
                    break;
                case TypeEnum::CONSUMPTION:
                    $summary['consumptions'] += $points;
                    break;
            }

            // Garder seulement les 5 dernières activités
            if (count($summary['recent_activities']) < 5) {
                $summary['recent_activities'][] = [
                    'task' => $task->getName(),
                    'points' => $points,
                    'type' => $task->getType(),
                    'date' => $historic->getCreatedAt()
                ];
            }
        }

        return $summary;
    }

    /**
     * Vérifie si un enfant peut effectuer une tâche de consommation
     */
    public function canConsumePoints(Child $child, int $pointsToConsume): bool
    {
        return $child->getPoints() >= $pointsToConsume;
    }

    /**
     * Récupère les tâches disponibles pour un enfant selon sa situation
     */
    public function getAvailableTasksForChild(Child $child): array
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        $availableTasks = [];

        foreach ($tasks as $task) {
            if ($this->canPerformTask($child, $task)) {
                $availableTasks[] = $task;
            }
        }

        return $availableTasks;
    }
}
