
import 'package:mysql_client_plus/mysql_client_plus.dart';

import '../lib/database/connection.dart';

Future<void> main() async {
  MySQLConnection? connection;

  try {
    print('Testando conexão com o MariaDB...');

    connection = await createDatabaseConnection();

    print('Conexão estabelecida com sucesso!');

    final result = await connection.execute('SELECT DATABASE() AS banco');

    print('Banco conectado: ${result.rows.first.assoc()['banco']}');
  } catch (error) {
    print('Falha ao conectar: $error');
  } finally {
    if (connection != null) {
      await connection.close();
    }
  }
}
