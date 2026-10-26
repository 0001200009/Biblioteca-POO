<?php

namespace App\Models;

use DateTime;

class Emprestimo
{
    private string $codigo;
    private Usuario $usuario;
    private Material $material;
    private DateTime $dataEmprestimo;
    private DateTime $dataPrevistaDevolucao;
    private ?DateTime $dataRealDevolucao;
    private string $status;

    public function __construct(string $codigo, Usuario $usuario, Material $material, DateTime $dataEmprestimo, DateTime $dataPrevistaDevolucao)
    {
        $this->codigo = $codigo;
        $this->usuario = $usuario;
        $this->material = $material;
        $this->dataEmprestimo = $dataEmprestimo;
        $this->dataPrevistaDevolucao = $dataPrevistaDevolucao;
        $this->dataRealDevolucao = null;
        $this->status = 'ATIVO';
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function getMaterial(): Material
    {
        return $this->material;
    }

    public function getDataEmprestimo(): DateTime
    {
        return $this->dataEmprestimo;
    }

    public function getDataPrevistaDevolucao(): DateTime
    {
        return $this->dataPrevistaDevolucao;
    }

    public function getDataRealDevolucao(): ?DateTime
    {
        return $this->dataRealDevolucao;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setDataRealDevolucao(DateTime $data): void
    {
        $this->dataRealDevolucao = $data;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}