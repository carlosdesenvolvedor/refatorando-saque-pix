import 'package:fpdart/fpdart.dart';
import '../entities/account.dart';
import '../repositories/account_repository.dart';

class CreateAccountUseCase {
  final AccountRepository repository;

  CreateAccountUseCase({required this.repository});

  Future<Either<Exception, Account>> call(String name, String document, String email) {
    return repository.createAccount(name, document, email);
  }
}
