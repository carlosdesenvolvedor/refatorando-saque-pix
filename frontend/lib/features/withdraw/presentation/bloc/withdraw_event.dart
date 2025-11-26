import 'package:equatable/equatable.dart';

abstract class WithdrawEvent extends Equatable {
  const WithdrawEvent();

  @override
  List<Object> get props => [];
}

class WithdrawRequested extends WithdrawEvent {
  final String accountId;
  final double amount;
  final String pixType;
  final String pixKey;

  const WithdrawRequested({
    required this.accountId,
    required this.amount,
    required this.pixType,
    required this.pixKey,
  });

  @override
  List<Object> get props => [accountId, amount, pixType, pixKey];
}
