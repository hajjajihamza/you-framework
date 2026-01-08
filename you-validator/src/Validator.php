<?php

namespace YouValidator;

/**
 * Classe responsable de la validation des données.
 * Cette classe permet de définir des règles de validation et de vérifier les valeurs fournies.
 */
class Validator
{
    /**
     * @var array Liste des règles de validation enregistrées.
     */
    private array $rules = [];

    /**
     * Ajoute une règle de validation.
     *
     * @param callable $rule La fonction de rappel qui définit la logique de validation. Elle doit retourner true si valide.
     * @param string $errorMessage Le message d'erreur à associer si la validation échoue.
     * @return void
     */
    public function addRule(callable $rule, string $errorMessage): void
    {
        $this->rules[] = ['rule' => $rule, 'message' => $errorMessage];
    }

    /**
     * Valide une valeur donnée en fonction des règles définies.
     *
     * Si la validation échoue, les erreurs sont stockées dans la session et les anciennes entrées sont conservées.
     *
     * @param string $fieldName Le nom du champ à valider (utilisé pour les messages d'erreur et la session).
     * @param mixed $value La valeur à valider.
     * @return bool Retourne true si toutes les règles sont respectées, sinon false.
     */
    public function validate(string $fieldName, mixed $value): bool
    {
        // reset errors
        $errors = [];

        // validate the value
        foreach ($this->rules as $rule) {
            if (!$rule['rule']($value)) {
                $errors[] = str_replace(':field', $fieldName, $rule['message']);
            }
        }

        // enregistrer les erreurs et les anciennes entrées
        if (!empty($errors)) {
            // merge the new errors with existing errors
            $existingErrors = errors() ?: [];
            $existingErrors[$fieldName] = $errors;
            with_errors($existingErrors);

            // save the old input
            $oldInput = session('_flash')['_old_input'] ?? [];
            $oldInput[$fieldName] = $value;
            with_old($oldInput);
        }

        return empty($errors);
    }
}