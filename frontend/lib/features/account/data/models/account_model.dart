import '../../domain/entities/account.dart';

class AccountModel extends Account {
  const AccountModel({
    required super.id,
    required super.name,
    required super.balance, 
    required super.document, 
    required super.email,
  });

  factory AccountModel.fromJson(Map<String, dynamic> json) {
    return AccountModel(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
      balance: json['balance'] != null ? double.tryParse(json['balance'].toString()) ?? 0.0 : 0.0, document: '', email: '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'balance': balance,
    };
  }
}
