<?php

namespace App\Enums;

enum Genero: string
{
    case MASCULINO = 'MASCULINO';
    case FEMININO = 'FEMININO';
    case OUTRO = 'OUTRO';

    public function getLabel(): string
    {
        return match ($this) {
            self::MASCULINO => 'Masculino',
            self::FEMININO => 'Feminino',
            self::OUTRO => 'Outro',
        };
    }
}
