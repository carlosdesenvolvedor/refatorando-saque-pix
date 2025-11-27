import 'package:dio/dio.dart';
import 'package:fpdart/fpdart.dart';
import '../../domain/entities/withdraw.dart';
import '../../domain/repositories/withdraw_repository.dart';
import '../datasources/withdraw_remote_datasource.dart';

class WithdrawRepositoryImpl implements WithdrawRepository {
  final WithdrawRemoteDataSource remoteDataSource;

  WithdrawRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Either<Exception, Withdraw>> requestWithdraw({
    required String accountId,
    required double amount,
    required String pixType,
    required String pixKey,
    String? schedule,
  }) async {
    try {
      final withdraw = await remoteDataSource.requestWithdraw(
        accountId: accountId,
        amount: amount,
        pixType: pixType,
        pixKey: pixKey,
        schedule: schedule,
      );
      return Right(withdraw);
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        final message = e.response?.data['message'] ?? 'Erro de validação';
        return Left(Exception(message));
      }
      return Left(Exception('Erro na requisição: ${e.message}'));
    } catch (e) {
      return Left(Exception(e.toString()));
    }
  }
}
