import 'package:get_it/get_it.dart';
import '../network/api_client.dart';
import '../services/local_storage_service.dart';

// Features - Account
import '../../features/account/data/datasources/account_remote_datasource.dart';
import '../../features/account/data/repositories/account_repository_impl.dart';
import '../../features/account/domain/repositories/account_repository.dart';
import '../../features/account/domain/usecases/create_account_usecase.dart';
import '../../features/account/domain/usecases/deposit_usecase.dart';
import '../../features/account/domain/usecases/get_balance_usecase.dart';
import '../../features/account/presentation/bloc/account_bloc.dart';

// Features - Withdraw
import '../../features/withdraw/data/datasources/withdraw_remote_datasource.dart';
import '../../features/withdraw/data/repositories/withdraw_repository_impl.dart';
import '../../features/withdraw/domain/repositories/withdraw_repository.dart';
import '../../features/withdraw/domain/usecases/request_withdraw_usecase.dart';
import '../../features/withdraw/presentation/bloc/withdraw_bloc.dart';

final sl = GetIt.instance;

void setupServiceLocator() {
  // Core
  sl.registerLazySingleton<ApiClient>(() => ApiClient(baseUrl: 'http://localhost:9501'));
  sl.registerLazySingleton<LocalStorageService>(() => LocalStorageService());

  // Features - Account
  // Datasources
  sl.registerLazySingleton<AccountRemoteDataSource>(
      () => AccountRemoteDataSourceImpl(client: sl()));
  // Repositories
  sl.registerLazySingleton<AccountRepository>(
      () => AccountRepositoryImpl(remoteDataSource: sl()));
  // UseCases
  sl.registerLazySingleton(() => CreateAccountUseCase(repository: sl()));
  sl.registerLazySingleton(() => DepositUseCase(repository: sl()));
  sl.registerLazySingleton(() => GetBalanceUseCase(repository: sl()));

  // Features - Withdraw
  // Datasources
  sl.registerLazySingleton<WithdrawRemoteDataSource>(
      () => WithdrawRemoteDataSourceImpl(client: sl()));
  // Repositories
  sl.registerLazySingleton<WithdrawRepository>(
      () => WithdrawRepositoryImpl(remoteDataSource: sl()));
  // UseCases
  sl.registerLazySingleton(() => RequestWithdrawUseCase(repository: sl()));
  
  // BLoCs
  sl.registerFactory(() => WithdrawBloc(requestWithdrawUseCase: sl()));

  sl.registerFactory(() => AccountBloc(
        createAccountUseCase: sl(),
        depositUseCase: sl(),
        getBalanceUseCase: sl(),
        localStorageService: sl(),
      ));
}
