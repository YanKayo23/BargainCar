import 'package:password_guard/password_guard.dart';

/// Gera um hash seguro para armazenar a senha no banco.
Future<String> generatePasswordHash(String password) async {
  final result = await PasswordGuard.hash(password: password);
  return result.hash;
}

/// Confere se a senha informada corresponde ao hash armazenado.
Future<bool> verifyPassword({
  required String password,
  required String storedHash,
}) async {
  return PasswordGuard.verify(
    password: password,
    hash: storedHash,
  );
}
