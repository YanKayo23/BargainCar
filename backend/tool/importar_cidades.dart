import 'dart:convert';
import 'dart:io';

import 'package:csv/csv.dart';
import 'package:backend/database/connection.dart';

Future<void> main() async {
  print('Iniciando importação das cidades...');

  // 1. Localiza os arquivos CSV.
  final arquivoEstados = File('../dados/estados.csv');
  final arquivoMunicipios = File('../dados/municipios.csv');

  // 2. Lê os arquivos usando UTF-8.
  final conteudoEstados = await arquivoEstados.readAsString(
    encoding: utf8,
  );

  final conteudoMunicipios = await arquivoMunicipios.readAsString(
    encoding: utf8,
  );

  // 3. Converte os arquivos CSV em listas.
  final estados = csv.decode(conteudoEstados);
  final municipios = csv.decode(conteudoMunicipios);

  print('Linhas de estados: ${estados.length}');
  print('Linhas de municípios: ${municipios.length}');

  // 4. Relaciona o código de cada estado à sua UF.
  final Map<String, String> estadosPorCodigo = {};

  for (final estado in estados.skip(1)) {
    final codigoUf = estado[0].toString().trim();
    final uf = estado[1].toString().trim();

    estadosPorCodigo[codigoUf] = uf;
  }

  print('Estados mapeados: ${estadosPorCodigo.length}');
  print('UF do estado de Goiás: ${estadosPorCodigo['52']}');

  // 5. Prepara os municípios com suas respectivas UFs.
  final List<Map<String, String>> cidades = [];

  int estadosNaoEncontrados = 0;

  for (final municipio in municipios.skip(1)) {
    final codigoIbge = municipio[0].toString().trim();
    final nome = municipio[1].toString().trim();
    final latitude = municipio[2].toString().trim();
    final longitude = municipio[3].toString().trim();
    final codigoUf = municipio[5].toString().trim();

    // Procura a sigla do estado correspondente.
    final uf = estadosPorCodigo[codigoUf];

    if (uf == null) {
      estadosNaoEncontrados++;

      print('Estado não encontrado para: $nome ($codigoUf)');
      continue;
    }

    // Organiza os dados conforme a tabela cidades.
    cidades.add({
      'codigo_ibge': codigoIbge,
      'nome': nome,
      'uf': uf,
      'latitude': latitude,
      'longitude': longitude,
    });
  }

  // 6. Mostra o resultado da preparação.
  print('Municípios preparados: ${cidades.length}');
  print('Municípios sem estado: $estadosNaoEncontrados');

  if (cidades.isNotEmpty) {
    print('Primeiro município: ${cidades.first}');
  }

  
  // 7. Valida os municípios antes da importação.
  final codigosIbge = <String>{};
  final nomesPorUf = <String>{};
  int registrosInvalidos = 0;

  for (final cidade in cidades) {
    final codigo = cidade['codigo_ibge']!;
    final nome = cidade['nome']!;
    final uf = cidade['uf']!;
    final latitude = double.tryParse(cidade['latitude']!);
    final longitude = double.tryParse(cidade['longitude']!);

    final codigoValido = RegExp(r'^\d{7}$').hasMatch(codigo);
    final coordenadasValidas =
        latitude != null &&
        longitude != null &&
        latitude.isFinite &&
        longitude.isFinite &&
        latitude >= -90 &&
        latitude <= 90 &&
        longitude >= -180 &&
        longitude <= 180;

    final codigoDuplicado = !codigosIbge.add(codigo);
    final nomeUfDuplicado = !nomesPorUf.add('${nome.toLowerCase()}|$uf');

    if (!codigoValido ||
        nome.isEmpty ||
        uf.isEmpty ||
        !coordenadasValidas ||
        codigoDuplicado ||
        nomeUfDuplicado) {
      registrosInvalidos++;
      print('Registro inválido ou duplicado: $cidade');
    }
  }

  print('Registros inválidos ou duplicados: $registrosInvalidos');

}
