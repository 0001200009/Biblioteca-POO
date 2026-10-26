<?php

namespace App\Models;

class Aluno extends Usuario
{
    public function __construct(string $matricula, string $nome, string $email, string $telefone)
    {
        parent::__construct($matricula, $nome, $email, $telefone);
        $this->quantidadeMaximaEmprestimos = 3;
    }
}