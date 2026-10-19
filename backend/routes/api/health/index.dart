import 'package:backend/database/connection.dart';
import 'package:dart_frog/dart_frog.dart';

Future<Response> onRequest(RequestContext context) async {
  try {
    final connection = await createDatabaseConnection();
    await connection.close();

    return Response.json(
      body: {
        'api': 'ok',
        'database': 'connected',
      },
    );
  } catch (_) {
    return Response.json(
      statusCode: 503,
      body: {
        'api': 'ok',
        'database': 'unavailable',
      },
    );
  }
}
