import 'package:flutter_bloc/flutter_bloc.dart';
import '../../domain/usecases/request_withdraw_usecase.dart';
import 'withdraw_event.dart';
import 'withdraw_state.dart';

class WithdrawBloc extends Bloc<WithdrawEvent, WithdrawState> {
  final RequestWithdrawUseCase requestWithdrawUseCase;

  WithdrawBloc({required this.requestWithdrawUseCase}) : super(WithdrawInitial()) {
    on<WithdrawRequested>(_onWithdrawRequested);
  }

  Future<void> _onWithdrawRequested(
    WithdrawRequested event,
    Emitter<WithdrawState> emit,
  ) async {
    emit(WithdrawLoading());

    final result = await requestWithdrawUseCase(
      accountId: event.accountId,
      amount: event.amount,
      pixType: event.pixType,
      pixKey: event.pixKey,
    );

    result.fold(
      (failure) => emit(WithdrawFailure(message: failure.toString())),
      (withdraw) => emit(WithdrawSuccess(withdraw: withdraw)),
    );
  }
}
