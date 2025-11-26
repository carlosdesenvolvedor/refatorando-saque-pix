import '../../domain/entities/withdraw.dart';

class WithdrawModel extends Withdraw {
  const WithdrawModel({
    required super.id,
    required super.accountId,
    required super.amount,
    required super.status,
  });

  factory WithdrawModel.fromJson(Map<String, dynamic> json) {
    return WithdrawModel(
      id: json['id'],
      accountId: json['account_id'],
      amount: double.parse(json['amount'].toString()),
      status: json['done'] == true ? 'done' : 'pending',
    );
  }
}
