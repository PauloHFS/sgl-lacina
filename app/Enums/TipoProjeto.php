<?php

namespace App\Enums;

enum TipoProjeto: string
{
  case PDI = 'PDI';
  case TCC = 'TCC';
  case MESTRADO = 'MESTRADO';
  case DOUTORADO = 'DOUTORADO';
  case SUPORTE = 'SUPORTE';
}
