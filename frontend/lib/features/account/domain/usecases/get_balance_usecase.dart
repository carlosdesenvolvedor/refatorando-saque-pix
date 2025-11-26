import 'package:fpdart/fpdart.dart';
import '../repositories/account_repository.dart';

class GetBalanceUseCase {
  final AccountRepository repository;

  GetBalanceUseCase({required this.repository});

  Future<Either<Exception, double>> call(String accountId) {
    return repository.getBalance(accountId);
  }
}
