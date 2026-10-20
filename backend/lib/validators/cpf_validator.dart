/// Verifica se um CPF possui dígitos verificadores válidos.
bool validarCpf(String cpf) {
  final cpfLimpo = cpf.replaceAll(RegExp(r'\D'), '');

  if (cpfLimpo.length != 11) {
    return false;
  }

  if (RegExp(r'^(\d)\1{10}$').hasMatch(cpfLimpo)) {
    return false;
  }

  final numeros = cpfLimpo.split('').map(int.parse).toList();

  var soma = 0;

  for (var i = 0; i < 9; i++) {
    soma += numeros[i] * (10 - i);
  }

  var resto = (soma * 10) % 11;
  final primeiroDigito = resto == 10 ? 0 : resto;

  if (numeros[9] != primeiroDigito) {
    return false;
  }

  soma = 0;

  for (var i = 0; i < 10; i++) {
    soma += numeros[i] * (11 - i);
  }

  resto = (soma * 10) % 11;
  final segundoDigito = resto == 10 ? 0 : resto;

  return numeros[10] == segundoDigito;
}
