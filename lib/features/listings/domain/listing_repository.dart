import 'listing.dart';

/// A futura API será responsável pelo raio de 100 km e filtros de status.
abstract interface class ListingRepository {
  List<Listing> get listings;
  Listing? findById(String id);
}
