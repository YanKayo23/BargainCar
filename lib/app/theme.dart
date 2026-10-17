import 'package:flutter/material.dart';

abstract final class AppTheme {
  static ThemeData get light => ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0066CC)),
    scaffoldBackgroundColor: const Color(0xFFF2F3F5),
    inputDecorationTheme: const InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(),
    ),
    appBarTheme: const AppBarTheme(backgroundColor: Colors.white),
  );
}
