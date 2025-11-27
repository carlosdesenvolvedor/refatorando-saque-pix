
import 'package:frontend/core/network/api_client.dart';

import '../models/withdraw_model.dart';

abstract class WithdrawRemoteDataSource {
  Future<WithdrawModel> requestWithdraw({
    required String accountId,
    required double amount,
    required String pixType,
    required String pixKey,
    String? schedule,
  });
}

class WithdrawRemoteDataSourceImpl implements WithdrawRemoteDataSource {
  final ApiClient client;

  WithdrawRemoteDataSourceImpl({required this.client});

  @override
  Future<WithdrawModel> requestWithdraw({
    required String accountId,
    required double amount,
    required String pixType,
    required String pixKey,
    String? schedule,
  }) async {
    final response = await client.client.post(
      '/account/$accountId/balance/withdraw',
      data: {
        'method': 'PIX',
        'amount': amount,
        'pix': {
          'type': pixType,
          'key': pixKey,
        },
        'schedule': schedule,
      },
    );

    if (response.statusCode == 201) {
      return WithdrawModel.fromJson(response.data);
    } else {
      throw Exception('Failed to request withdraw');
    }
  }
}
