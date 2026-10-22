import 'package:backend/validators/cpf_validator.dart';
import 'package:dart_frog/dart_frog.dart';
import 'package:password_guard/password_guard.dart';




Future<Response> onRequest(RequestContext context) async {
  final method = context.request.method;

  if (method != HttpMethod.post) {
    return Response(
      statusCode: 405,
      body: 'Método não permitido. Use POST.',
    );
  }

  final body = await context.request.json();

  if (body is! Map<String, dynamic>) {
    return Response(
      statusCode: 400,
      body: 'O corpo da requisição deve ser um objeto JSON.',
    );
  }

  final nome = body['nome'];

  if (nome is! String ||
    nome.trim().isEmpty ||
    nome.trim().length > 100) {
    return Response(
      statusCode: 400,
      body: 'O nome deve ter entre 1 e 100 caracteres.',
    );
  }

  final nomeNormalizado = nome.trim();

  final email = body['email'];

  if (email is! String ||
      !RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(email.trim())) {
    return Response(
      statusCode: 400,
      body: 'Informe um e-mail válido.',
    );
  }

  final emailNormalizado = email.trim().toLowerCase();

  final cpf = body['cpf'];

  if (cpf is! String || !validarCpf(cpf)) {
    return Response(
      statusCode: 400,
      body: 'Informe um CPF válido.',
    );
  }

  final senha = body['senha'];


  if (senha is! String || senha.length < 8) {
    return Response(
      statusCode: 400,
      body: 'A senha deve ter pelo menos 8 caracteres.',
    );
  }

  final resultado = await PasswordGuard.hash(
    password: senha,
  );

  final senhaHash = resultado.hash;

  return Response(body: 'Requisição POST recebida!');
}
