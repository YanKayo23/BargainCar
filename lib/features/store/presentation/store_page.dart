import 'package:flutter/material.dart';

import '../../../app/routes.dart';
import '../../../core/widgets/app_scaffold.dart';
import '../../../core/widgets/prototype_form.dart';

class StorePage extends StatelessWidget {
  const StorePage({super.key});
  @override
  Widget build(BuildContext context) => AppScaffold(
    title: 'Minha loja',
    child: Column(
      children: [
        const SizedBox(
          height: 140,
          child: Center(child: Icon(Icons.storefront, size: 80)),
        ),
        const Text(
          'Estrutura do perfil da empresa. Banner, dados, anúncios e troca de perfil serão integrados.',
        ),
        TextButton(
          onPressed: () => Navigator.pushNamed(context, AppRoutes.editStore),
          child: const Text('Visualizar formulário da loja'),
        ),
      ],
    ),
  );
}

class StoreFormPage extends StatelessWidget {
  const StoreFormPage({this.edit = false, super.key});
  final bool edit;
  @override
  Widget build(BuildContext context) => PrototypeForm(
    title: edit ? 'Editar loja' : 'Criar minha loja',
    notice: 'Dados pessoais e da loja serão separados. Cada usuário poderá ter uma loja. Salvamento ainda não integrado.',
    fields: const [
      PrototypeField('Nome da empresa'),
      PrototypeField('CNPJ (opcional)', optional: true),
      PrototypeField('Cidade da loja'),
      PrototypeField('Contato da loja', keyboard: TextInputType.phone),
    ],
  );
}
