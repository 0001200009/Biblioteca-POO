<?php

namespace App\Models;

use App\Exceptions\MaterialIndisponivelException;

class EBook extends Material
{
    private string $formatoArquivo;
    private float $tamanhoArquivo;
    private string $linkAcesso;
    private int $acessosSimultaneos;
    private int $acessosAtivos;

    public function __construct(string $codigo, string $titulo, string $autor, int $anoPublicacao, string $formatoArquivo, float $tamanhoArquivo, string $linkAcesso, int $acessosSimultaneos)
    {
        parent::__construct($codigo, $titulo, $autor, $anoPublicacao, 1);
        $this->formatoArquivo = $formatoArquivo;
        $this->tamanhoArquivo = $tamanhoArquivo;
        $this->linkAcesso = $linkAcesso;
        $this->acessosSimultaneos = $acessosSimultaneos;
        $this->acessosAtivos = 0;
    }

    public function consultarQuantidadeDisponivel(): int
    {
        return $this->acessosSimultaneos - $this->acessosAtivos;
    }

    public function emprestarExemplar(): void
    {
        if ($this->acessosAtivos >= $this->acessosSimultaneos) {
            throw new MaterialIndisponivelException("Limite de acessos simultâneos atingido.");
        }
        $this->acessosAtivos++;
        if ($this->acessosAtivos >= $this->acessosSimultaneos) {
            $this->disponibilidade = false;
        }
    }

    public function devolverExemplar(): void
    {
        if ($this->acessosAtivos > 0) {
            $this->acessosAtivos--;
        }
        $this->disponibilidade = true;
    }

    public function calcularPrazoMaximo(): int
    {
        return 21;
    }
}