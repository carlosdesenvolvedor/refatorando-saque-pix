import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:frontend/features/account/domain/entities/account.dart';
import '../../../../core/di/service_locator.dart';
import '../../../../core/services/local_storage_service.dart';

import '../bloc/account_bloc.dart';
import '../bloc/account_event.dart';
import '../bloc/account_state.dart';

class CreateAccountPage extends StatefulWidget {
  const CreateAccountPage({super.key});

  @override
  State<CreateAccountPage> createState() => _CreateAccountPageState();
}

class _CreateAccountPageState extends State<CreateAccountPage> {
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _documentController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();

  @override
  void dispose() {
    _nameController.dispose();
    _documentController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  void _showSavedAccountsModal(BuildContext context) async {
    final localStorageService = sl<LocalStorageService>();
    final savedAccounts = await localStorageService.getSavedAccounts();

    if (!context.mounted) return;

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Contas Salvas',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF820AD1),
                ),
              ),
              const SizedBox(height: 16),
              if (savedAccounts.isEmpty)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(24.0),
                    child: Text(
                      'Nenhuma conta salva neste dispositivo.',
                      style: TextStyle(color: Colors.grey),
                    ),
                  ),
                )
              else
                Expanded(
                  child: ListView.builder(
                    shrinkWrap: true,
                    itemCount: savedAccounts.length,
                    itemBuilder: (context, index) {
                      final account = savedAccounts[index];
                      return ListTile(
                        leading: const CircleAvatar(
                          backgroundColor: Color(0xFFBA4DE3),
                          child: Icon(Icons.person, color: Color(0xFF820AD1)),
                        ),
                        title: Text(
                          account['name'],
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          'ID: ${account['id']}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                        onTap: () {
                          Navigator.pop(context); // Close modal
                          // Navigate to Home with selected account
                          // We create a temporary Account object since we only have ID and Name
                          // The HomePage will fetch the balance
                          final selectedAccount = Account(
                            id: account['id'],
                            name: account['name'],
                            document: '', // Not needed for Home
                            email: '', // Not needed for Home
                            balance: 0.0, // Will be fetched
                          );
                          Navigator.pushReplacementNamed(
                            context,
                            '/home',
                            arguments: selectedAccount,
                          );
                        },
                      );
                    },
                  ),
                ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Fechar'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: BlocConsumer<AccountBloc, AccountState>(
        listener: (context, state) {
          if (state is AccountSuccess) {
            Navigator.pushReplacementNamed(
              context,
              '/home',
              arguments: state.account,
            );
          } else if (state is AccountFailure) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Erro: ${state.message}'),
                backgroundColor: Colors.red,
              ),
            );
          }
        },
        builder: (context, state) {
          if (state is AccountLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          return LayoutBuilder(
            builder: (context, constraints) {
              return SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    minHeight: constraints.maxHeight,
                  ),
                  child: IntrinsicHeight(
                    child: SafeArea(
                      child: Padding(
                        padding: const EdgeInsets.all(24.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 40),
                            const Icon(
                              Icons.account_balance_wallet,
                              size: 48,
                              color: Color(0xFF820AD1),
                            ),
                            const SizedBox(height: 24),
                            const Text(
                              'Abra sua conta\nem minutos',
                              style: TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                                color: Colors.black87,
                                height: 1.2,
                              ),
                            ),
                            const SizedBox(height: 40),
                            TextField(
                              controller: _nameController,
                              decoration: const InputDecoration(labelText: 'Nome Completo'),
                            ),
                            const SizedBox(height: 16),
                            TextField(
                              controller: _documentController,
                              decoration: const InputDecoration(labelText: 'CPF'),
                              keyboardType: TextInputType.number,
                            ),
                            const SizedBox(height: 16),
                            TextField(
                              controller: _emailController,
                              decoration: const InputDecoration(labelText: 'E-mail'),
                              keyboardType: TextInputType.emailAddress,
                            ),
                            const Spacer(),
                            const SizedBox(height: 24),
                            SizedBox(
                              width: double.infinity,
                              height: 56,
                              child: ElevatedButton(
                                onPressed: () {
                                  context.read<AccountBloc>().add(
                                        CreateAccountRequested(
                                          name: _nameController.text,
                                          document: _documentController.text,
                                          email: _emailController.text,
                                        ),
                                      );
                                },
                                child: const Text('Criar Conta Grátis'),
                              ),
                            ),
                            const SizedBox(height: 16),
                            SizedBox(
                              width: double.infinity,
                              height: 56,
                              child: OutlinedButton(
                                onPressed: () => _showSavedAccountsModal(context),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF820AD1),
                                  side: const BorderSide(color: Color(0xFF820AD1)),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                                child: const Text('Já tenho conta'),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
