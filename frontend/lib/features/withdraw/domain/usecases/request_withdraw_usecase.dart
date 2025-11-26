import 'package:fpdart/fpdart.dart';
import '../entities/withdraw.dart';
import '../repositories/withdraw_repository.dart';

class RequestWithdrawUseCase {
  final WithdrawRepository repository;

  RequestWithdrawUseCase({required this.repository});

  Future<Either<Exception, Withdraw>> call({
    required String accountId,
    required double amount,
    required String pixType,
    required String pixKey,
  }) {
    return repository.requestWithdraw(
      accountId: accountId,
      amount: amount,
      pixType: pixType,
      pixKey: pixKey,
    );
  }
}
