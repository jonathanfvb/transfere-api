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

Ao acessar a URL acima deverá ser exibido o seguinte json

```javascript
{
	"success": true,
	"message": "Ok"
}
```

### Como executar uma transação?
Abaixo estão descritos os passos para executar uma transação. Para maiores detalhes sobre as rotas e o payload acesse a documentação no [Swagger](http://localhost:8080/docs/).

Para criar uma transação é necessário:
- Ter o uuid dos dois usuários envolvidos;
- Iniciar a transação;
- Autorizar a transação;
- Enviar a notificação da transação autorizada;

Etapas:
- Crie um usuário comum e salve o uuid retornado;
- Adicione um valor à carteira deste usuáio;
- Crie um segundo usuário, pode ser do tipo comum ou lojista, e salve o uuid retornado;
- Inicie a transação e salve o uuid retornado;
- Autorize esta transação;
- Envie a notificação desta transação;
- Fim;


## Acessando a documentação
[Swagger API Doc](http://localhost:8080/docs/)

## Rodando Testes Unitários
Para executar os testes unitários execute o comando abaixo:

```
./run-tests.sh
```
