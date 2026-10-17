import 'package:flutter_test/flutter_test.dart';
import 'package:bargain_car/app/app_state.dart';
import 'package:bargain_car/features/listings/data/demo_listing_repository.dart';

void main() {
  test('Favorito indisponível permanece até remoção manual', () {
    final state = AppState(DemoListingRepository());
    addTearDown(state.dispose);
    state.toggleFavorite('4');
    expect(state.favorites.single.isAvailable, isFalse);
    state.toggleFavorite('4');
    expect(state.favorites, isEmpty);
  });
}
