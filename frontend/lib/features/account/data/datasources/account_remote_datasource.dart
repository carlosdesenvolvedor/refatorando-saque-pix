
import 'package:frontend/core/network/api_client.dart';

import '../models/account_model.dart';

abstract class AccountRemoteDataSource {
  Future<AccountModel> createAccount(String name, String document, String email);
  Future<void> deposit(String accountId, double amount);
  Future<double> getBalance(String accountId);
}

class AccountRemoteDataSourceImpl implements AccountRemoteDataSource {
  final ApiClient client;

  AccountRemoteDataSourceImpl({required this.client});

  @override
  Future<AccountModel> createAccount(String name, String document, String email) async {
    final response = await client.client.post('/accounts', data: {
      'name': name,
      'document': document,
      'email': email,
    });

    if (response.statusCode == 201) {
      return AccountModel.fromJson(response.data);
    } else {
      throw Exception('Failed to create account');
    }
  }

  @override
  Future<void> deposit(String accountId, double amount) async {
    final response = await client.client.post('/account/$accountId/deposit', data: {
      'amount': amount,
    });

    if (response.statusCode != 200) {
      throw Exception('Failed to deposit');
    }
  }

  @override
  Future<double> getBalance(String accountId) async {
    final response = await client.client.get('/account/$accountId/balance');

    if (response.statusCode == 200) {
      return double.tryParse(response.data['balance'].toString()) ?? 0.0;
    } else {
      throw Exception('Failed to get balance');
    }
  }
}
