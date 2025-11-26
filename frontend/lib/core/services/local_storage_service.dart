import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class LocalStorageService {
  static const String _accountsKey = 'saved_accounts';

  Future<void> saveAccount(String id, String name) async {
    final prefs = await SharedPreferences.getInstance();
    final accountsJson = prefs.getString(_accountsKey);
    List<Map<String, dynamic>> accounts = [];

    if (accountsJson != null) {
      accounts = List<Map<String, dynamic>>.from(json.decode(accountsJson));
    }

    // Check if account already exists
    final existingIndex = accounts.indexWhere((acc) => acc['id'] == id);
    if (existingIndex != -1) {
      accounts[existingIndex] = {'id': id, 'name': name};
    } else {
      accounts.add({'id': id, 'name': name});
    }

    await prefs.setString(_accountsKey, json.encode(accounts));
  }

  Future<List<Map<String, dynamic>>> getSavedAccounts() async {
    final prefs = await SharedPreferences.getInstance();
    final accountsJson = prefs.getString(_accountsKey);

    if (accountsJson != null) {
      return List<Map<String, dynamic>>.from(json.decode(accountsJson));
    }

    return [];
  }

  Future<void> removeAccount(String id) async {
    final prefs = await SharedPreferences.getInstance();
    final accountsJson = prefs.getString(_accountsKey);

    if (accountsJson != null) {
      final accounts = List<Map<String, dynamic>>.from(json.decode(accountsJson));
      accounts.removeWhere((acc) => acc['id'] == id);
      await prefs.setString(_accountsKey, json.encode(accounts));
    }
  }
}
