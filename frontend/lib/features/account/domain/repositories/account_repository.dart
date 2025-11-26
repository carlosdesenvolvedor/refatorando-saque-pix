import 'package:fpdart/fpdart.dart';
import '../entities/account.dart';

abstract class AccountRepository {
  Future<Either<Exception, Account>> createAccount(String name, String document, String email);
  Future<Either<Exception, void>> deposit(String accountId, double amount);
  Future<Either<Exception, double>> getBalance(String accountId);
}
