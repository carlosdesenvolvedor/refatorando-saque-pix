import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'core/di/service_locator.dart';
import 'features/account/presentation/bloc/account_bloc.dart';
import 'features/account/presentation/pages/create_account_page.dart';
import 'features/home/presentation/pages/home_page.dart';
import 'features/withdraw/presentation/pages/withdraw_page.dart';
import 'features/account/domain/entities/account.dart';

void main() {
  setupServiceLocator();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(create: (_) => sl<AccountBloc>()),
      ],
      child: MaterialApp(
        title: 'SaquePix2',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          primaryColor: const Color(0xFF820AD1),
          scaffoldBackgroundColor: const Color(0xFFF5F5F7),
          colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFF820AD1),
            primary: const Color(0xFF820AD1),
            secondary: const Color(0xFF00D793),
          ),
          useMaterial3: true,
          inputDecorationTheme: InputDecorationTheme(
            filled: true,
            fillColor: Colors.grey[100],
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFF820AD1), width: 2),
            ),
            labelStyle: const TextStyle(color: Colors.grey),
          ),
          elevatedButtonTheme: ElevatedButtonThemeData(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF820AD1),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              padding: const EdgeInsets.symmetric(vertical: 16),
              textStyle: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ),
        initialRoute: '/',
        onGenerateRoute: (settings) {
          if (settings.name == '/') {
            return MaterialPageRoute(builder: (_) => const CreateAccountPage());
          } else if (settings.name == '/home') {
            final account = settings.arguments as Account;
            return MaterialPageRoute(
              builder: (_) => HomePage(account: account),
            );
          } else if (settings.name == '/withdraw') {
            final accountId = settings.arguments as String;
            return MaterialPageRoute(
              builder: (_) => WithdrawPage(accountId: accountId),
            );
          }
          return null;
        },
      ),
    );
  }
}
