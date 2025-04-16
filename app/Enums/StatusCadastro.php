<?php

namespace App\Enums;

enum StatusCadastro: string
{
  case ACEITO = 'ACEITO';
  case PENDENTE = 'PENDENTE';
  case RECUSADO = 'RECUSADO';
}
