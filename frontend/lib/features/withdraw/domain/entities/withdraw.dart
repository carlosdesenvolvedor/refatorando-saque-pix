import 'package:equatable/equatable.dart';

class Withdraw extends Equatable {
  final String id;
  final String accountId;
  final double amount;
  final String status;

  const Withdraw({
    required this.id,
    required this.accountId,
    required this.amount,
    required this.status,
  });

  @override
  List<Object?> get props => [id, accountId, amount, status];
}
