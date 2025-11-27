import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

class ApiClient {
  final Dio _dio;

  static String _getBaseUrl() {
    // 1. Tenta pegar por injeção (prioridade)
    const envUrl = String.fromEnvironment('API_URL');
    if (envUrl.isNotEmpty) return envUrl;

    // 2. Se não tiver injeção, verifica se é Produção (Release)
    if (kReleaseMode) {
      // URL HARDCODED DO RENDER PARA GARANTIR
      return 'https://saque-pix-backend.onrender.com';
    }

    // 3. Fallback para desenvolvimento local
    return 'http://localhost:9501';
  }

  ApiClient({String? baseUrl})
      : _dio = Dio(BaseOptions(
          baseUrl: baseUrl ?? _getBaseUrl(),
          connectTimeout: const Duration(seconds: 5),
          receiveTimeout: const Duration(seconds: 3),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        )) {
    _dio.interceptors.add(LogInterceptor(
      requestBody: true,
      responseBody: true,
    ));
  }

  Dio get client => _dio;
}
