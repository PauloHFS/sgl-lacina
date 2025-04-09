<?php

namespace App\Enums;

enum TipoHorario: string
{
  case AULA = 'AULA';
  case TRABALHO = 'TRABALHO';
  case AUSENTE = 'AUSENTE';
}
