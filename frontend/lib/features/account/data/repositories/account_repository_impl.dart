import 'package:fpdart/fpdart.dart';
import '../../domain/entities/account.dart';
import '../../domain/repositories/account_repository.dart';
import '../datasources/account_remote_datasource.dart';

class AccountRepositoryImpl implements AccountRepository {
  final AccountRemoteDataSource remoteDataSource;

  AccountRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Either<Exception, Account>> createAccount(String name, String document, String email) async {
    try {
      final account = await remoteDataSource.createAccount(name, document, email);
      return Right(account);
    } catch (e) {
      return Left(Exception(e.toString()));
    }
  }

  @override
  Future<Either<Exception, void>> deposit(String accountId, double amount) async {
    try {
      await remoteDataSource.deposit(accountId, amount);
      return const Right(null);
    } catch (e) {
      return Left(Exception(e.toString()));
    }
  }

  @override
  Future<Either<Exception, double>> getBalance(String accountId) async {
    try {
      final balance = await remoteDataSource.getBalance(accountId);
      return Right(balance);
    } catch (e) {
      return Left(Exception(e.toString()));
    }
  }
}
