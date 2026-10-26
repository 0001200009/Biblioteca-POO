<?php

namespace App\Models;

abstract class Usuario
{
    protected string $matricula;
    protected string $nome;
    protected string $email;
    protected string $telefone;
    protected string $status;
    protected int $quantidadeMaximaEmprestimos;
    protected int $emprestimosAtivos;

    public function __construct(string $matricula, string $nome, string $email, string $telefone)
    {
        $this->matricula = $matricula;
        $this->nome = $nome;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->status = 'ATIVO';
        $this->emprestimosAtivos = 0;
    }

    public function getMatricula(): string
    {
        return $this->matricula;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function bloquearUsuario(): void
    {
        $this->status = 'BLOQUEADO';
    }

    public function consultarDados(): string
    {
        return "{$this->nome} ({$this->matricula}) - Limite: {$this->quantidadeMaximaEmprestimos}";
    }

    public function verificarSePodeRealizarNovoEmprestimo(): bool
    {
        return $this->emprestimosAtivos < $this->quantidadeMaximaEmprestimos;
    }

    public function consultarLimiteEmprestimos(): int
    {
        return $this->quantidadeMaximaEmprestimos;
    }

    public function consultarQuantidadeEmprestimosAtivos(): int
    {
        return $this->emprestimosAtivos;
    }

    public function incrementarEmprestimos(): void
    {
        $this->emprestimosAtivos++;
    }

    public function decrementarEmprestimos(): void
    {
        if ($this->emprestimosAtivos > 0) {
            $this->emprestimosAtivos--;
        }
    }
}