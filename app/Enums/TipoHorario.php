<?php

namespace App\Enums;

enum TipoHorario: string
{
  case EM_AULA = 'EM_AULA';
  case TRABALHO_PRESENCIAL = 'TRABALHO_PRESENCIAL';
  case TRABALHO_REMOTO = 'TRABALHO_REMOTO';
  case AUSENTE = 'AUSENTE';
}
