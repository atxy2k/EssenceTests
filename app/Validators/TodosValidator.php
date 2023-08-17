<?php namespace App\Validators;

use Atxy2k\Essence\Infrastructure\Validator;

class TodosValidator extends Validator
{
    public const CREATE = 'create';
    public const CHANGE = 'change';
    public const ASSIGN_RESPONSIBLE = 'assign-responsible';

    protected array $rules = [
        self::CREATE => [
            'title' => 'required'
        ],
        self::CHANGE => [
            'title' => 'required'
        ],
        self::ASSIGN_RESPONSIBLE => [
            'responsible_id' => 'required|integer|exists:users,id'
        ]

    ];
}
