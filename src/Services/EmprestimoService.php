<?php

namespace App\Services;

use App\Models\Emprestimo;
use App\Models\Usuario;
use App\Models\Material;
use App\Models\Professor;
use App\Models\Historico;
use App\Interfaces\MultaInterface;
use App\Exceptions\MaterialIndisponivelException;
use App\Exceptions\LimiteEmprestimoException;
use App\Exceptions\UsuarioBloqueadoException;
use App\Exceptions\EmprestimoInvalidoException;
use DateTime;

class EmprestimoService
{
    private array $historicoLogs = [];

    public function criarEmprestimo(Usuario $usuario, Material $material): Emprestimo
    {
        if ($usuario->getStatus() !== 'ATIVO') {
            throw new UsuarioBloqueadoException("Usuário bloqueado não pode realizar empréstimos.");
        }

        if (!$usuario->verificarSePodeRealizarNovoEmprestimo()) {
            throw new LimiteEmprestimoException("Usuário atingiu o limite máximo de empréstimos.");
        }

        $material->emprestarExemplar();

        $diasPrazo = $material->calcularPrazoMaximo();
        if ($usuario instanceof Professor) {
            $diasPrazo += 7;
        }

        $dataAtual = new DateTime();
        $dataPrevista = (clone $dataAtual)->modify("+{$diasPrazo} days");
        
        $codigoEmprestimo = uniqid('EMP_');
        $emprestimo = new Emprestimo($codigoEmprestimo, $usuario, $material, $dataAtual, $dataPrevista);
        
        $usuario->incrementarEmprestimos();

        $this->registrarHistorico("Empréstimo realizado", $usuario->getNome(), $material->getTitulo());

        return $emprestimo;
    }

    public function registrarDevolucao(Emprestimo $emprestimo, DateTime $dataDevolucao, MultaInterface $estrategiaMulta): array
    {
        if ($emprestimo->getStatus() === 'DEVOLVIDO') {
            throw new EmprestimoInvalidoException("Este empréstimo já foi devolvido.");
        }

        if ($emprestimo->getStatus() === 'CANCELADO') {
            throw new EmprestimoInvalidoException("Empréstimo cancelado não pode ser devolvido.");
        }

        $emprestimo->setDataRealDevolucao($dataDevolucao);
        $emprestimo->getMaterial()->devolverExemplar();
        $emprestimo->getUsuario()->decrementarEmprestimos();

        $atraso = 0;
        $multa = 0.0;

        if ($dataDevolucao > $emprestimo->getDataPrevistaDevolucao()) {
            $diferenca = $dataDevolucao->diff($emprestimo->getDataPrevistaDevolucao());
            $atraso = $diferenca->days;
            $multa = $estrategiaMulta->calcularMulta($atraso);
            $emprestimo->setStatus('DEVOLVIDO');
            $this->registrarHistorico("Devolução com atraso", $emprestimo->getUsuario()->getNome(), $emprestimo->getMaterial()->getTitulo(), "Atraso de {$atraso} dias. Multa: R$ {$multa}");
        } else {
            $emprestimo->setStatus('DEVOLVIDO');
            $this->registrarHistorico("Devolução realizada", $emprestimo->getUsuario()->getNome(), $emprestimo->getMaterial()->getTitulo());
        }

        return [
            'atraso' => $atraso,
            'multa' => $multa
        ];
    }

    private function registrarHistorico(string $acao, string $usuario, string $material, string $observacao = ""): void
    {
        $this->historicoLogs[] = clone new Historico($acao, $usuario, $material, $observacao);
    }

    public function getHistorico(): array
    {
        return $this->historicoLogs;
    }
}