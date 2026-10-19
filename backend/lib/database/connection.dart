import 'package:dotenv/dotenv.dart' as dotenv;
import 'package:mysql_client_plus/mysql_client_plus.dart';

/// Cria e abre uma conexão com o banco MariaDB do BargainCar.
Future<MySQLConnection> createDatabaseConnection() async {
  final env = dotenv.DotEnv()..load();

  final host = env['DB_HOST'] ?? 'localhost';
  final portValue = env['DB_PORT'] ?? '3306';
  final user = env['DB_USER'] ?? '';
  final password = env['DB_PASSWORD'] ?? '';

  final port = int.tryParse(portValue);

  if (port == null || port < 1 || port > 65535) {
    throw StateError('DB_PORT deve conter uma porta válida.');
  }

  if (user.isEmpty) {
    throw StateError('Configure DB_USER no arquivo .env.');
  }

  final connection = await MySQLConnection.createConnection(
    host: host,
    port: port,
    userName: user,
    password: password,
    databaseName: 'bargaincar',
    secure: false,
  );

  await connection.connect();

  return connection;
}
