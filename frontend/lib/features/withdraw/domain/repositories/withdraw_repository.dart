import 'package:fpdart/fpdart.dart';
import '../entities/withdraw.dart';

abstract class WithdrawRepository {
  Future<Either<Exception, Withdraw>> requestWithdraw({
    required String accountId,
    required double amount,
    required String pixType,
    required String pixKey,
  });
}
