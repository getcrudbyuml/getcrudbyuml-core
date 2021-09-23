define({ "api": [
  {
    "version": "0.0.1",
    "type": "delete",
    "url": "/logout",
    "title": "Logout",
    "group": "Autenticação",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "status",
            "description": "<p>Mensagem de saída do sistema</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Sucesso",
          "content": "HTTP/1.1 200 OK\n{\n  \"status\": \"Você saiu do sistema!\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./myApi.js",
    "groupTitle": "Autenticação",
    "name": "DeleteLogout"
  },
  {
    "type": "get",
    "url": "/signin",
    "title": "Singin",
    "version": "0.0.1",
    "group": "Autenticação",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "status",
            "description": "<p>Mensagem de acesso autorizado</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Sucesso",
          "content": "HTTP/1.1 200 OK\n{\n  \"status\": \"Logado!\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./myApi.js",
    "groupTitle": "Autenticação",
    "name": "GetSignin"
  },
  {
    "version": "0.0.1",
    "type": "post",
    "url": "/signup",
    "title": "Signup",
    "group": "Autenticação",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "status",
            "description": "<p>Mensagem de cadastro efetuado</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Sucesso",
          "content": "HTTP/1.1 200 OK\n{\n  \"status\": \"Cadastrado!\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./myApi.js",
    "groupTitle": "Autenticação",
    "name": "PostSignup"
  },
  {
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "optional": false,
            "field": "varname1",
            "description": "<p>No type.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "varname2",
            "description": "<p>With type.</p>"
          }
        ]
      }
    },
    "type": "",
    "url": "",
    "version": "0.0.0",
    "filename": "./doc/main.js",
    "group": "D:\\web\\getcrudbyuml\\getcrudbyuml-core\\src\\doc\\main.js",
    "groupTitle": "D:\\web\\getcrudbyuml\\getcrudbyuml-core\\src\\doc\\main.js",
    "name": ""
  },
  {
    "version": "0.0.1",
    "type": "put",
    "url": "/pagamento/:codigo",
    "title": "Pagamento com código de barras",
    "group": "Pagamentos",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "status",
            "description": "<p>Mensagem de dados atualizados</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Sucesso",
          "content": "HTTP/1.1 200 OK\n{\n  \"status\": \"Dados atualizados!\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./myApi.js",
    "groupTitle": "Pagamentos",
    "name": "PutPagamentoCodigo"
  }
] });
