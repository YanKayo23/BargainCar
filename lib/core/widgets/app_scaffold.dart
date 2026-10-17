import 'package:flutter/material.dart';

import '../../app/routes.dart';

class AppScaffold extends StatelessWidget {
  const AppScaffold({required this.title, required this.child, super.key});
  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: Text(title),
      actions: [
        PopupMenuButton<String>(
          tooltip: 'Navegar',
          onSelected: (route) => Navigator.pushNamed(context, route),
          itemBuilder: (_) => const [
            PopupMenuItem(
              value: AppRoutes.home,
              child: Text('Buscar veículos'),
            ),
            PopupMenuItem(value: AppRoutes.favorites, child: Text('Favoritos')),
            PopupMenuItem(value: AppRoutes.profile, child: Text('Meu perfil')),
            PopupMenuItem(
              value: AppRoutes.createListing,
              child: Text('Anunciar'),
            ),
            PopupMenuItem(value: AppRoutes.login, child: Text('Entrar')),
          ],
        ),
      ],
    ),
    body: SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 1200),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Protótipo • dados demonstrativos, sem conexão com a API',
                ),
                const SizedBox(height: 24),
                child,
              ],
            ),
          ),
        ),
      ),
    ),
  );
}
