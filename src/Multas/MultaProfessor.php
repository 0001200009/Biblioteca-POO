<?php

namespace App\Multas;

use App\Interfaces\MultaInterface;

class MultaProfessor implements MultaInterface
{
    public function calcularMulta(int $diasAtraso): float
    {
        return $diasAtraso * 1.0;
    }
}