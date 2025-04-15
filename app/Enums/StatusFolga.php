<?php

namespace App\Enums;

enum StatusFolga: string
{
  case PENDENTE = 'PENDENTE';
  case APROVADO = 'APROVADO';
  case REJEITADO = 'REJEITADO';
}
