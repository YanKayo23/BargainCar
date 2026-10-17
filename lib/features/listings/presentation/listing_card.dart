import 'package:flutter/material.dart';

import '../../../app/app_state.dart';
import '../../../app/routes.dart';
import '../domain/listing.dart';

class ListingCard extends StatelessWidget {
  const ListingCard({required this.listing, super.key});
  final Listing listing;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final favorite = state.isFavorite(listing.id);
    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            height: 140,
            color: Theme.of(context).colorScheme.surfaceContainerHighest,
            child: const Icon(Icons.directions_car, size: 64),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  listing.title,
                  style: Theme.of(context).textTheme.titleLarge,
                ),
                const SizedBox(height: 8),
                Text(
                  listing.isAvailable ? listing.formattedPrice : 'Indisponível',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                Text('${listing.year} • ${listing.color} • ${listing.city}'),
                const SizedBox(height: 16),
                FilledButton(
                  onPressed: () => Navigator.pushNamed(
                    context,
                    AppRoutes.listing(listing.id),
                  ),
                  child: const Text('Ver anúncio'),
                ),
                TextButton.icon(
                  onPressed: () => state.toggleFavorite(listing.id),
                  icon: Icon(favorite ? Icons.favorite : Icons.favorite_border),
                  label: Text(favorite ? 'Remover dos favoritos' : 'Favoritar'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
