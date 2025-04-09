<?php

namespace App\Enums;

enum StatusParticipacaoProjeto: string
{
  case ACEITO = 'ACEITO';
  case REJEITADO = 'REJEITADO';
  case PENDENTE = 'PENDENTE';
}
