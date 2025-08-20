<?php

namespace App\Validator;

use App\Entity\Child;
use App\Entity\Task;
use App\Enum\TypeEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TaskValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Valide les données d'une tâche
     */
    public function validateTaskData(array $data): array
    {
        $constraints = new Assert\Collection([
            'task' => [
                new Assert\NotBlank(['message' => 'L\'identifiant de la tâche est requis']),
                new Assert\Type(['type' => 'integer', 'message' => 'L\'identifiant de la tâche doit être un nombre']),
                new Assert\Positive(['message' => 'L\'identifiant de la tâche doit être positif'])
            ]
        ]);

        $violations = $this->validator->validate($data, $constraints);
        
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;
    }

    /**
     * Valide qu'un enfant peut effectuer une tâche
     */
    public function validateChildCanPerformTask(Child $child, Task $task): array
    {
        $errors = [];

        // Vérifier que l'enfant existe
        if (!$child) {
            $errors[] = 'Enfant non trouvé';
        }

        // Vérifier que la tâche existe
        if (!$task) {
            $errors[] = 'Tâche non trouvée';
        }

        // Vérifier que l'enfant a assez de points pour les consommations
        if ($task && $task->getType() === TypeEnum::CONSUMPTION) {
            if ($child && $child->getPoints() < $task->getPoints()) {
                $errors[] = sprintf(
                    'L\'enfant n\'a pas assez de points. Points actuels: %d, Points requis: %d',
                    $child->getPoints(),
                    $task->getPoints()
                );
            }
        }

        return $errors;
    }

    /**
     * Valide les paramètres de création d'une tâche
     */
    public function validateTaskCreation(array $data): array
    {
        $constraints = new Assert\Collection([
            'name' => [
                new Assert\NotBlank(['message' => 'Le nom de la tâche est requis']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => 'Le nom de la tâche doit contenir au moins {{ limit }} caractères',
                    'maxMessage' => 'Le nom de la tâche ne peut pas dépasser {{ limit }} caractères'
                ])
            ],
            'points' => [
                new Assert\NotBlank(['message' => 'Les points sont requis']),
                new Assert\Type(['type' => 'integer', 'message' => 'Les points doivent être un nombre']),
                new Assert\NotEqualTo(['value' => 0, 'message' => 'Les points ne peuvent pas être égaux à 0'])
            ],
            'type' => [
                new Assert\NotBlank(['message' => 'Le type de tâche est requis']),
                new Assert\Choice([
                    'choices' => [TypeEnum::BONUS, TypeEnum::PENALTY, TypeEnum::CONSUMPTION],
                    'message' => 'Le type de tâche doit être valide'
                ])
            ],
            'frequency' => [
                new Assert\NotBlank(['message' => 'La fréquence est requise']),
                new Assert\Type(['type' => 'integer', 'message' => 'La fréquence doit être un nombre']),
                new Assert\Range([
                    'min' => 0,
                    'max' => 1,
                    'minMessage' => 'La fréquence doit être au moins {{ limit }}',
                    'maxMessage' => 'La fréquence ne peut pas dépasser {{ limit }}'
                ])
            ]
        ]);

        $violations = $this->validator->validate($data, $constraints);
        
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;
    }

    /**
     * Valide les paramètres de création d'un enfant
     */
    public function validateChildCreation(array $data): array
    {
        $constraints = new Assert\Collection([
            'firstname' => [
                new Assert\NotBlank(['message' => 'Le prénom est requis']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                    'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères'
                ])
            ],
            'email' => [
                new Assert\NotBlank(['message' => 'L\'email est requis']),
                new Assert\Email(['message' => 'L\'email n\'est pas valide'])
            ],
            'points' => [
                new Assert\NotBlank(['message' => 'Les points initiaux sont requis']),
                new Assert\Type(['type' => 'integer', 'message' => 'Les points doivent être un nombre']),
                new Assert\GreaterThanOrEqual(['value' => 0, 'message' => 'Les points initiaux ne peuvent pas être négatifs'])
            ],
            'gender' => [
                new Assert\NotBlank(['message' => 'Le genre est requis']),
                new Assert\Type(['type' => 'integer', 'message' => 'Le genre doit être un nombre']),
                new Assert\Range([
                    'min' => 0,
                    'max' => 1,
                    'minMessage' => 'Le genre doit être au moins {{ limit }}',
                    'maxMessage' => 'Le genre ne peut pas dépasser {{ limit }}'
                ])
            ]
        ]);

        $violations = $this->validator->validate($data, $constraints);
        
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;
    }
}
