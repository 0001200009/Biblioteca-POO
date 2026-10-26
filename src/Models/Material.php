<?php

namespace App\Models;

use App\Exceptions\MaterialIndisponivelException;

abstract class Material
{
    protected string $codigo;
    protected string $titulo;
    protected string $autor;
    protected int $anoPublicacao;
    protected bool $disponibilidade;
    protected int $quantidadeExemplares;

    public function __construct(string $codigo, string $titulo, string $autor, int $anoPublicacao, int $quantidadeExemplares)
    {
        $this->codigo = $codigo;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->anoPublicacao = $anoPublicacao;
        $this->quantidadeExemplares = $quantidadeExemplares;
        $this->disponibilidade = $quantidadeExemplares > 0;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function consultarDisponibilidade(): bool
    {
        return $this->disponibilidade;
    }

    public function consultarQuantidadeDisponivel(): int
    {
        return $this->quantidadeExemplares;
    }

    public function emprestarExemplar(): void
    {
        if ($this->quantidadeExemplares <= 0) {
            throw new MaterialIndisponivelException("Material indisponível para empréstimo.");
        }
        $this->quantidadeExemplares--;
        if ($this->quantidadeExemplares === 0) {
            $this->disponibilidade = false;
        }
    }

    public function devolverExemplar(): void
    {
        $this->quantidadeExemplares++;
        $this->disponibilidade = true;
    }

    public function exibirInformacoes(): string
    {
        return "{$this->titulo} - {$this->autor} ({$this->anoPublicacao})";
    }

    abstract public function calcularPrazoMaximo(): int;
}