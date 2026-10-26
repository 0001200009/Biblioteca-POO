<?php

namespace App\Multas;

use App\Interfaces\MultaInterface;

class MultaPadrao implements MultaInterface
{
    public function calcularMulta(int $diasAtraso): float
    {
        return $diasAtraso * 2.0;
    }
}