<?php

namespace App\Enums;

enum StatusSolicitacaoTrocaProjeto: string
{
  case ACEITO = 'ACEITO';
  case REJEITADO = 'REJEITADO';
  case PENDENTE = 'PENDENTE';
}
