# Arquitetura

Neste documento poderão ser encontradas informações referente à arquitetura do projeto, bem como detalhes referente à organização dos diretórios do mesmo.

Índice
- [Arquitetura](#arquitetura)
  - [`.data/`](#data)
  - [`docs/`](#data)
  - [`src/`](#src)
    - [`Container/`](#container)
      - [`Modules/`](#container-modules)
    - [`Library/`](#library)
      - [`Contracts/`](#library-contracts)
        - [`Service/`](#library-contracts-service)
      - [`Persistence/`](#library-persistence)
      - [`Service/`](#library-service)
      - [`Util/`](#library-util)
      - [`ValueObject/`](#library-valueobject)
    - [`Modules/`](#modules)
      - [`DomainModel/`](#modules-domainmodel)
        - [`DTO/`](#modules-dto)
        - [`Exception/`](#modules-exception)
        - [`Model/`](#modules-model)
        - [`Repository/`](#modules-repository)
        - [`UseCase/`](#modules-usecase)
      - [`Persistence/`](#modules-persistence)
  - [`tests/`](#tests)
  - [`vendor/`](#vendor)
        
## `.data/`
Arquivos referente ao banco de dados da aplicação.
Atualmente a aplicação utiliza um banco de dados MySQL.
Maiores informações sobre a estrutura do banco de dados da aplicação podem ser encontradas [aqui](DATABASE.md).

## `docs/`
Documentação da API desenvolvida utilizando o Swagger.

## `src/`
Ponto de entrada para o código da aplicação.

### `Container/`
A aplicação utiliza o padrão de Container de Injeção de Dependência (Dependency Injection Container), centralizando a injeção das dependências da aplicação visando facilitar a remoção ou inclusão de dependências nas classes.

Neste diretório estão o BuilderContainer o qual é responsável por inicializar as dependências espeficadas em cada módulo.

Nele também está contido o LibraryContainer cuja função é relacionar as dependências das bibliotecas da aplicação.

#### `Container/Modules/`
Contém o container de cada módulo da aplicação, os quais são responsável por organizar suas respectivas dependências de Casos de Uso (Use Cases) e Repositórios (Repositories).

### `Library/`
Armazena as classes de bibliotecas da aplicação.

#### `Library/Contracts/`
Interfaces utilizadas na aplicação. Possui desde interfaces mais simples, como a Arrayble responsável pela assinatura do método "toArray()", como interfaces de Serviços.

##### `Library/Contracts/Persistence/`
Interfaces relacionadas à camada de persistência.

##### `Library/Contracts/Service/`
Interfaces de serviços, como, por exemplo, NotificationServiceInterface, responsável pela assinatura do método "sendNotification()".

#### `Library/Persistence/`
Classes que implementam as interfaces da camada de persistência, além da classe de Exception desta camada.

#### `Library/Service/`
Classes que implementam as interfaces de serviço.

#### `Library/Util/`
Classes que implementam as interfaces que possuem caráter a auxiliar em tarefas rotineiras.

#### `Library/ValueObject/`
Diretório destinado a armazenar os objetos identificados como ValueObjects dentro do contexto da aplicação.

### `Modules/`
Ponto de entrada dos módulos da aplicação. A separação em módulo visa trazer uma maior separação de acordo com os contextos da aplicação.

A partir deste ponto é criado um diretório o qual deve representar o nome do módulo em questão e a partir de então os diretórios seguirão o padrão descrito a seguir.

#### `Modules/DomainModel/`
Camada de domínio da aplicação. É nesta camada que deverão constar os códigos que possuem os elementos diretamente ligados ao negócio que a aplicação visa atender.

##### `Modules/DTO/`
A camada de DTO (Data Transfer Object) possui as classes responsáveis pela transferência de dados entre as camadas da aplicação.

##### `Modules/Exception/`
Exceptions relacionadas ao módulo.

##### `Modules/Model/`
Destinada a conter as classes que representam os Modelos (Models) do módulo. Os modelos podem ser entendidos de maneira análoga à tabela do banco de dados, porém sua implementação não representa necessariamente a estrutura de uma tabela.

##### `Modules/Repository/`
Contém as interfaces dos Repositórios do módulo. Os Repositórios estão na camada mais próxima dos dados da aplicação. É nesta camada que estão os métodos "find" e "persist", por exemplo.

##### `Modules/UseCase/`
Nesta camada estão as classes que representam os Casos de Uso da aplicação. Neste tipo de padrão os Casos de Uso tem suas responsabilidades muito bem definidas. A ideia é que ao ler o nome de uma classe que implementa um Caso de Uso, seja possível identificar o que ela está destinada a fazer, como por exemplo:

- "TransactionStart" -> Responsável por ininiciar uma transação;
- "TransactionAuthorize" -> Responsável por autorizar uma transação;

#### `Modules/Persistence/`
Camada de Persistência da aplicação. Possui as classes que implementam as interfaces dos Repositórios. Essa camada pode conter classes que implementam a persistência através de algum framework, por exemplo.

### `tests/`
Diretório que contém a estutura de testes da aplicação.

Atualmente os testes unitários estão sendo realizados com o PHPUnit.

#### Como implementar um teste?
A partir do diretório "UnitTest" os diretrórios seguem o padrão contido no diretório "src".

O nome do arquivo de um novo teste unitário deve possuir o prefixo "Ut", seguido do nome da classe, terminando com o sufixo "Test", padrão do PHPUnit.

#### Como rodar os testes?
Os testes podem ser executados de forma automática através do shell script "run-tests.sh" localizado na raiz do projeto.

### `vendor/`
Diretório com as bibliotecas externas utilizadas na aplicação. Essas bibliotecas estão definidas no arquivo composer.json.