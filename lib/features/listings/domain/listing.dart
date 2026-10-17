enum ListingStatus { active, pending, expired }

class Listing {
  const Listing({
    required this.id,
    required this.title,
    required this.price,
    required this.year,
    required this.color,
    required this.city,
    this.status = ListingStatus.active,
  });
  final String id;
  final String title;
  final int price;
  final int year;
  final String color;
  final String city;
  final ListingStatus status;
  bool get isAvailable => status != ListingStatus.expired;
  String get formattedPrice =>
      'R\$ ${price.toString().replaceAllMapped(RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (match) => '${match[1]}.')}';
}
