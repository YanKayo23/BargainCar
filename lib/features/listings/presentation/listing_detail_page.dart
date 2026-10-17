import 'package:flutter/material.dart';

import '../../../app/app_state.dart';
import '../../../app/routes.dart';
import '../../../core/widgets/app_scaffold.dart';

class ListingDetailPage extends StatelessWidget {
  const ListingDetailPage({required this.id, super.key});
  final String id;
  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final listing = state.repository.findById(id);
    if (listing == null || !listing.isAvailable) {
      return AppScaffold(
        title: 'Anúncio indisponível',
        child: Column(
          children: [
            const Icon(Icons.info_outline, size: 64),
            const Text('Este anúncio não está mais disponível'),
            FilledButton(
              onPressed: () => Navigator.pushNamed(context, AppRoutes.home),
              child: const Text('Ver outros anúncios'),
            ),
            if (state.isFavorite(id))
              TextButton(
                onPressed: () => state.toggleFavorite(id),
                child: const Text('Remover dos favoritos'),
              ),
          ],
        ),
      );
    }
    return AppScaffold(
      title: listing.title,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(
            height: 180,
            child: Center(child: Icon(Icons.directions_car, size: 96)),
          ),
          Text(
            listing.formattedPrice,
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          Text('${listing.year} • ${listing.color} • ${listing.city}'),
          const SizedBox(height: 16),
          const Text(
            'Veículo de demonstração. Fotos, vídeo, descrição, contato e perguntas serão conectados à API.',
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: () => state.toggleFavorite(id),
            icon: Icon(
              state.isFavorite(id) ? Icons.favorite : Icons.favorite_border,
            ),
            label: Text(
              state.isFavorite(id) ? 'Remover dos favoritos' : 'Favoritar',
            ),
          ),
        ],
      ),
    );
  }
}
