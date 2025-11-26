import 'package:fpdart/fpdart.dart';
import '../repositories/account_repository.dart';

class DepositUseCase {
  final AccountRepository repository;

  DepositUseCase({required this.repository});

  Future<Either<Exception, void>> call(String accountId, double amount) {
    return repository.deposit(accountId, amount);
  }
}
