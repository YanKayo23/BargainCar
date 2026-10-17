# Base Flutter do BargainCar

O README original foi preservado. Este documento descreve somente a base inicial
do front-end e como o grupo pode continuá-la.

## Executar

Use Flutter **3.47.2 stable** (Dart 3.13.2, versão usada na geração):

```sh
flutter pub get
flutter run -d chrome
# Ou, com um emulador/dispositivo Android disponível:
flutter run -d <id-do-dispositivo>
```

Consulte `flutter devices` para obter o identificador do dispositivo.
Android usa os arquivos padrão gerados pelo Flutter; a compilação exige o SDK
Android e o Java configurados. O identificador inicial é `br.com.bargaincar.bargain_car`.
O projeto inclui somente Android e Web, conforme o escopo atual.

## Organização

```text
lib/
  main.dart                     # Ponto de entrada
  app/                          # App, rotas, tema e estado da demonstração
  core/widgets/                 # Layout e formulário reutilizáveis
  features/
    auth/presentation/          # Login, cadastro e recuperação
    listings/
      domain/                   # Modelo e contrato do repositório
      data/                     # Repositório demonstrativo
      presentation/             # Catálogo, detalhe e formulário
    profile/presentation/       # Perfil e área de anúncios
    store/presentation/         # Loja e formulário
test/                           # Navegação, responsividade, busca e favoritos
.github/workflows/flutter.yml   # Formatação, análise, testes e build web
```

Cada funcionalidade pode receber suas próprias camadas `domain` e `data` quando
houver integração real. Não há dependências de estado ou roteamento adicionais;
a base usa Material 3, Navigator e ChangeNotifier do próprio Flutter.

## Referência das telas

O PDF foi usado como referência visual e de fluxos. Esta entrega é uma estrutura
inicial navegável, não a implementação completa das 21 páginas.

| Páginas do PDF | Rotas / estrutura inicial |
| --- | --- |
| 1, 11 | `/cadastro`, `/entrar`: campos e links entre telas |
| 12, 13 | `/recuperar-senha`, `/nova-senha`: formulários sem envio |
| 2, 14, 15 | `/`: catálogo responsivo, busca local por título e cidade |
| 3, 21 | `/anuncios/:id`: detalhe demonstrativo e estado indisponível |
| 4, 16, 17 | `/anuncios/novo`, `/anuncios/:id/editar`: campos iniciais |
| 5 | `/loja/criar`: formulário inicial da empresa |
| 6, 7, 8 | `/perfil`, `/perfil/editar`, `/meus-anuncios`: estrutura sem sessão |
| 9, 10 | `/loja`, `/loja/editar`: estrutura da loja e formulário |
| 18, 19 | Reservadas à futura exclusão com confirmação em Meus anúncios |
| 20 | `/favoritos`: favoritos em memória e estado vazio |

Tema azul, cartões e campos seguem a direção das telas. As imagens são
representadas por ícones locais, sem fotos ou serviços externos.

## O que funciona nesta base

- Navegação entre as rotas e fallback para páginas desconhecidas.
- Catálogo com dados fictícios, layout de uma a três colunas e filtro de texto.
- Status ativo e pendente visíveis; expirados não aparecem no catálogo.
- Favoritos em memória compartilhados entre catálogo, detalhe e favoritos.
- Anúncio expirado fica indisponível, mas permanece favoritado até remoção.
- Formulários verificam somente campos obrigatórios vazios. Não enviam dados.

## Próximas implementações

- Conectar a API Dart Frog prevista no README. Ela ainda não foi criada nesta etapa.
- Implementar autenticação real, sessão e proteção das operações restritas.
- Substituir o repositório demonstrativo pelo cliente HTTP e definir DTOs assíncronos.
- Implementar validação de CPF, e-mail, senha e confirmação de nova senha.
- Implementar busca por cidade e raio de 100 km no servidor. O filtro local atual
  compara texto, sem geolocalização ou cálculo de distância.
- Preencher e salvar os formulários; editar dados existentes; confirmar exclusões;
  renovar anúncios pendentes e implementar o ciclo de expiração no servidor.
- Adicionar upload e validação de fotos/vídeo, contato e perguntas públicas.
- Implementar perfil público/privado, banner e alternância pessoa/loja.
- Persistir favoritos por usuário, incluindo referências a anúncios removidos.
- Configurar notificações e recuperação por e-mail na etapa de integração.

Não insira credenciais, tokens, dados pessoais reais ou contas de serviço no Git.
Os formulários do protótipo não autenticam e não armazenam senhas.

## Verificar

```sh
dart format --output=none --set-exit-if-changed lib test
flutter analyze
flutter test
flutter build web --release
```

O workflow executa esses mesmos checks em pull requests e em pushes na `main`.
