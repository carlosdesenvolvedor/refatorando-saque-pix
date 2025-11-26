import 'package:equatable/equatable.dart';
import '../../domain/entities/account.dart';

abstract class AccountState extends Equatable {
  const AccountState();

  @override
  List<Object> get props => [];
}

class AccountInitial extends AccountState {}

class AccountLoading extends AccountState {}

class AccountSuccess extends AccountState {
  final Account account;

  const AccountSuccess({required this.account});

  @override
  List<Object> get props => [account];
}

class AccountFailure extends AccountState {
  final String message;

  const AccountFailure({required this.message});

  @override
  List<Object> get props => [message];
}

class DepositSuccess extends AccountState {}

class DepositFailure extends AccountState {
  final String message;

  const DepositFailure({required this.message});

  @override
  List<Object> get props => [message];
}

class AccountBalanceLoaded extends AccountState {
  final double balance;

  const AccountBalanceLoaded({required this.balance});

  @override
  List<Object> get props => [balance];
}
