import 'package:flutter/material.dart';

import '../../../app/routes.dart';
import '../../../core/widgets/app_scaffold.dart';

class ProfilePage extends StatelessWidget {
  const ProfilePage({this.myListings = false, super.key});
  final bool myListings;
  @override
  Widget build(BuildContext context) => AppScaffold(
    title: myListings ? 'Meus anúncios' : 'Meu perfil',
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Icon(Icons.account_circle_outlined, size: 80),
        const Text('Entre na sua conta para gerenciar seus dados e anúncios.'),
        const Text(
          'Área inicial sem sessão autenticada. Edição, exclusão, renovação e alternância pessoa/loja serão integradas à API.',
        ),
        const SizedBox(height: 16),
        FilledButton(
          onPressed: () => Navigator.pushNamed(context, AppRoutes.login),
          child: const Text('Entrar'),
        ),
        TextButton(
          onPressed: () => Navigator.pushNamed(context, AppRoutes.editProfile),
          child: const Text('Visualizar formulário de perfil'),
        ),
        TextButton(
          onPressed: () => Navigator.pushNamed(context, AppRoutes.createStore),
          child: const Text('Criar minha loja'),
        ),
        TextButton(
          onPressed: () => Navigator.pushNamed(context, AppRoutes.store),
          child: const Text('Visualizar estrutura da loja'),
        ),
        TextButton(
          onPressed: () => Navigator.pushNamed(context, AppRoutes.myListings),
          child: const Text('Meus anúncios'),
        ),
      ],
    ),
  );
}
