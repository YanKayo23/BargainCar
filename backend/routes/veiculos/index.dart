
import 'package:dart_frog/dart_frog.dart';

Future<Response> onRequest(RequestContext context) async {
  final request = context.request;

  if (request.method == HttpMethod.get) {
    return Response.json(
      body: {
        'mensagem': 'Consulta de veículos do BargainCar',
      },
    );
  }

  
if (request.method == HttpMethod.post) {
  final dados = await request.json();

  if (dados is! Map<String, dynamic>) {
    return Response.json(
      statusCode: 400,
      body: {'erro': 'O corpo deve ser um objeto JSON.'},
    );
  }

  final marca = dados['marca'];
  final modelo = dados['modelo'];
  final ano = dados['ano'];
  final preco = dados['preco'];

  if (marca is! String || marca.trim().isEmpty) {
    return Response.json(
      statusCode: 400,
      body: {'erro': 'A marca é obrigatória.'},
    );
  }

  if (modelo is! String || modelo.trim().isEmpty) {
    return Response.json(
      statusCode: 400,
      body: {'erro': 'O modelo é obrigatório.'},
    );
  }

  if (ano is! int || ano < 1886) {
    return Response.json(
      statusCode: 400,
      body: {'erro': 'Informe um ano válido.'},
    );
  }

  if (preco is! num || !preco.isFinite || preco <= 0) {
    return Response.json(
      statusCode: 400,
      body: {'erro': 'O preço deve ser maior que zero.'},);
  }

  return Response.json(
    statusCode: 201,
    body: {
      'mensagem': 'Dados validados com sucesso!',
        'veiculo': dados,
      },
    );
  }

  return Response.json(
    statusCode: 405,
    body: {
      'erro': 'Método HTTP não permitido.',
    },
  );
}
