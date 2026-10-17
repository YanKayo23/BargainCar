import 'package:flutter/material.dart';

import '../core/widgets/app_scaffold.dart';
import '../core/widgets/prototype_form.dart';
import '../features/auth/presentation/auth_page.dart';
import '../features/listings/data/demo_listing_repository.dart';
import '../features/listings/presentation/catalog_page.dart';
import '../features/listings/presentation/listing_detail_page.dart';
import '../features/listings/presentation/listing_form_page.dart';
import '../features/profile/presentation/profile_page.dart';
import '../features/store/presentation/store_page.dart';
import 'app_state.dart';
import 'routes.dart';
import 'theme.dart';

class BargainCarApp extends StatefulWidget {
  const BargainCarApp({super.key});
  @override
  State<BargainCarApp> createState() => _BargainCarAppState();
}

class _BargainCarAppState extends State<BargainCarApp> {
  final _state = AppState(DemoListingRepository());
  @override
  void dispose() {
    _state.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => AppScope(
    state: _state,
    child: MaterialApp(
      title: 'BargainCar',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      onGenerateRoute: (settings) {
        final route = settings.name ?? AppRoutes.home;
        final page = switch (route) {
          AppRoutes.home => const CatalogPage(),
          AppRoutes.favorites => const CatalogPage(favoritesOnly: true),
          AppRoutes.login => const AuthPage(mode: AuthMode.login),
          AppRoutes.register => const AuthPage(mode: AuthMode.register),
          AppRoutes.recoverPassword => const AuthPage(mode: AuthMode.recover),
          AppRoutes.resetPassword => const AuthPage(mode: AuthMode.reset),
          AppRoutes.profile => const ProfilePage(),
          AppRoutes.myListings => const ProfilePage(myListings: true),
          AppRoutes.editProfile => const PrototypeForm(
            title: 'Editar meus dados',
            fields: [
              PrototypeField('Nome completo'),
              PrototypeField('E-mail'),
              PrototypeField('Cidade padrão'),
              PrototypeField('Contato padrão'),
            ],
          ),
          AppRoutes.createListing => const ListingFormPage(),
          AppRoutes.createStore => const StoreFormPage(),
          AppRoutes.editStore => const StoreFormPage(edit: true),
          AppRoutes.store => const StorePage(),
          _ => _dynamicPage(route),
        };
        return MaterialPageRoute<void>(
          settings: settings,
          builder: (_) => page,
        );
      },
    ),
  );

  Widget _dynamicPage(String route) {
    final edit = RegExp(r'^/anuncios/([^/]+)/editar$').firstMatch(route);
    if (edit != null) return ListingFormPage(id: edit[1]);
    final detail = RegExp(r'^/anuncios/([^/]+)$').firstMatch(route);
    if (detail != null) return ListingDetailPage(id: detail[1]!);
    return const AppScaffold(
      title: 'Página não encontrada',
      child: Text('Use o menu para continuar navegando.'),
    );
  }
}
