# Transfere API
Api para realizar a transferência de valor entre usuários

## Pré-requisitos
* Docker
* Docker Compose

## Instalando
Para instalar siga os passos abaixo:

### Rodando o container
```
docker-compose build && docker-compose up
```

### Criando o schema do banco de dados
*Executar apenas uma vez*

```
docker exec transfere-api_app_1 php config/db_setup.php
```

## Acessando a aplicação
[http://localhost:8080](http://localhost:8080)

## Acessando a documentação
[Swagger API Doc](http://localhost:8080/docs/)

## Rodando Testes Unitários
Para executar os testes unitários execute o comando abaixo:

```
./run-tests.sh
```
