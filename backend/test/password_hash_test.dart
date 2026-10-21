
import 'package:password_guard/password_guard.dart';
import 'package:test/test.dart';

void main() {
  test('gera e verifica o hash de uma senha', () async {
    const senha = 'MinhaSenha123!';

    final resultado = await PasswordGuard.hash(
      password: senha,
    );

    expect(resultado.hash, isNotEmpty);

    final senhaCorreta = await PasswordGuard.verify(
      password: senha,
      hash: resultado.hash,
    );

    expect(senhaCorreta, isTrue);

    final senhaIncorreta = await PasswordGuard.verify(
      password: 'OutraSenha456!',
      hash: resultado.hash,
    );

    expect(senhaIncorreta, isFalse);
  });
}
