import 'package:flutter/material.dart';

import '../../../app/app_state.dart';
import '../../../core/widgets/app_scaffold.dart';
import 'listing_card.dart';

class CatalogPage extends StatefulWidget {
  const CatalogPage({this.favoritesOnly = false, super.key});
  final bool favoritesOnly;
  @override
  State<CatalogPage> createState() => _CatalogPageState();
}

class _CatalogPageState extends State<CatalogPage> {
  String _query = '';
  String _city = '';
  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final source = widget.favoritesOnly
        ? state.favorites
        : state.repository.listings;
    final listings = source
        .where(
          (item) =>
              (widget.favoritesOnly || item.isAvailable) &&
              item.title.toLowerCase().contains(_query.toLowerCase()) &&
              item.city.toLowerCase().contains(_city.toLowerCase()),
        )
        .toList();
    return AppScaffold(
      title: 'BargainCar',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            widget.favoritesOnly
                ? 'Meus favoritos'
                : 'Encontre seu próximo carro',
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          const SizedBox(height: 8),
          Text(
            widget.favoritesOnly
                ? 'Favoritos salvos apenas durante esta sessão.'
                : 'Busca demonstrativa por nome e cidade. O raio de 100 km será aplicado pela API.',
          ),
          const SizedBox(height: 24),
          TextField(
            decoration: const InputDecoration(
              labelText: 'O que você procura?',
              prefixIcon: Icon(Icons.search),
            ),
            onChanged: (value) => setState(() => _query = value),
          ),
          const SizedBox(height: 12),
          TextField(
            decoration: const InputDecoration(labelText: 'Cidade'),
            onChanged: (value) => setState(() => _city = value),
          ),
          const SizedBox(height: 24),
          if (listings.isEmpty) const Text('Nenhum anúncio encontrado.'),
          LayoutBuilder(
            builder: (context, constraints) {
              final columns = constraints.maxWidth >= 1000
                  ? 3
                  : constraints.maxWidth >= 650
                  ? 2
                  : 1;
              final width =
                  (constraints.maxWidth - (columns - 1) * 16) / columns;
              return Wrap(
                spacing: 16,
                runSpacing: 16,
                children: listings
                    .map(
                      (item) => SizedBox(
                        width: width,
                        child: ListingCard(listing: item),
                      ),
                    )
                    .toList(),
              );
            },
          ),
        ],
      ),
    );
  }
}
