import 'package:equatable/equatable.dart';

class Account extends Equatable {
  final String id;
  final String name;
  final double balance;

  const Account({
    required this.id,
    required this.name,
    required this.balance, required String document, required String email,
  });

  @override
  List<Object?> get props => [id, name, balance];
}
