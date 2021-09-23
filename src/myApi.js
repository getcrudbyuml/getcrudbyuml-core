/**
 * @api {get} /signin Singin
 * @apiVersion 0.0.1
 * @apiGroup GenerateCode
 * @apiSuccess {String} status Mensagem de acesso autorizado
 * @apiSuccessExample {json} Sucesso
 *    HTTP/1.1 200 OK
 *    {
 *      "status": "Logado!"
 *    }
 */

/**
* @apiVersion 0.0.1
* @api {post} /signup Signup
* @apiGroup Autenticação
*
* @apiSuccess {String} status Mensagem de cadastro efetuado
*
* @apiSuccessExample {json} Sucesso
*    HTTP/1.1 200 OK
*    {
*      "status": "Cadastrado!"
*    }
*
*/

/**
 * @apiVersion 0.0.1
 * @api {delete} /logout Logout
 * @apiGroup Autenticação
 *
 * @apiSuccess {String} status Mensagem de saída do sistema
 *
 * @apiSuccessExample {json} Sucesso
 *    HTTP/1.1 200 OK
 *    {
 *      "status": "Você saiu do sistema!"
 *    }
 *
 */

/**
 * @apiVersion 0.0.1
 * @api {put} /pagamento/:codigo Pagamento com código de barras
 * @apiGroup Pagamentos
 *
 * @apiSuccess {String} status Mensagem de dados atualizados
 *
 * @apiSuccessExample {json} Sucesso
 *    HTTP/1.1 200 OK
 *    {
 *      "status": "Dados atualizados!"
 *    }
 *
 */