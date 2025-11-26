import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/services/local_storage_service.dart';
import '../../domain/usecases/create_account_usecase.dart';
import '../../domain/usecases/deposit_usecase.dart';
import '../../domain/usecases/get_balance_usecase.dart';
import 'account_event.dart';
import 'account_state.dart';

class AccountBloc extends Bloc<AccountEvent, AccountState> {
  final CreateAccountUseCase createAccountUseCase;
  final DepositUseCase depositUseCase;
  final GetBalanceUseCase getBalanceUseCase;
  final LocalStorageService localStorageService;

  AccountBloc({
    required this.createAccountUseCase,
    required this.depositUseCase,
    required this.getBalanceUseCase,
    required this.localStorageService,
  }) : super(AccountInitial()) {
    on<CreateAccountRequested>(_onCreateAccountRequested);
    on<DepositRequested>(_onDepositRequested);
    on<AccountFetchBalance>(_onAccountFetchBalance);
  }

  Future<void> _onCreateAccountRequested(
    CreateAccountRequested event,
    Emitter<AccountState> emit,
  ) async {
    emit(AccountLoading());

    final result = await createAccountUseCase(
      event.name,
      event.document,
      event.email,
    );

    await result.fold(
      (failure) async => emit(AccountFailure(message: failure.toString())),
      (account) async {
        await localStorageService.saveAccount(account.id, account.name);
        emit(AccountSuccess(account: account));
      },
    );
  }

  Future<void> _onDepositRequested(
    DepositRequested event,
    Emitter<AccountState> emit,
  ) async {
    emit(AccountLoading());

    final result = await depositUseCase(event.accountId, event.amount);

    result.fold(
      (failure) => emit(DepositFailure(message: failure.toString())),
      (_) => emit(DepositSuccess()),
    );
  }

  Future<void> _onAccountFetchBalance(
    AccountFetchBalance event,
    Emitter<AccountState> emit,
  ) async {
    final result = await getBalanceUseCase(event.accountId);

    result.fold(
      (failure) => null, // Silently fail or handle error if needed
      (balance) => emit(AccountBalanceLoaded(balance: balance)),
    );
  }
}
