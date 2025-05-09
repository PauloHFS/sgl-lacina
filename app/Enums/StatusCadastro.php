<?php

namespace App\Enums;

enum StatusCadastro: string
{
  case IMCOMPLETO = 'IMCOMPLETO';
  case PENDENTE = 'PENDENTE';
  case ACEITO = 'ACEITO';
  case RECUSADO = 'RECUSADO';
}
