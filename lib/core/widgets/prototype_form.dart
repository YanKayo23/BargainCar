import 'package:flutter/material.dart';

import 'app_scaffold.dart';

class PrototypeField {
  const PrototypeField(
    this.label, {
    this.secret = false,
    this.optional = false,
    this.keyboard = TextInputType.text,
    this.lines = 1,
  });
  final String label;
  final bool secret;
  final bool optional;
  final TextInputType keyboard;
  final int lines;
}

/// Estrutura visual: não envia, persiste nem autentica os dados informados.
class PrototypeForm extends StatefulWidget {
  const PrototypeForm({
    required this.title,
    required this.fields,
    this.footer,
    this.notice,
    super.key,
  });
  final String title;
  final List<PrototypeField> fields;
  final Widget? footer;
  final String? notice;
  @override
  State<PrototypeForm> createState() => _PrototypeFormState();
}

class _PrototypeFormState extends State<PrototypeForm> {
  final _formKey = GlobalKey<FormState>();
  @override
  Widget build(BuildContext context) => AppScaffold(
    title: widget.title,
    child: Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 620),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                widget.title,
                style: Theme.of(context).textTheme.headlineMedium,
              ),
              const SizedBox(height: 16),
              if (widget.notice != null) ...[
                Text(widget.notice!),
                const SizedBox(height: 16),
              ],
              for (final field in widget.fields) ...[
                TextFormField(
                  decoration: InputDecoration(labelText: field.label),
                  obscureText: field.secret,
                  keyboardType: field.keyboard,
                  maxLines: field.lines,
                  validator: (value) =>
                      !field.optional && (value == null || value.trim().isEmpty)
                      ? 'Preencha este campo.'
                      : null,
                ),
                const SizedBox(height: 16),
              ],
              FilledButton(
                onPressed: () {
                  if (_formKey.currentState!.validate()) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          'Campos preenchidos. A integração com a API ainda será implementada; nenhum dado foi enviado.',
                        ),
                      ),
                    );
                  }
                },
                child: const Text('Validar campos (protótipo)'),
              ),
              if (widget.footer != null) widget.footer!,
            ],
          ),
        ),
      ),
    ),
  );
}
