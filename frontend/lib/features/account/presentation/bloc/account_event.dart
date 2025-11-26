import 'package:equatable/equatable.dart';

abstract class AccountEvent extends Equatable {
  const AccountEvent();

  @override
  List<Object> get props => [];
}

class CreateAccountRequested extends AccountEvent {
  final String name;
  final String document;
  final String email;

  const CreateAccountRequested({
    required this.name,
    required this.document,
    required this.email,
  });

  @override
  List<Object> get props => [name, document, email];
}

class DepositRequested extends AccountEvent {
  final String accountId;
  final double amount;

  const DepositRequested({required this.accountId, required this.amount});

  @override
  List<Object> get props => [accountId, amount];
}

class AccountFetchBalance extends AccountEvent {
  final String accountId;

  const AccountFetchBalance({required this.accountId});

  @override
  List<Object> get props => [accountId];
}
