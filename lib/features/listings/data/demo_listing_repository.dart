import '../domain/listing.dart';
import '../domain/listing_repository.dart';

class DemoListingRepository implements ListingRepository {
  @override
  final List<Listing> listings = const [
    Listing(
      id: '1',
      title: 'Volkswagen Gol 1.0',
      price: 39000,
      year: 2018,
      color: 'Prata',
      city: 'Ceres',
    ),
    Listing(
      id: '2',
      title: 'Fiat Argo 1.3',
      price: 65000,
      year: 2021,
      color: 'Branco',
      city: 'Rialma',
      status: ListingStatus.pending,
    ),
    Listing(
      id: '3',
      title: 'Chevrolet Onix',
      price: 72000,
      year: 2022,
      color: 'Preto',
      city: 'Ceres',
    ),
    Listing(
      id: '4',
      title: 'Ford Ka',
      price: 35000,
      year: 2017,
      color: 'Azul',
      city: 'Ceres',
      status: ListingStatus.expired,
    ),
  ];

  @override
  Listing? findById(String id) {
    for (final listing in listings) {
      if (listing.id == id) return listing;
    }
    return null;
  }
}
