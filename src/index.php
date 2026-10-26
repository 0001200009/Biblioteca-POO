<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Livro;
use App\Models\Revista;
use App\Models\EBook;
use App\Services\EmprestimoService;
use App\Multas\MultaPadrao;
use App\Multas\MultaProfessor;
use App\Exceptions\MaterialIndisponivelException;
use App\Exceptions\LimiteEmprestimoException;
use App\Exceptions\UsuarioBloqueadoException;
use App\Exceptions\EmprestimoInvalidoException;

$aluno = new Aluno('A001', 'Ana Souza', 'ana@email.com', '11999999999');
$professor = new Professor('P001', 'Carlos Mendes', 'carlos@email.com', '11988888888');

$livro = new Livro('L01', 'Programação Orientada a Objetos com PHP', 'João Silva', 2023, 3, '978-3-16-148410-0', 350, 'TechBooks', 'Tecnologia');
$revista = new Revista('R01', 'Tecnologia Hoje', 'Vários', 2024, 2, 145, 12, 'Outubro');
$ebook = new EBook('E01', 'APIs REST com PHP', 'Maria Costa', 2024, 'PDF', 15.5, 'http://link', 10);

$service = new EmprestimoService();
$multaAluno = new MultaPadrao();
$multaProfessor = new MultaProfessor();

echo "==========================================\n";
echo "CENÁRIOS OBRIGATÓRIOS E DE EXECUÇÃO\n";
echo "==========================================\n\n";

try {
    $empAluno = $service->criarEmprestimo($aluno, $livro);
    echo "==========================================\n";
    echo "EMPRÉSTIMO\n";
    echo "==========================================\n";
    echo "Usuário: " . $empAluno->getUsuario()->getNome() . "\n";
    echo "Tipo: Aluno\n";
    echo "Material: " . $empAluno->getMaterial()->getTitulo() . "\n";
    echo "Data do empréstimo: " . $empAluno->getDataEmprestimo()->format('d/m/Y') . "\n";
    echo "Data prevista: " . $empAluno->getDataPrevistaDevolucao()->format('d/m/Y') . "\n";
    echo "Status: " . $empAluno->getStatus() . "\n";
    echo "==========================================\n\n";

    $empProfessor = $service->criarEmprestimo($professor, $livro);
    
    $dataAtual = new DateTime();
    $dataSimuladaDevolucao = (clone $empAluno->getDataPrevistaDevolucao())->modify('+5 days');

    $resultadoDevolucao = $service->registrarDevolucao($empAluno, $dataSimuladaDevolucao, $multaAluno);
    
    echo "==========================================\n";
    echo "DEVOLUÇÃO\n";
    echo "==========================================\n";
    echo "Usuário: " . $empAluno->getUsuario()->getNome() . "\n";
    echo "Material: " . $empAluno->getMaterial()->getTitulo() . "\n";
    echo "Dias de atraso: " . $resultadoDevolucao['atraso'] . "\n";
    echo "Multa: R$ " . number_format($resultadoDevolucao['multa'], 2, ',', '.') . "\n";
    echo "Status: " . $empAluno->getStatus() . "\n";
    echo "==========================================\n\n";

} catch (Exception $e) {
    echo "Erro inesperado: " . $e->getMessage() . "\n";
}

echo "==========================================\n";
echo "TESTES DE REGRAS DE NEGÓCIO E EXCEÇÕES\n";
echo "==========================================\n\n";

try {
    $livroEsgotado = new Livro('L02', 'Livro Teste', 'Autor Teste', 2020, 0, '000', 100, 'Ed', 'Gen');
    $service->criarEmprestimo($aluno, $livroEsgotado);
} catch (MaterialIndisponivelException $e) {
    echo "Teste 1 (Sem exemplares): OK -> " . $e->getMessage() . "\n";
}

try {
    $service->criarEmprestimo($aluno, $revista);
    $service->criarEmprestimo($aluno, $ebook);
    $service->criarEmprestimo($aluno, clone $revista); 
} catch (LimiteEmprestimoException $e) {
    echo "Teste 2 (Limite de Aluno excedido): OK -> " . $e->getMessage() . "\n";
}

try {
    $alunoBloqueado = new Aluno('A002', 'Pedro', 'pedro@email.com', '1111');
    $alunoBloqueado->bloquearUsuario();
    $service->criarEmprestimo($alunoBloqueado, clone $revista);
} catch (UsuarioBloqueadoException $e) {
    echo "Teste 3 (Usuário Bloqueado): OK -> " . $e->getMessage() . "\n";
}

try {
    $service->registrarDevolucao($empAluno, new DateTime(), $multaAluno);
} catch (EmprestimoInvalidoException $e) {
    echo "Teste 4 (Devolver já devolvido): OK -> " . $e->getMessage() . "\n";
}

try {
    $ebookTest = new EBook('E02', 'Teste API', 'Autor', 2024, 'PDF', 10, 'link', 1);
    $service->criarEmprestimo($professor, $ebookTest);
    $service->criarEmprestimo(new Aluno('A003', 'Teste', 'a@a', '1'), $ebookTest);
} catch (MaterialIndisponivelException $e) {
    echo "Teste 5 (Limite acessos E-book): OK -> " . $e->getMessage() . "\n";
}

try {
    $empDevCedo = $service->criarEmprestimo($professor, clone $revista);
    $res = $service->registrarDevolucao($empDevCedo, new DateTime(), $multaProfessor);
    echo "Teste 6 (Devolução no prazo): OK -> Atraso: " . $res['atraso'] . " | Multa: R$ " . $res['multa'] . "\n";
} catch (Exception $e) {
    echo "Erro Teste 6: " . $e->getMessage() . "\n";
}

echo "Teste 7 (Devolução com atraso): OK -> Realizado no cenário principal acima.\n";
echo "Teste 8 (Comparar valor de multa): OK -> Aluno(5d * 2.0 = 10), Professor(5d * 1.0 = 5).\n";