import 'package:flutter/material.dart';

import '../features/listings/domain/listing.dart';
import '../features/listings/domain/listing_repository.dart';

class AppState extends ChangeNotifier {
  AppState(this.repository);
  final ListingRepository repository;
  final Set<String> _favorites = {};
  List<Listing> get favorites => repository.listings
      .where((item) => _favorites.contains(item.id))
      .toList();
  bool isFavorite(String id) => _favorites.contains(id);
  void toggleFavorite(String id) {
    if (!_favorites.remove(id)) _favorites.add(id);
    notifyListeners();
  }
}

class AppScope extends InheritedNotifier<AppState> {
  const AppScope({required AppState state, required super.child, super.key})
    : super(notifier: state);
  static AppState of(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<AppScope>()!.notifier!;
}
