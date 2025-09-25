<?php

namespace App\Enums;

use Carbon\Carbon;

enum DiaDaSemana: string
{
    case DOMINGO = 'DOMINGO';
    case SEGUNDA = 'SEGUNDA';
    case TERCA = 'TERCA';
    case QUARTA = 'QUARTA';
    case QUINTA = 'QUINTA';
    case SEXTA = 'SEXTA';
    case SABADO = 'SABADO';

    public static function fromCarbon(Carbon $data): self
    {
        return match ($data->dayOfWeek) {
            Carbon::SUNDAY => self::DOMINGO,
            Carbon::MONDAY => self::SEGUNDA,
            Carbon::TUESDAY => self::TERCA,
            Carbon::WEDNESDAY => self::QUARTA,
            Carbon::THURSDAY => self::QUINTA,
            Carbon::FRIDAY => self::SEXTA,
            Carbon::SATURDAY => self::SABADO,
        };
    }
}
