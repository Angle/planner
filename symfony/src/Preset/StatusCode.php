<?php

namespace App\Preset;

class StatusCode
{
    const LABEL_SUCCESS = 'success';
    const LABEL_ERROR = 'error';

    const SUCCESS_OK = 100;
    const SUCCESS_NOTEBOOK_SHARE = 101;

    const ERROR_ENTITY_NOT_FOUND = 200;
    const ERROR_DATABASE_INSERT = 201;
    const ERROR_NOTEBOOK_SHARE = 202;
    const ERROR_OLD_PASSWORD_NO_MATCH = 203;
    const ERROR_PASSWORD_NO_MATCH = 204;


    /** @var string $label */
    private $label;

    /** @var int $code */
    private $code;

    public function __construct(string $label, int $code)
    {
        $this->label = $label;
        $this->code = $code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}