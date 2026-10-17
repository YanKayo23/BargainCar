import 'package:flutter/material.dart';

import '../../../app/routes.dart';
import '../../../core/widgets/prototype_form.dart';

enum AuthMode { login, register, recover, reset }

class AuthPage extends StatelessWidget {
  const AuthPage({required this.mode, super.key});
  final AuthMode mode;

  @override
  Widget build(BuildContext context) {
    final title = switch (mode) {
      AuthMode.login => 'Entrar na sua conta',
      AuthMode.register => 'Crie sua conta',
      AuthMode.recover => 'Recuperar minha conta',
      AuthMode.reset => 'Criar nova senha',
    };
    final fields = switch (mode) {
      AuthMode.login => const [
        PrototypeField('CPF', keyboard: TextInputType.number),
        PrototypeField('Senha', secret: true),
      ],
      AuthMode.register => const [
        PrototypeField('Nome completo'),
        PrototypeField('E-mail', keyboard: TextInputType.emailAddress),
        PrototypeField('CPF', keyboard: TextInputType.number),
        PrototypeField('Sua cidade'),
        PrototypeField('Contato', keyboard: TextInputType.phone),
        PrototypeField('Senha', secret: true),
      ],
      AuthMode.recover => const [
        PrototypeField('E-mail', keyboard: TextInputType.emailAddress),
      ],
      AuthMode.reset => const [
        PrototypeField('Nova senha', secret: true),
        PrototypeField('Repita a nova senha', secret: true),
      ],
    };
    return PrototypeForm(
      title: title,
      fields: fields,
      notice: 'Estrutura inicial: autenticação, validações de CPF e senha e envio de e-mail ainda não estão integrados.',
      footer: Column(
        children: [
          if (mode != AuthMode.login)
            TextButton(
              onPressed: () => Navigator.pushNamed(context, AppRoutes.login),
              child: const Text('Já tenho uma conta'),
            ),
          if (mode == AuthMode.login) ...[
            TextButton(
              onPressed: () => Navigator.pushNamed(context, AppRoutes.register),
              child: const Text('Criar conta'),
            ),
            TextButton(
              onPressed: () =>
                  Navigator.pushNamed(context, AppRoutes.recoverPassword),
              child: const Text('Esqueci minha senha'),
            ),
          ],
          TextButton(
            onPressed: () => Navigator.pushNamed(context, AppRoutes.home),
            child: const Text('Explorar anúncios'),
          ),
        ],
      ),
    );
  }
}
