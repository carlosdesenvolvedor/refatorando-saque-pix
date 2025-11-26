import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:frontend/features/account/domain/entities/account.dart';
import 'package:frontend/features/account/presentation/bloc/account_bloc.dart';
import 'package:frontend/features/account/presentation/bloc/account_event.dart';
import 'package:frontend/features/account/presentation/bloc/account_state.dart';



class HomePage extends StatefulWidget {
  final Account account;

  const HomePage({super.key, required this.account});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  late Account _currentAccount;

  @override
  void initState() {
    super.initState();
    _currentAccount = widget.account;
    // Fetch initial balance
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AccountBloc>().add(AccountFetchBalance(accountId: _currentAccount.id));
    });
  }

  void _showDepositDialog(BuildContext context) {
    final depositController = TextEditingController();
    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text('Depositar'),
          content: TextField(
            controller: depositController,
            decoration: const InputDecoration(
              labelText: 'Valor',
              prefixText: 'R\$ ',
            ),
            keyboardType: TextInputType.number,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Cancelar'),
            ),
            ElevatedButton(
              onPressed: () {
                final amount = double.tryParse(depositController.text) ?? 0.0;
                if (amount > 0) {
                  context.read<AccountBloc>().add(
                        DepositRequested(
                          accountId: _currentAccount.id,
                          amount: amount,
                        ),
                      );
                  Navigator.pop(dialogContext);
                }
              },
              child: const Text('Confirmar'),
            ),
          ],
        );
      },
    );
  }

  void _showFeatureUnavailable(BuildContext context) {
    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Row(
            children: [
              Icon(Icons.info_outline, color: Color(0xFF820AD1)),
              SizedBox(width: 8),
              Text('Funcionalidade Extra'),
            ],
          ),
          content: const Text(
            'Essas funcionalidades não estavam descritas no escopo do teste técnico, por isso ficaram desabilitadas. Para habilitar ou ver a versão completa, contate o desenvolvedor Carlos Cezar.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Entendi'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F7),
      body: BlocListener<AccountBloc, AccountState>(
        listener: (context, state) {
          if (state is DepositSuccess) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Depósito realizado com sucesso!'),
                backgroundColor: Color(0xFF00D793),
              ),
            );
            // Refresh balance after deposit
            context.read<AccountBloc>().add(AccountFetchBalance(accountId: _currentAccount.id));
          } else if (state is DepositFailure) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Erro no depósito: ${state.message}'),
                backgroundColor: Colors.red,
              ),
            );
          }
        },
        child: SingleChildScrollView(
          child: Column(
            children: [
              _buildHeader(),
              const SizedBox(height: 24),
              _buildActions(context),
              const SizedBox(height: 24),
              _buildAccountCard(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(24, 60, 24, 32),
      decoration: const BoxDecoration(
        color: Color(0xFF820AD1),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(32)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const CircleAvatar(
                    backgroundColor: Color(0xFFBA4DE3),
                    child: Icon(Icons.person, color: Colors.white),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    'Olá, ${_currentAccount.name}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
              IconButton(
                icon: const Icon(Icons.logout, color: Colors.white),
                onPressed: () {
                  Navigator.pushReplacementNamed(context, '/');
                },
              ),
            ],
          ),
          const SizedBox(height: 32),
          const Text(
            'Saldo disponível',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 8),
          BlocBuilder<AccountBloc, AccountState>(
            buildWhen: (previous, current) => current is AccountBalanceLoaded,
            builder: (context, state) {
              double balance = _currentAccount.balance;
              if (state is AccountBalanceLoaded) {
                balance = state.balance;
              }
              return Text(
                'R\$ ${balance.toStringAsFixed(2)}',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildActions(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _buildActionButton(
            icon: Icons.add,
            label: 'Depositar',
            onTap: () => _showDepositDialog(context),
          ),
          _buildActionButton(
            icon: Icons.arrow_upward,
            label: 'Sacar',
            onTap: () async {
              await Navigator.pushNamed(
                context,
                '/withdraw',
                arguments: _currentAccount.id,
              );
              // Refresh balance after returning from withdraw
              if (context.mounted) {
                context.read<AccountBloc>().add(AccountFetchBalance(accountId: _currentAccount.id));
              }
            },
          ),
          _buildActionButton(
            icon: Icons.list_alt,
            label: 'Extrato',
            onTap: () => _showFeatureUnavailable(context),
          ),
          _buildActionButton(
            icon: Icons.pix,
            label: 'Pix',
            onTap: () => _showFeatureUnavailable(context),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Column(
      children: [
        InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(30),
          child: Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Icon(icon, color: const Color(0xFF820AD1)),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: Colors.black87,
          ),
        ),
      ],
    );
  }

  Widget _buildAccountCard() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.credit_card, color: Colors.grey),
                SizedBox(width: 8),
                Text(
                  'Dados da Conta',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.black87,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            const Text(
              'ID da Conta',
              style: TextStyle(color: Colors.grey, fontSize: 12),
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                Expanded(
                  child: Text(
                    _currentAccount.id,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.copy, size: 20, color: Color(0xFF820AD1)),
                  onPressed: () {
                    Clipboard.setData(ClipboardData(text: _currentAccount.id));
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('ID copiado!')),
                    );
                  },
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
