import 'package:flutter/material.dart';

import '../../../core/widgets/prototype_form.dart';

class ListingFormPage extends StatelessWidget {
  const ListingFormPage({this.id, super.key});
  final String? id;
  @override
  Widget build(BuildContext context) => PrototypeForm(
    title: id == null ? 'Novo anúncio' : 'Editar anúncio',
    notice:
        'Formulário inicial. Autenticação, preenchimento do anúncio existente, seleção de pessoa/loja, '
        'envio de mídia e salvamento serão implementados. Fotos: 3 a 10, até 5 MB cada. Vídeo: 1 MP4, até 50 MB.',
    fields: const [
      PrototypeField('Título do anúncio'),
      PrototypeField('Ano', keyboard: TextInputType.number),
      PrototypeField(
        'Preço (R\$)',
        keyboard: TextInputType.numberWithOptions(decimal: true),
      ),
      PrototypeField('Cor'),
      PrototypeField('Descrição do veículo', lines: 4),
      PrototypeField('Defeitos'),
      PrototypeField('Débitos pendentes'),
      PrototypeField('Documentação'),
      PrototypeField('Cidade'),
      PrototypeField('Contato', keyboard: TextInputType.phone),
    ],
  );
}
