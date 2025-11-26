import 'package:equatable/equatable.dart';
import '../../domain/entities/withdraw.dart';

abstract class WithdrawState extends Equatable {
  const WithdrawState();

  @override
  List<Object> get props => [];
}

class WithdrawInitial extends WithdrawState {}

class WithdrawLoading extends WithdrawState {}

class WithdrawSuccess extends WithdrawState {
  final Withdraw withdraw;

  const WithdrawSuccess({required this.withdraw});

  @override
  List<Object> get props => [withdraw];
}

class WithdrawFailure extends WithdrawState {
  final String message;

  const WithdrawFailure({required this.message});

  @override
  List<Object> get props => [message];
}
