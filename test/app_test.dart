import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:bargain_car/app/app.dart';
import 'package:bargain_car/app/routes.dart';

void main() {
  for (final width in [360.0, 1440.0]) {
    testWidgets('Catálogo em largura $width filtra anúncios expirados', (
      tester,
    ) async {
      tester.view.physicalSize = Size(width, 1000);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      await tester.pumpWidget(const BargainCarApp());
      expect(find.text('Volkswagen Gol 1.0'), findsOneWidget);
      expect(find.text('Fiat Argo 1.3'), findsOneWidget);
      expect(find.text('Ford Ka'), findsNothing);
      expect(tester.takeException(), isNull);
      await tester.enterText(find.byType(TextField).first, 'inexistente');
      await tester.pumpAndSettle();
      expect(find.text('Nenhum anúncio encontrado.'), findsOneWidget);
      expect(find.text('Volkswagen Gol 1.0'), findsNothing);
    });
  }

  testWidgets('Favorito acompanha a navegação entre catálogo e favoritos', (
    tester,
  ) async {
    await tester.pumpWidget(const BargainCarApp());
    await tester.ensureVisible(find.text('Favoritar').first);
    await tester.tap(find.text('Favoritar').first);
    await tester.pumpAndSettle();
    final navigator = tester.state<NavigatorState>(find.byType(Navigator));
    navigator.pushNamed(AppRoutes.favorites);
    await tester.pumpAndSettle();
    expect(find.text('Meus favoritos'), findsOneWidget);
    expect(find.text('Volkswagen Gol 1.0'), findsOneWidget);
    await tester.ensureVisible(find.text('Remover dos favoritos'));
    await tester.tap(find.text('Remover dos favoritos'));
    await tester.pumpAndSettle();
    expect(find.text('Nenhum anúncio encontrado.'), findsOneWidget);
  });

  testWidgets('Rotas iniciais abrem sem erro em tela pequena', (tester) async {
    tester.view.physicalSize = const Size(360, 800);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(const BargainCarApp());
    final navigator = tester.state<NavigatorState>(find.byType(Navigator));
    for (final route in [
      AppRoutes.login,
      AppRoutes.register,
      AppRoutes.recoverPassword,
      AppRoutes.resetPassword,
      AppRoutes.profile,
      AppRoutes.editProfile,
      AppRoutes.myListings,
      AppRoutes.createListing,
      AppRoutes.createStore,
      AppRoutes.store,
      AppRoutes.editStore,
      AppRoutes.listing('1'),
      AppRoutes.listing('4'),
      AppRoutes.editListing('1'),
      '/inexistente',
    ]) {
      navigator.pushNamed(route);
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull, reason: route);
      navigator.pop();
      await tester.pumpAndSettle();
    }
  });
}
