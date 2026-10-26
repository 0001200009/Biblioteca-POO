# Sistema de Gestão de Biblioteca e Empréstimos

## Descrição
Sistema de gestão bibliotecária desenvolvido em PHP focado na implementação rigorosa dos conceitos de Programação Orientada a Objetos (POO).

## Requisitos
- PHP 7.4 ou superior.
- CLI para execução.

## Estrutura de Diretórios

biblioteca-poo/
├── src/
│   ├── Models/
│   │   ├── Material.php
│   │   ├── Livro.php
│   │   ├── Revista.php
│   │   ├── EBook.php
│   │   ├── Usuario.php
│   │   ├── Aluno.php
│   │   ├── Professor.php
│   │   ├── Emprestimo.php
│   │   └── Historico.php
│   ├── Interfaces/
│   │   └── MultaInterface.php
│   ├── Multas/
│   │   ├── MultaPadrao.php
│   │   └── MultaProfessor.php
│   ├── Services/
│   │   └── EmprestimoService.php
│   └── Exceptions/
│       ├── MaterialIndisponivelException.php
│       ├── LimiteEmprestimoException.php
│       ├── UsuarioBloqueadoException.php
│       └── EmprestimoInvalidoException.php
├── index.php
└── README.md

## Aplicação dos Conceitos de POO

*   **Encapsulamento**: Todos os atributos das classes (`Material`, `Usuario`, `Emprestimo`) estão definidos como `private` ou `protected`, com o acesso restrito por métodos getters e setters lógicos. A atualização de estoque, limites e status ocorre apenas via métodos da própria classe (ex: `emprestarExemplar()`).
*   **Herança**: As classes `Livro`, `Revista` e `EBook` estendem `Material`, aproveitando os atributos e métodos genéricos. O mesmo acontece com `Aluno` e `Professor` em relação à classe `Usuario`.
*   **Abstração**: `Material` e `Usuario` são classes abstratas. O método `calcularPrazoMaximo()` em `Material` força cada subclasse concreta a definir sua própria regra de prazo de devolução.
*   **Polimorfismo**: Ocorre de duas formas. Polimorfismo de Inclusão nas classes filhas de `Material` ao sobrescreverem `emprestarExemplar` (como no `EBook` mudando a regra de estoque físico para acesso virtual) e Polimorfismo através da injeção de dependência usando `MultaInterface` no `EmprestimoService`, permitindo alternar a lógica de cálculo de multa sem alterar o serviço.
*   **Composição**: A classe `Emprestimo` contém instâncias inteiras de objetos das classes `Usuario` e `Material`.
*   **Interfaces**: A `MultaInterface` determina um contrato padronizado que as classes `MultaPadrao` e `MultaProfessor` são obrigadas a seguir, gerando coesão.

## Instruções de Execução
No terminal raiz do projeto, execute o comando:
`php index.php`

## Testes Realizados
Foram implementados 8 cenários de testes no arquivo `index.php` abrangendo:
1. Validação de estoque vazio.
2. Limite máximo de empréstimos ativos para tipos de usuário.
3. Restrição de usuário bloqueado.
4. Tentativa de devolução duplicada.
5. Validação do limite especial de acessos simultâneos de um EBook.
6. Devolução dentro do prazo gerando isenção de multa.
7. Devolução com dias de atraso controlados pelo DateTime.
8. Execução correta da interface de cálculo para valores de Professor vs Aluno.

## Diagrama UML

```mermaid
classDiagram
    class Material {
        <<abstract>>
        #codigo: string
        #titulo: string
        #autor: string
        #anoPublicacao: int
        #disponibilidade: bool
        #quantidadeExemplares: int
        +consultarDisponibilidade(): bool
        +emprestarExemplar(): void
        +devolverExemplar(): void
        +calcularPrazoMaximo(): int*
    }

    class Livro {
        -isbn: string
        -numeroPaginas: int
        -editora: string
        -genero: string
        +calcularPrazoMaximo(): int
    }

    class Revista {
        -edicao: int
        -volume: int
        -mesPublicacao: string
        +calcularPrazoMaximo(): int
    }

    class EBook {
        -formatoArquivo: string
        -tamanhoArquivo: float
        -linkAcesso: string
        -acessosSimultaneos: int
        -acessosAtivos: int
        +emprestarExemplar(): void
        +devolverExemplar(): void
        +calcularPrazoMaximo(): int
    }

    Material <|-- Livro
    Material <|-- Revista
    Material <|-- EBook

    class Usuario {
        <<abstract>>
        #matricula: string
        #nome: string
        #email: string
        #telefone: string
        #status: string
        #quantidadeMaximaEmprestimos: int
        #emprestimosAtivos: int
        +verificarSePodeRealizarNovoEmprestimo(): bool
        +incrementarEmprestimos(): void
        +decrementarEmprestimos(): void
    }

    class Aluno {
    }

    class Professor {
    }

    Usuario <|-- Aluno
    Usuario <|-- Professor

    class Emprestimo {
        -codigo: string
        -dataEmprestimo: DateTime
        -dataPrevistaDevolucao: DateTime
        -dataRealDevolucao: DateTime
        -status: string
    }

    Emprestimo o-- Usuario
    Emprestimo o-- Material

    class EmprestimoService {
        +criarEmprestimo(Usuario, Material): Emprestimo
        +registrarDevolucao(Emprestimo, DateTime, MultaInterface): array
    }

    class Historico {
        -data: string
        -hora: string
        -acao: string
        -usuarioNome: string
        -materialTitulo: string
        -observacao: string
    }

    class MultaInterface {
        <<interface>>
        +calcularMulta(int diasAtraso): float
    }

    class MultaPadrao {
        +calcularMulta(int diasAtraso): float
    }

    class MultaProfessor {
        +calcularMulta(int diasAtraso): float
    }

    MultaInterface <|.. MultaPadrao
    MultaInterface <|.. MultaProfessor